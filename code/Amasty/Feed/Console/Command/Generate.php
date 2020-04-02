<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Console\Command;

use Amasty\Feed\Api\Data\FeedInterface;
use Amasty\Feed\Api\Data\ValidProductsInterface;
use Amasty\Feed\Api\FeedRepositoryInterface;
use Amasty\Feed\Model\Config;
use Amasty\Feed\Model\Config\Source\FeedStatus;
use Amasty\Feed\Model\FeedExport;
use Amasty\Feed\Model\FeedExportFactory;
use Amasty\Feed\Model\Config\Source\ExecuteModeList;
use Amasty\Feed\Model\JobManager;
use Amasty\Feed\Model\JobManagerFactory as JobManagerFactory;
use Amasty\Feed\Model\ValidProduct\ResourceModel\Collection as ValidProductsCollection;
use Amasty\Feed\Model\ValidProduct\ResourceModel\CollectionFactory as ValidProductsCollectionFactory;
use Magento\Framework\App\State;
use Magento\Framework\UrlFactory;
use Magento\Setup\Console\Command\AbstractSetupCommand;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Console: feed:profile:generate
 *
 * phpcs:ignoreFile
 */
class Generate extends AbstractSetupCommand
{
    const JOBS_AMOUNT = 'jobs';

    /**
     * @var FeedRepositoryInterface
     */
    private $feedRepository;

    /**
     * @var ValidProductsCollectionFactory
     */
    private $vpCollectionFactory;

    /**
     * @var UrlFactory
     */
    private $urlFactory;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var State
     */
    private $state;

    /**
     * @var FeedExportFactory
     */
    private $feedExportFactory;

    /**
     * @var array
     */
    private $batchSizes = [];

    /**
     * @var JobManagerFactory
     */
    private $jobManagerFactory;

    public function __construct(
        FeedRepositoryInterface $feedRepository,
        ValidProductsCollectionFactory $vpCollectionFactory,
        FeedExportFactory $feedExportFactory,
        UrlFactory $urlFactory,
        Config $config,
        State $state,
        JobManagerFactory $jobManagerFactory,
        $name = null
    ) {
        $this->feedRepository = $feedRepository;
        $this->vpCollectionFactory = $vpCollectionFactory;
        $this->urlFactory = $urlFactory;
        $this->config = $config;
        $this->state = $state;
        $this->feedExportFactory = $feedExportFactory;
        $this->jobManagerFactory = $jobManagerFactory;

        parent::__construct($name);
    }

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this->setName('feed:profile:generate')
            ->setDescription('Generates feed for specified profile id');

        $this->setDefinition(
            [
                new InputArgument(
                    'id',
                    InputArgument::REQUIRED,
                    'Feed profile ID.'
                ),
                new InputOption(
                    self::JOBS_AMOUNT,
                    '-j',
                    InputOption::VALUE_OPTIONAL,
                    'Enable parallel processing using the specified number of jobs. Default value you can configure in'
                    . ' Admin Panel: Amasty -> Product Feed -> Multi-Process Generation (set \'Yes\')->'
                    . ' Number of Parallels Processes'
                ),
            ]
        );

        parent::configure();
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->state->emulateAreaCode(
            \Magento\Framework\App\Area::AREA_GLOBAL,
            [$this, 'generate'],
            [$input, $output]
        );
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int
     */
    public function generate(InputInterface $input, OutputInterface $output)
    {
        try {
            $profileId = $input->getArgument('id');
            $maxJobs = $input->getOption(self::JOBS_AMOUNT);

            if ($maxJobs === null) {
                $maxJobs = $this->config->getMaxJobsCount();
            }
            if ($maxJobs > 1) {
                if (!function_exists('pcntl_fork')) {
                    $output->writeln(__('Warning: \'pcntl\' php extension is required for parallel feed generation.'));
                    $maxJobs = 1;
                }
            }

            $multiProcessMode = $maxJobs > 1;

            if ($multiProcessMode) {
                /** @var JobManager $jobManager */
                $jobManager = $this->jobManagerFactory->create(['maxJobs' => $maxJobs]);
            }

            $itemsPerPage = (int)$this->config->getItemsPerPage();
            $totalGenerated = 0;
            $page = 1;
            $lastPage = false;

            /** @var FeedInterface $feed */
            $feed = $this->feedRepository->getById($profileId);

            /** @var ValidProductsCollection $vProductsCollection */
            $vProductsCollection = $this->vpCollectionFactory->create()
                ->setPageSize($itemsPerPage)->setCurPage($page);
            $vProductsCollection->addFieldToFilter(ValidProductsInterface::FEED_ID, $feed->getEntityId());

            $feed->setGenerationType(ExecuteModeList::MANUAL_GENERATED);
            $feed->setProductsAmount(0);

            $progressBar = $this->initProgressBar($output, $vProductsCollection->getSize());

            /** @var FeedExport $feedExport */
            $feedExport = $this->feedExportFactory->create([
                'multiProcessMode' => $multiProcessMode
            ]);

            while ($page <= $vProductsCollection->getLastPageNumber()) {
                if ($page == $vProductsCollection->getLastPageNumber()) {
                    $lastPage = true;
                }

                $collectionData = $vProductsCollection->getData();
                $productIds = [];

                foreach ($collectionData as $datum) {
                    $productIds[] = $datum[ValidProductsInterface::VALID_PRODUCT_ID];
                }
                $currentBatch = count($productIds);

                if ($multiProcessMode) {
                    if ($pid = $jobManager->waitForFreeSlot()) {
                        $progressBar->advance($this->batchSizes[$pid]);
                    }

                    if ($pid = $jobManager->fork()) { // Parent process
                        $this->batchSizes[$pid] = $currentBatch;
                    } else { // Child process
                        $feedExport->export($feed, $page - 1, $productIds, $lastPage);

                        return 0;
                    }
                } else {
                    $feedExport->export($feed, $page - 1, $productIds, $lastPage);
                    $progressBar->advance($currentBatch);
                }

                $totalGenerated += $currentBatch;
                $vProductsCollection->setCurPage(++$page)->resetData();
            }

            if ($multiProcessMode) {
                foreach ($jobManager->waitForJobCompletion() as $pid) {
                    $progressBar->advance($this->batchSizes[$pid]);
                }

                $feedExport->combineChunks($feed);
                $feed->setProductsAmount($totalGenerated);
                $feed->setStatus(FeedStatus::READY);
                $this->feedRepository->save($feed);
            }
            return $this->finish($output, $progressBar, $totalGenerated, $feed);
        } catch (\Exception $exception) {
            $output->writeln('<error>' . $exception->getMessage() . '</error>');

            if ($output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
                $output->writeln($exception->getTraceAsString());
            }

            return \Magento\Framework\Console\Cli::RETURN_FAILURE;
        }
    }

    /**
     * @param OutputInterface $output
     * @param int $totalProductsSize
     *
     * @return ProgressBar
     */
    private function initProgressBar($output, $totalProductsSize)
    {
        $progressBar = new ProgressBar($output, $totalProductsSize);
        $progressBar->setFormat('<info>%message%</info> %current%/%max% [%bar%] %percent:3s%% %elapsed%');
        $progressBar->setMessage('Products processed:');
        $progressBar->start();

        return $progressBar;
    }

    /**
     * @param OutputInterface $output
     * @param ProgressBar $progressBar
     * @param int $totalGenerated
     * @param FeedInterface $feed
     *
     * @return int
     */
    private function finish($output, $progressBar, $totalGenerated, $feed)
    {
        $progressBar->finish();
        $output->writeln('');
        $output->writeln("<info>Total generated: $totalGenerated.</info>");
        $output->writeln("<comment>Download link: {$this->getDownloadLink($feed)}</comment>");

        return \Magento\Framework\Console\Cli::RETURN_SUCCESS;
    }

    /**
     * @param FeedInterface $feed
     *
     * @return string
     */
    private function getDownloadLink($feed)
    {
        /** @var \Magento\Framework\UrlInterface $urlInstance */
        $urlInstance = $this->urlFactory->create();

        $routeParams = [
            '_direct' => 'amfeed/feed/download',
            '_query' => [
                'id' => $feed->getEntityId()
            ]
        ];

        return $urlInstance
                ->setScope($feed->getStoreId())
                ->getUrl('', $routeParams)
                //TODO outputFilename ????
            . '&file=' . $feed->getFilename();
    }
}

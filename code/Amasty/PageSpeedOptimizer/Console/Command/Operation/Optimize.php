<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_PageSpeedOptimizer
 */


namespace Amasty\PageSpeedOptimizer\Console\Command\Operation;

use Amasty\PageSpeedOptimizer\Console\Command\OptimizeCommand2;
use Magento\Framework\App\ObjectManager;

class Optimize implements CommandOperationInterface
{
    /**
     * @var \Amasty\PageSpeedOptimizer\Model\Image\ForceOptimization
     */
    private $forceOptimization;

    /**
     * @var \Amasty\PageSpeedOptimizer\Api\QueueRepositoryInterface
     */
    private $queueRepository;

    /**
     * @var \Amasty\PageSpeedOptimizer\Model\Image\GenerateQueue
     */
    private $generateQueue;

    /**
     * @var \Amasty\PageSpeedOptimizer\Model\Image\JobManagerFactory
     */
    private $jobManagerFactory;

    /**
     * @var \Amasty\PageSpeedOptimizer\Model\Image\Process
     */
    private $imageProcess;

    /**
     * @var \Amasty\PageSpeedOptimizer\Model\ConfigProvider
     */
    private $configProvider;

    /**
     * @var array
     */
    private $batches = [];

    public function __construct(
        \Amasty\PageSpeedOptimizer\Model\Image\ForceOptimization $forceOptimization,
        \Amasty\PageSpeedOptimizer\Api\QueueRepositoryInterface $queueRepository,
        \Amasty\PageSpeedOptimizer\Model\Image\GenerateQueue $generateQueue,
        \Amasty\PageSpeedOptimizer\Model\Image\JobManagerFactory $jobManagerFactory,
        \Amasty\PageSpeedOptimizer\Model\Image\Process $imageProcess,
        \Amasty\PageSpeedOptimizer\Model\ConfigProvider $configProvider
    ) {
        $this->forceOptimization = $forceOptimization;
        $this->queueRepository = $queueRepository;
        $this->generateQueue = $generateQueue;
        $this->jobManagerFactory = $jobManagerFactory;
        $this->imageProcess = $imageProcess;
        $this->configProvider = $configProvider;
    }

    /**
     * @inheritdoc
     */
    public function execute(
        \Symfony\Component\Console\Input\InputInterface $input,
        \Symfony\Component\Console\Output\OutputInterface $output
    ) {
        $output->writeln('<info>Generating Images Queue.</info>');
        $queueSize = $this->generateQueue->generateQueue($input->getOption(OptimizeCommand2::IMAGE_SETTING_ID));
        $counter = 0;

        $maxJobs = (int)$input->getOption(OptimizeCommand2::JOBS_AMOUNT);
        if ($maxJobs > 1) {
            if (!function_exists('pcntl_fork')) {
                $output->writeln(__('Warning: \'pcntl\' php extension is required for parallel image optimization.'));
                $maxJobs = 1;
            }
        }

        $multiProcessMode = $maxJobs > 1;

        if ($multiProcessMode) {
            /** @var \Amasty\PageSpeedOptimizer\Model\Image\JobManager $jobManager */
            $jobManager = $this->jobManagerFactory->create(['maxJobs' => $maxJobs]);
            while (!$this->queueRepository->isQueueEmpty()) {
                $this->batches[] = $this->queueRepository->shuffleQueues(100);
            }
        }

        /** @var \Symfony\Component\Console\Helper\ProgressBar $progressBar */
        $progressBar = ObjectManager::getInstance()->create(
            \Symfony\Component\Console\Helper\ProgressBar::class,
            [
                'output' => $output,
                'max' => ceil($queueSize/100)
            ]
        );
        $progressBar->setFormat(
            '<info>%message%</info> %current%/%max% [%bar%]'
        );
        $output->writeln('<info>Optimization Process Started.</info>');
        $progressBar->start();
        $progressBar->display();

        if ($multiProcessMode) {
            while (!empty($this->batches)) {
                if ($jobManager->waitForFreeSlot()) {
                    $progressBar->advance();
                }
                $imagesQueue = array_shift($this->batches);
                $counter += count($imagesQueue);
                $progressBar->setMessage('Process Images ' . ($counter) . ' from ' . $queueSize . '...');
                $progressBar->display();
                if (!$jobManager->fork()) { // Child process
                    foreach ($imagesQueue as $queue) {
                        $this->imageProcess->execute($queue);
                    }

                    return 0;
                }
            }
        } else {
            while (!$this->queueRepository->isQueueEmpty()) {
                $progressBar->setMessage('Process Images ' . (($counter++) * 100) . ' from ' . $queueSize . '...');
                $progressBar->display();
                $this->forceOptimization->execute(100);
                $progressBar->advance();
            }
        }

        $progressBar->setMessage('Process Images ' . $queueSize . ' from ' . $queueSize . '...');
        $progressBar->display();
        $progressBar->finish();
        $output->writeln('');
        $output->writeln('<info>Images were optimized successfully.</info>');
    }
}

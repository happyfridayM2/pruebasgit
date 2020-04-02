<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Console\Command;

use Amasty\Feed\Api\Data\FeedInterface;
use Amasty\Feed\Model\ResourceModel\Feed;
use Magento\Framework\DB\Select;
use Magento\Setup\Console\Command\AbstractSetupCommand;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Console: feed:profile:list
 *
 * phpcs:ignoreFile
 */
class ProfileList extends AbstractSetupCommand
{
    /**
     * @var Feed
     */
    private $feedResource;

    public function __construct(
        Feed $feedResource,
        $name = null
    ) {
        parent::__construct($name);

        $this->feedResource = $feedResource;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('feed:profile:list')
            ->setDescription('Display all feed profiles.');

        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $data = $this->feedResource->getProfilesMainData();

            $table = new Table($output);

            $table
                ->setHeaders(['ID', 'Name', 'Filename', 'Generated At'])
                ->setRows($data);

            $table->render();

            return \Magento\Framework\Console\Cli::RETURN_SUCCESS;
        } catch (\Exception $exception) {
            $output->writeln('<error>' . $exception->getMessage() . '</error>');

            if ($output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
                $output->writeln($exception->getTraceAsString());
            }

            return \Magento\Framework\Console\Cli::RETURN_FAILURE;
        }
    }
}

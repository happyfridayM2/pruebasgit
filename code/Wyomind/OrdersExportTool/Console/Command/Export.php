<?php
/**
 * Copyright © 2019 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\OrdersExportTool\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

/**
 * $ bin/magento help wyomind:ordersexporttool:generate
 * Usage:
 * wyomind:msu:run [feed_id1] ... [feed_idN]
 *
 * Arguments:
 * feed_id            Space-separated list of feeds (generate all feeds if empty)
 *
 * Options:
 * --help (-h)           Display this help message
 * --quiet (-q)          Do not output any message
 * --verbose (-v|vv|vvv) Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug
 * --version (-V)        Display this application version
 * --ansi                Force ANSI output
 * --no-ansi             Disable ANSI output
 * --no-interaction (-n) Do not ask any interactive question
 */
class Export extends Command
{
    const FEED_ID_ARG = "profile_id";

    /**
     * @var \Wyomind\OrdersExportTool\Model\ResourceModel\Profiles\CollectionFactory|null
     */
    protected $_profilesCollectionFactory = null;

    /**
     * @var \Magento\Framework\App\State|null
     */
    protected $_state = null;

    /**
     * Export constructor.
     * @param \Wyomind\OrdersExportTool\Model\ResourceModel\Profiles\CollectionFactory $feedsCollectionFactory
     * @param \Magento\Framework\App\State $state
     */
    public function __construct(
        \Wyomind\OrdersExportTool\Model\ResourceModel\Profiles\CollectionFactory $feedsCollectionFactory,
        \Magento\Framework\App\State $state
    )
    {
    
        $this->_state = $state;
        $this->_profilesCollectionFactory = $feedsCollectionFactory;
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('wyomind:ordersexporttool:export')
                ->setDescription(__('Orders Export Tool : export a profile'))
                ->setDefinition([
                    new InputArgument(
                        self::FEED_ID_ARG,
                        InputArgument::OPTIONAL | InputArgument::IS_ARRAY,
                        __('Space-separated list of profiles (export all profiles if empty)')
                    )
                ]);
        parent::configure();
    }

    protected function execute(
        InputInterface $input,
        OutputInterface $output
    )
    {
        try {
            try {
                $this->_state->setAreaCode('adminhtml');
            } catch (\Exception $e) {
                
            }

            $feedsIds = $input->getArgument(self::FEED_ID_ARG);
            $collection = $this->_profilesCollectionFactory->create()->getList($feedsIds);
            $first = true;
            foreach ($collection as $profile) {

                $profile->isCron = true;
                if ($first) {

                    $profile->loadCustomFunctions();
                    $first = false;
                }

                $output->write("Exporting profile #" . $profile->getId());
                $profile->generate();
                $output->writeln(" => exported");
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $output->writeln($e->getMessage());
            $returnValue = \Magento\Framework\Console\Cli::RETURN_FAILURE;
        }

        $returnValue = \Magento\Framework\Console\Cli::RETURN_FAILURE;

        return $returnValue;
    }
}
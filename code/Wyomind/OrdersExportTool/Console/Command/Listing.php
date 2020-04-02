<?php
/**
 * Copyright © 2019 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\OrdersExportTool\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * $ bin/magento help wyomind:ordersexporttool:list
 * Usage:
 * wyomind:ordersexporttool:list
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
class Listing extends Command
{
    /**
     * @var \Wyomind\OrdersExportTool\Model\ResourceModel\Profiles\CollectionFactory|null
     */
    protected $_profilesCollectionFactory = null;

    /**
     * @var \Magento\Framework\App\State|null
     */
    protected $_state = null;

    /**
     * @var \Wyomind\OrdersExportTool\Helper\Storage|null
     */
    protected $_storageHelper = null;

    /**
     * Listing constructor.
     * @param \Wyomind\OrdersExportTool\Model\ResourceModel\Profiles\CollectionFactory $feedsCollectionFactory
     * @param \Magento\Framework\App\State $state
     * @param \Wyomind\OrdersExportTool\Helper\Storage $storageHelper
     */
    public function __construct(
        \Wyomind\OrdersExportTool\Model\ResourceModel\Profiles\CollectionFactory $feedsCollectionFactory,
        \Magento\Framework\App\State $state,
        \Wyomind\OrdersExportTool\Helper\Storage $storageHelper
    )
    {
        $this->_state = $state;
        $this->_profilesCollectionFactory = $feedsCollectionFactory;
        $this->_storageHelper = $storageHelper;
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('wyomind:ordersexporttool:list')
                ->setDescription(__('Orders Export Tool : get list of available profiles'))
                ->setDefinition([]);
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
            $collection = $this->_profilesCollectionFactory->create();
            foreach ($collection as $profile) {
                $row = sprintf(
                    "%-6d %-55s %-22s",
                    $profile->getId(),
                    $this->_storageHelper->getFileName($profile->getDateFormat(), $profile->getName(), $profile->getType(), time(), null, null),
                    $profile->getUpdatedAt()
                );
                $output->writeln($row);
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $output->writeln($e->getMessage());
            $returnValue = \Magento\Framework\Console\Cli::RETURN_FAILURE;
        }

        $returnValue = \Magento\Framework\Console\Cli::RETURN_FAILURE;

        return $returnValue;
    }
}
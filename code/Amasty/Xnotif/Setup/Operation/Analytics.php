<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Xnotif
 */


namespace Amasty\Xnotif\Setup\Operation;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;
use Amasty\Xnotif\Api\Analytics\Data\StockInterface as Stock;
use Amasty\Xnotif\Api\Analytics\Data\Daily\StockInterface as TempStock;

/**
 * Class Analytics
 */
class Analytics
{
    /**
     * @param SchemaSetupInterface $setup
     */
    public function execute(SchemaSetupInterface $setup)
    {
        $setup->startSetup();

        $stockAnalyticsTable = $setup->getConnection()
            ->newTable($setup->getTable(Stock::MAIN_TABLE))
            ->addColumn(
                'id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Entity ID'
            )
            ->addColumn(
                'subscribed',
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Back in Stock Requests created'
            )
            ->addColumn(
                'sent',
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Back in Stock Alerts sent'
            )
            ->addColumn(
                'orders',
                Table::TYPE_DECIMAL,
                '12,4',
                [],
                'Orders made from notifications sent'
            )
            ->addColumn(
                'date',
                Table::TYPE_TEXT,
                null,
                ['nullable' => false],
                'Date collected info'
            )
            ->setComment('Stock Request Analytics');

        $tempStockTable = $setup->getConnection()
            ->newTable($setup->getTable(TempStock::MAIN_TABLE))
            ->addColumn(
                'id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Entity ID'
            )
            ->addColumn(
                'subscribed',
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Back in Stock Requests created'
            )
            ->addColumn(
                'sent',
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Back in Stock Alerts sent'
            )
            ->addColumn(
                'date',
                Table::TYPE_TEXT,
                null,
                ['nullable' => false],
                'Date collected info'
            )
            ->setComment('Stock Request Analytics. Subscribed and Sent');

        $setup->getConnection()->createTable($stockAnalyticsTable);
        $setup->getConnection()->createTable($tempStockTable);
    }
}

<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_PageSpeedOptimizer
 */


namespace Amasty\PageSpeedOptimizer\Setup\Operation;

use Amasty\PageSpeedOptimizer\Api\Data\QueueInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;

class CreateQueueTable
{
    /**
     * @param SchemaSetupInterface $setup
     */
    public function execute(SchemaSetupInterface $setup)
    {
        $setup->getConnection()->createTable(
            $this->createTable($setup)
        );
    }

    /**
     * @param SchemaSetupInterface $setup
     *
     * @return Table
     */
    private function createTable(SchemaSetupInterface $setup)
    {
        $table = $setup->getTable(\Amasty\PageSpeedOptimizer\Model\Queue\ResourceModel\Queue::TABLE_NAME);

        return $setup->getConnection()
            ->newTable(
                $table
            )->setComment(
                'Amasty Page Speed Optimizer Queue Table'
            )->addColumn(
                QueueInterface::QUEUE_ID,
                Table::TYPE_INTEGER,
                null,
                [
                    'identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true
                ]
            )->addColumn(
                QueueInterface::FILENAME,
                Table::TYPE_TEXT,
                1024,
                [
                    'nullable' => false
                ]
            )->addColumn(
                QueueInterface::EXTENSION,
                Table::TYPE_TEXT,
                255,
                [
                    'default' => '', 'nullable' => false
                ]
            )->addColumn(
                QueueInterface::RESOLUTIONS,
                Table::TYPE_TEXT,
                255,
                [
                    'default' => '', 'nullable' => false
                ]
            )->addColumn(
                QueueInterface::WEBP,
                Table::TYPE_BOOLEAN,
                null,
                [
                    'default' => false, 'nullable' => true,
                ]
            )->addColumn(
                QueueInterface::DUMP_ORIGINAL,
                Table::TYPE_BOOLEAN,
                null,
                [
                    'default' => false, 'nullable' => true
                ]
            )->addColumn(
                QueueInterface::RESIZE_ALGORITHM,
                Table::TYPE_SMALLINT,
                null,
                [
                    'default' => 0, 'nullable' => true, 'unsigned' => true,
                ]
            );
    }
}

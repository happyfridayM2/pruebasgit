<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_PageSpeedOptimizer
 */


namespace Amasty\PageSpeedOptimizer\Setup\Operation;

use Amasty\PageSpeedOptimizer\Api\Data\BundleFileInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;

class CreateBundleTable
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
        $table = $setup->getTable(\Amasty\PageSpeedOptimizer\Model\Bundle\ResourceModel\Bundle::TABLE_NAME);

        return $setup->getConnection()
            ->newTable(
                $table
            )->setComment(
                'Amasty Page Speed Optimizer Bundle Table'
            )->addColumn(
                BundleFileInterface::BUNDLE_FILE_ID,
                Table::TYPE_INTEGER,
                null,
                [
                    'identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true
                ]
            )->addColumn(
                BundleFileInterface::FILENAME,
                Table::TYPE_TEXT,
                1024,
                [
                    'nullable' => false
                ]
            )->addColumn(
                BundleFileInterface::AREA,
                Table::TYPE_TEXT,
                255,
                [
                    'nullable' => true
                ]
            )->addColumn(
                BundleFileInterface::THEME,
                Table::TYPE_TEXT,
                255,
                [
                    'nullable' => true
                ]
            )->addColumn(
                BundleFileInterface::LOCALE,
                Table::TYPE_TEXT,
                255,
                [
                    'nullable' => true
                ]
            );
    }
}

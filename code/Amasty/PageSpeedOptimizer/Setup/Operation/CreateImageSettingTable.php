<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_PageSpeedOptimizer
 */


namespace Amasty\PageSpeedOptimizer\Setup\Operation;

use Amasty\PageSpeedOptimizer\Api\Data\BundleFileInterface;
use Amasty\PageSpeedOptimizer\Api\Data\ImageSettingInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;

class CreateImageSettingTable
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
        $table = $setup->getTable(\Amasty\PageSpeedOptimizer\Model\Image\ResourceModel\ImageSetting::TABLE_NAME);

        return $setup->getConnection()
            ->newTable(
                $table
            )->setComment(
                'Amasty Page Speed Optimizer Image Setting Table'
            )->addColumn(
                ImageSettingInterface::IMAGE_SETTING_ID,
                Table::TYPE_INTEGER,
                null,
                [
                    'identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true
                ]
            )->addColumn(
                ImageSettingInterface::IS_ENABLED,
                Table::TYPE_BOOLEAN,
                false,
                [
                    'nullable' => false, 'default' => true
                ]
            )->addColumn(
                ImageSettingInterface::FOLDERS,
                Table::TYPE_TEXT,
                null,
                [
                    'nullable' => false
                ]
            )->addColumn(
                ImageSettingInterface::TITLE,
                Table::TYPE_TEXT,
                null,
                [
                    'nullable' => true
                ]
            )->addColumn(
                ImageSettingInterface::IS_CREATE_MOBILE_RESOLUTION,
                Table::TYPE_BOOLEAN,
                false,
                [
                    'nullable' => false, 'default' => false
                ]
            )->addColumn(
                ImageSettingInterface::IS_CREATE_TABLET_RESOLUTION,
                Table::TYPE_BOOLEAN,
                false,
                [
                    'nullable' => false, 'default' => false
                ]
            )->addColumn(
                ImageSettingInterface::RESIZE_ALGORITHM,
                Table::TYPE_SMALLINT,
                false,
                [
                    'nullable' => false, 'default' => 0
                ]
            )->addColumn(
                ImageSettingInterface::IS_CREATE_WEBP,
                Table::TYPE_BOOLEAN,
                false,
                [
                    'nullable' => false, 'default' => false
                ]
            )->addColumn(
                ImageSettingInterface::IS_DUMP_ORIGINAL,
                Table::TYPE_BOOLEAN,
                false,
                [
                    'nullable' => false, 'default' => false
                ]
            )->addColumn(
                ImageSettingInterface::JPEG_TOOL,
                Table::TYPE_SMALLINT,
                false,
                [
                    'nullable' => false, 'default' => 0
                ]
            )->addColumn(
                ImageSettingInterface::PNG_TOOL,
                Table::TYPE_SMALLINT,
                false,
                [
                    'nullable' => false, 'default' => 0
                ]
            )->addColumn(
                ImageSettingInterface::GIF_TOOL,
                Table::TYPE_SMALLINT,
                false,
                [
                    'nullable' => false, 'default' => 0
                ]
            );
    }
}

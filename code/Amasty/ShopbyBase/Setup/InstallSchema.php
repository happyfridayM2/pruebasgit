<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_ShopbyBase
 */


namespace Amasty\ShopbyBase\Setup;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

/**
 * Class InstallSchema
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * @var \Amasty\ShopbyBase\Helper\Data
     */
    private $helper;

    public function __construct(\Amasty\ShopbyBase\Helper\Data $helper)
    {
        $this->helper = $helper;
    }

    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
        if (!$this->helper->isShopbyInstalled()) {
            $context->getVersion();
            $tableName = $installer->getTable('amasty_amshopby_filter_setting');
            $table = $installer->getConnection()
                ->newTable($tableName)
                ->addColumn(
                    'setting_id',
                    Table::TYPE_SMALLINT,
                    null,
                    ['identity' => true, 'nullable' => false, 'primary' => true]
                )
                ->addColumn(
                    'filter_code',
                    Table::TYPE_TEXT,
                    100,
                    ['nullable' => false]
                )
                ->addColumn(
                    'is_multiselect',
                    Table::TYPE_SMALLINT,
                    null,
                    ['nullable' => false, 'default' => 0]
                )
                ->addColumn(
                    'display_mode',
                    Table::TYPE_SMALLINT,
                    null,
                    ['nullable' => false, 'default' => 0]
                )
                ->addColumn(
                    'is_seo_significant',
                    Table::TYPE_SMALLINT,
                    null,
                    ['nullable' => false, 'default' => 0]
                )
                ->addIndex(
                    $setup->getIdxName(
                        'amasty_amshopby_filter_setting',
                        ['filter_code'],
                        AdapterInterface::INDEX_TYPE_UNIQUE
                    ),
                    ['filter_code'],
                    ['type' => AdapterInterface::INDEX_TYPE_UNIQUE]
                );

            $installer->getConnection()->createTable($table);
        }
        $installer->endSetup();
    }
}

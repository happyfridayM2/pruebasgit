<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */


namespace Amasty\Shopby\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

/**
 * Class UpgradeSchema
 * @package Amasty\Shopby\Setup
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '1.13.2', '<')) {
            $this->addCmsPageTable($setup);
        }

        if (version_compare($context->getVersion(), '1.15.0', '<')) {
            $this->addGroupsAttribute($setup);
        }

        if (version_compare($context->getVersion(), '2.1.3', '<')) {
            $this->addIndexForRatingFilter($setup);
        }

        if (version_compare($context->getVersion(), '2.1.7', '<')) {
            $this->changeGroupedValueColumnType($setup);
        }

        if (version_compare($context->getVersion(), '2.8.0', '<')) {
            $this->addLimitOptionsShowSearchBox($setup);
        }

        if (version_compare($context->getVersion(), '2.8.5', '<')) {
            $this->dropShowOnlyFeatured($setup);
        }

        $setup->endSetup();
    }

    /**
     * @param SchemaSetupInterface $setup
     * @throws \Zend_Db_Exception
     */
    public function addCmsPageTable(SchemaSetupInterface $setup)
    {
        $tableName = $setup->getTable('amasty_amshopby_cms_page');
        $table = $setup->getConnection()
            ->newTable($tableName)
            ->addColumn(
                'entity_id',
                Table::TYPE_SMALLINT,
                null,
                ['identity' => true, 'nullable' => false, 'primary' => true]
            )
            ->addColumn('page_id', Table::TYPE_SMALLINT, null, ['nullable' => false])
            ->addColumn('enabled', Table::TYPE_BOOLEAN, null, ['nullable' => false, 'default' => false])
            ->addForeignKey(
                $setup->getFkName('amasty_amshopby_cms_page', 'page_id', 'cms_page', 'page_id'),
                'page_id',
                $setup->getTable('cms_page'),
                'page_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            );

        $setup->getConnection()->createTable($table);
    }

    /**
     * @param SchemaSetupInterface $setup
     * @throws \Zend_Db_Exception
     */
    public function addGroupsAttribute(SchemaSetupInterface $setup)
    {
        $tableName = $setup->getTable('amasty_amshopby_group_attr');

        $table = $setup->getConnection()
            ->newTable($tableName)
            ->addColumn(
                'group_id',
                Table::TYPE_SMALLINT,
                null,
                ['identity' => true, 'nullable' => false, 'primary' => true]
            )
            ->addColumn('attribute_id', Table::TYPE_SMALLINT, null, ['nullable' => false, 'unsigned' => true])
            ->addColumn('name', Table::TYPE_TEXT, 255, ['nullable' => false, 'default' => false])
            ->addColumn('group_code', Table::TYPE_TEXT, 50, ['nullable' => false, 'default' => false])
            ->addColumn('url', Table::TYPE_TEXT, 255, ['nullable' => true, 'default' => false])
            ->addColumn('position', Table::TYPE_SMALLINT, null, ['nullable' => false, 'default' => 0])
            ->addColumn('visual', Table::TYPE_TEXT, 255, ['nullable' => true, 'default' => false])
            ->addColumn('type', Table::TYPE_SMALLINT, null, ['nullable' => false, 'default' => 0])
            ->addColumn('enabled', Table::TYPE_BOOLEAN, null, ['nullable' => false, 'default' => false])
            ->addIndex(
                $setup->getIdxName(
                    'amasty_amshopby_group_attr',
                    ['attribute_id', 'group_code'],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                ),
                ['attribute_id', 'group_code'],
                ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
            )
            ->addForeignKey(
                $setup->getFkName('amasty_amshopby_group_attr', 'attribute_id', 'eav_attribute', 'attribute_id'),
                'attribute_id',
                $setup->getTable('eav_attribute'),
                'attribute_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            );

        $setup->getConnection()->createTable($table);

        $tableName = $setup->getTable('amasty_amshopby_group_attr_option');
        $table = $setup->getConnection()
            ->newTable($tableName)
            ->addColumn(
                'group_option_id',
                Table::TYPE_SMALLINT,
                null,
                ['identity' => true, 'nullable' => false, 'primary' => true]
            )
            ->addColumn(
                'group_id',
                Table::TYPE_SMALLINT,
                null,
                ['nullable' => false]
            )
            ->addColumn(
                'option_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Option ID'
            )->addColumn(
                'sort_order',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Sort Order'
            )->addIndex(
                $setup->getIdxName(
                    'amasty_amshopby_group_attr_option',
                    ['group_id', 'option_id'],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                ),
                ['group_id', 'option_id'],
                ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
            )
            ->addForeignKey(
                $setup->getFkName(
                    'amasty_amshopby_group_attr_option',
                    'group_id',
                    'amasty_amshopby_group_attr',
                    'group_id'
                ),
                'group_id',
                $setup->getTable('amasty_amshopby_group_attr'),
                'group_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->addForeignKey(
                $setup->getFkName(
                    'amasty_amshopby_group_attr_option',
                    'option_id',
                    'eav_attribute_option',
                    'option_id'
                ),
                'option_id',
                $setup->getTable('eav_attribute_option'),
                'option_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            );

        $setup->getConnection()->createTable($table);

        $tableName = $setup->getTable('amasty_amshopby_group_attr_value');
        $table = $setup->getConnection()
            ->newTable($tableName)
            ->addColumn(
                'group_option_id',
                Table::TYPE_SMALLINT,
                null,
                ['identity' => true, 'nullable' => false, 'primary' => true]
            )
            ->addColumn(
                'group_id',
                Table::TYPE_SMALLINT,
                null,
                ['nullable' => false]
            )
            ->addColumn(
                'value',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Option ID'
            )->addColumn(
                'sort_order',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Sort Order'
            )
            ->addForeignKey(
                $setup->getFkName(
                    'amasty_amshopby_group_attr_value',
                    'group_id',
                    'amasty_amshopby_group_attr',
                    'group_id'
                ),
                'group_id',
                $setup->getTable('amasty_amshopby_group_attr'),
                'group_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            );

        $setup->getConnection()->createTable($table);
    }

    /**
     * @param SchemaSetupInterface $setup
     */
    private function addIndexForRatingFilter(SchemaSetupInterface $setup)
    {
        $table = $setup->getTable('review_entity_summary');
        $connection = $setup->getConnection();

        $connection->addIndex(
            $table,
            'amasty_shopby_rating_filter',
            ['entity_pk_value', 'entity_type', 'store_id']
        );
    }

    /**
     * @param SchemaSetupInterface $setup
     */
    private function changeGroupedValueColumnType(SchemaSetupInterface $setup)
    {
        $table = $setup->getTable('amasty_amshopby_group_attr_value');
        $setup->getConnection()->changeColumn(
            $table,
            'value',
            'value',
            [
                'type' => Table::TYPE_TEXT,
                'length' => 20,
                'nullable' => false,
                'default' => '',
                'comment' => 'Option Value'
            ]
        );
    }

    /**
     * @param SchemaSetupInterface $setup
     */
    private function addLimitOptionsShowSearchBox(SchemaSetupInterface $setup)
    {
        $table = $setup->getTable('amasty_amshopby_filter_setting');
        $connection = $setup->getConnection();

        $connection->addColumn(
            $table,
            'limit_options_show_search_box',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_BIGINT,
                'nullable' => false,
                'default' => '0',
                'comment' => 'Show Search Box When Number Options'
            ]
        );
    }

    /**
     * @param SchemaSetupInterface $setup
     */
    private function dropShowOnlyFeatured(SchemaSetupInterface $setup)
    {
        $setup->getConnection()->dropColumn($setup->getTable('amasty_amshopby_filter_setting'), 'show_featured_only');
    }
}

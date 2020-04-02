<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_ShopbyBase
 */


namespace Amasty\ShopbyBase\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

/**
 * Class UpgradeSchema
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    const VISIBLE_EVERYWHERE = 'visible_everywhere';

    /**
     * @var \Amasty\ShopbyBase\Helper\Data
     */
    private $helper;

    /**
     * @var Operation\AddLabelPosition
     */
    private $addLabelPosition;

    /**
     * @var Operation\AddImageAlt
     */
    private $addImageAlt;

    public function __construct(
        \Amasty\ShopbyBase\Helper\Data $helper,
        \Amasty\ShopbyBase\Setup\Operation\AddLabelPosition $addLabelPosition,
        \Amasty\ShopbyBase\Setup\Operation\AddImageAlt $addImageAlt
    ) {
        $this->helper = $helper;
        $this->addLabelPosition = $addLabelPosition;
        $this->addImageAlt = $addImageAlt;
    }

    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        $version = $context->getVersion();
        if ($this->helper->isShopbyInstalled() && version_compare($version, '2.4.5', '<')) {
            $version = $this->helper->getShopbyVersion();
        }

        if (version_compare($version, '1.0.1', '<')) {
            $this->addPriceSliderColumnsToFilterSettings($setup);
        }

        if (version_compare($version, '1.2.2.1', '<')) {
            $this->addIndexModeColumnsToFilterSettings($setup);
        }

        if (version_compare($version, '1.3.1', '<')) {
            $this->addHideOneOptionColumnToFilterSettings($setup);
        }

        if (version_compare($version, '1.5.0', '<')) {
            $this->createOptionSettingTable($setup);
        }

        if (version_compare($version, '1.6.1', '<')) {
            $this->addCollapsedColumn($setup);
        }

        if (version_compare($version, '1.6.2', '<')) {
            $this->addDisplayProperties($setup);
        }

        if (version_compare($version, '1.6.3', '<')) {
            $this->addTooltips($setup);
        }

        if (version_compare($version, '1.6.4', '<')) {
            $this->renameCollapsedColumn($setup);
        }

        if (version_compare($version, '1.7.2', '<')) {
            $this->addUseAndLogicField($setup);
        }

        if (version_compare($version, '1.7.3', '<')) {
            $this->addFromToFilterSetting($setup);
        }

        if (version_compare($version, '1.7.4', '<')) {
            $this->addVisibleInCategoryFilterSetting($setup);
        }

        if (version_compare($version, '1.7.5', '<')) {
            $this->addAttributeFilterSetting($setup);
        }

        if (version_compare($version, '1.9.0', '<')) {
            $this->addBrandSliderSetting($setup);
        }

        if (version_compare($version, '1.10.0', '<')) {
            $this->addPlacedBlockToFilterSetting($setup);
        }

        if (version_compare($version, '1.14.7', '<')) {
            $this->addRangeSliderColumnsToFilterSettings($setup);
            $this->addRelNofollowColumnToFilterSettings($setup);
        }

        if (version_compare($version, '1.14.13', '<')) {
            $this->addShowIconsOnProduct($setup);
        }

        if (version_compare($version, '1.15.2', '<')) {
            $this->dropHideOneOption($setup);
            $this->modifyLengthInCategoryFilter($setup);
        }

        if (version_compare($version, '2.1.4', '<')) {
            $this->modifyLengthInAllowedCategoriesFilter($setup);
        }

        if (version_compare($version, '2.1.6', '<')) {
            $this->addDisplayFeatures($setup);
        }

        if (version_compare($version, '2.7.4', '<')) {
            $this->modifyOptionSettings($setup);
            $this->addShortDescription($setup);
        }

        if (version_compare($version, '2.8.5', '<')) {
            $this->addLabelPosition->execute($setup);
        }

        if (version_compare($version, '2.10.2', '<')) {
            $this->addImageAlt->execute($setup);
        }

        $setup->endSetup();
    }

    /**
     * @param SchemaSetupInterface $setup
     */
    private function addPriceSliderColumnsToFilterSettings(SchemaSetupInterface $setup)
    {
        $setup->getConnection()->addColumn(
            $setup->getTable('amasty_amshopby_filter_setting'),
            'slider_step',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                'length' => '12,4',
                'nullable' => false,
                'default' => '1.00',
                'comment' => 'Slider Step'
            ]
        );
        $setup->getConnection()->addColumn(
            $setup->getTable('amasty_amshopby_filter_setting'),
            'units_label_use_currency_symbol',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN,
                'nullable' => false,
                'default' => true,
                'comment' => 'is Units label used currency symbol'
            ]
        );
        $setup->getConnection()->addColumn(
            $setup->getTable('amasty_amshopby_filter_setting'),
            'units_label',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => '255',
                'nullable' => false,
                'default' => '',
                'comment' => 'Units label'
            ]
        );
    }

    /**
     * @param SchemaSetupInterface $setup
     */
    private function addIndexModeColumnsToFilterSettings(SchemaSetupInterface $setup)
    {
        $setup->getConnection()->addColumn(
            $setup->getTable('amasty_amshopby_filter_setting'),
            'index_mode',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                'nullable' => false,
                'default' => '0',
                'comment' => 'Robots Index Mode'
            ]
        );

        $setup->getConnection()->addColumn(
            $setup->getTable('amasty_amshopby_filter_setting'),
            'follow_mode',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                'nullable' => false,
                'default' => '0',
                'comment' => 'Robots Follow Mode'
            ]
        );
    }

    /**
     * @param SchemaSetupInterface $setup
     */
    private function addHideOneOptionColumnToFilterSettings(SchemaSetupInterface $setup)
    {
        $setup->getConnection()->addColumn(
            $setup->getTable('amasty_amshopby_filter_setting'),
            'hide_one_option',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                'nullable' => false,
                'default' => '0',
                'comment' => 'Hide filter when only one option available'
            ]
        );
    }

    /**
     * @param SchemaSetupInterface $setup
     */
    private function createOptionSettingTable(SchemaSetupInterface $setup)
    {
        $tableName = $setup->getTable('amasty_amshopby_option_setting');
        $table = $setup->getConnection()
            ->newTable($tableName)
            ->addColumn(
                'option_setting_id',
                Table::TYPE_SMALLINT,
                null,
                ['identity' => true, 'nullable' => false, 'primary' => true]
            )
            ->addColumn('filter_code', Table::TYPE_TEXT, 255, ['nullable' => false])
            ->addColumn('value', Table::TYPE_INTEGER, 11, ['nullable' => false])
            ->addColumn('store_id', Table::TYPE_INTEGER, null, ['nullable' => false, 'default' => 0])
            ->addColumn('url_alias', Table::TYPE_TEXT, 255, ['nullable' => false])
            ->addColumn('is_featured', Table::TYPE_SMALLINT, null, ['nullable' => false, 'default' => 0])
            ->addColumn('meta_title', Table::TYPE_TEXT, 1000, ['nullable' => false])
            ->addColumn('meta_description', Table::TYPE_TEXT, 10000)
            ->addColumn('meta_keywords', Table::TYPE_TEXT, 10000)
            ->addColumn('title', Table::TYPE_TEXT, 1000, ['nullable' => false])
            ->addColumn('description', Table::TYPE_TEXT, 10000)
            ->addColumn('image', Table::TYPE_TEXT, 255)
            ->addColumn('top_cms_block_id', Table::TYPE_INTEGER)
            ->addColumn('bottom_cms_block_id', Table::TYPE_INTEGER);

        $setup->getConnection()->createTable($table);
    }

    /**
     * @param SchemaSetupInterface $setup
     */
    private function addCollapsedColumn(SchemaSetupInterface $setup)
    {
        $setup->getConnection()->addColumn(
            $setup->getTable('amasty_amshopby_filter_setting'),
            'is_collapsed',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                'nullable' => false,
                'default' => '1',
                'comment' => 'Is filter collapsed'
            ]
        );
    }

    /**
     * @param SchemaSetupInterface $setup
     */
    private function addDisplayProperties(SchemaSetupInterface $setup)
    {
        $table = $setup->getTable('amasty_amshopby_filter_setting');
        $connection = $setup->getConnection();

        $connection->addColumn(
            $table,
            'sort_options_by',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                'nullable' => false,
                'default' => '0',
                'comment' => 'Sort Options By'
            ]
        );

        $connection->addColumn(
            $table,
            'show_product_quantities',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                'nullable' => false,
                'default' => '0',
                'comment' => 'Show Product Quantities'
            ]
        );

        $connection->addColumn(
            $table,
            'is_show_search_box',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                'nullable' => false,
                'default' => '0',
                'comment' => 'Show Search Box'
            ]
        );

        $connection->addColumn(
            $table,
            'number_unfolded_options',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                'nullable' => false,
                'default' => '0',
                'comment' => 'Number of unfolded options'
            ]
        );
    }

    /**
     * @param SchemaSetupInterface $setup
     */
    private function addDisplayFeatures(SchemaSetupInterface $setup)
    {
        $table = $setup->getTable('amasty_amshopby_filter_setting');
        $connection = $setup->getConnection();

        $connection->addColumn(
            $table,
            'show_featured_only',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                'nullable' => false,
                'default' => '0',
                'comment' => 'Show Featured Only'
            ]
        );

        $connection->addColumn(
            $table,
            'category_tree_display_mode',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                'nullable' => false,
                'default' => '0',
                'comment' => 'Category Tree Display Mode'
            ]
        );
    }

    /**
     * @param SchemaSetupInterface $setup
     */
    private function addTooltips(SchemaSetupInterface $setup)
    {
        $table = $setup->getTable('amasty_amshopby_filter_setting');
        $connection = $setup->getConnection();

        $connection->addColumn(
            $table,
            'tooltip',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'nullable' => false,
                'default' => '',
                'comment' => 'Tooltip'
            ]
        );
    }

    /**
     * @param SchemaSetupInterface $setup
     */
    private function renameCollapsedColumn(SchemaSetupInterface $setup)
    {
        $table = $setup->getTable('amasty_amshopby_filter_setting');
        $columnExist = $setup->getConnection()->tableColumnExists(
            $table,
            'is_expanded'
        );
        if (!$columnExist) {
            $setup->getConnection()->changeColumn(
                $table,
                'is_collapsed',
                'is_expanded',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    'default' => '0',
                    'nullable' => false,
                    'comment' => 'Is filter expanded'
                ]
            );
            // @codingStandardsIgnoreStart
            $setup->getConnection()->query("UPDATE `$table` SET `is_expanded` = 1 - `is_expanded`;");
            // @codingStandardsIgnoreEnd
        }
    }

    /**
     * @param SchemaSetupInterface $setup
     */
    private function addFromToFilterSetting(SchemaSetupInterface $setup)
    {
        $setup->getConnection()->addColumn(
            $setup->getTable('amasty_amshopby_filter_setting'),
            'add_from_to_widget',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN,
                'nullable' => false,
                'default' => false,
                'comment' => 'Add From To Widget'
            ]
        );
    }

    /**
     * @param SchemaSetupInterface $setup
     */
    private function addUseAndLogicField(SchemaSetupInterface $setup)
    {
        $table = $setup->getTable('amasty_amshopby_filter_setting');
        $connection = $setup->getConnection();

        $connection->addColumn(
            $table,
            'is_use_and_logic',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                'nullable' => false,
                'default' => 0,
                'comment' => 'Is Use And Logic'
            ]
        );
    }

    /**
     * @param SchemaSetupInterface $setup
     */
    private function addVisibleInCategoryFilterSetting(SchemaSetupInterface $setup)
    {
        $table = $setup->getTable('amasty_amshopby_filter_setting');
        $connection = $setup->getConnection();

        $connection->addColumn(
            $table,
            'visible_in_categories',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => '255',
                'nullable' => false,
                'default' => self::VISIBLE_EVERYWHERE,
                'comment' => 'Visible In Categories'
            ]
        );

        $connection->addColumn(
            $table,
            'categories_filter',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => '255',
                'nullable' => false,
                'default' => '',
                'comment' => 'Categories Filter'
            ]
        );
    }

    /**
     * @param SchemaSetupInterface $setup
     */
    private function addAttributeFilterSetting(SchemaSetupInterface $setup)
    {
        $table = $setup->getTable('amasty_amshopby_filter_setting');
        $connection = $setup->getConnection();

        $connection->addColumn(
            $table,
            'attributes_filter',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => '255',
                'nullable' => false,
                'default' => '',
                'comment' => 'Attributes Filter'
            ]
        );

        $connection->addColumn(
            $table,
            'attributes_options_filter',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => '255',
                'nullable' => false,
                'default' => '',
                'comment' => 'Attributes Options Filter'
            ]
        );
    }

    /**
     * @param SchemaSetupInterface $setup
     */
    private function addBrandSliderSetting(SchemaSetupInterface $setup)
    {
        $table = $setup->getTable('amasty_amshopby_option_setting');
        $connection = $setup->getConnection();

        $connection->addColumn(
            $table,
            'slider_position',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                'nullable' => false,
                'default' => '0',
                'comment' => 'Slider Position'
            ]
        );
        $connection->addColumn(
            $table,
            'slider_image',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => '255',
                'comment' => 'Slider Image'
            ]
        );
    }

    /**
     * @param SchemaSetupInterface $setup
     */
    private function addPlacedBlockToFilterSetting(SchemaSetupInterface $setup)
    {
        $table = $setup->getTable('amasty_amshopby_filter_setting');
        $connection = $setup->getConnection();

        $connection->addColumn(
            $table,
            'block_position',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                'nullable' => false,
                'default' => '0',
                'comment' => 'Show in the Block'
            ]
        );
    }

    /**
     * @param SchemaSetupInterface $setup
     */
    private function addRangeSliderColumnsToFilterSettings(SchemaSetupInterface $setup)
    {
        $setup->getConnection()->addColumn(
            $setup->getTable('amasty_amshopby_filter_setting'),
            'slider_min',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                'length' => '12,4',
                'nullable' => true,
                'comment' => 'Slider Min Value'
            ]
        );

        $setup->getConnection()->addColumn(
            $setup->getTable('amasty_amshopby_filter_setting'),
            'slider_max',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                'length' => '12,4',
                'nullable' => true,
                'comment' => 'Slider Max Value'
            ]
        );
    }

    /**
     * @param SchemaSetupInterface $setup
     */
    private function addRelNofollowColumnToFilterSettings(SchemaSetupInterface $setup)
    {
        $setup->getConnection()->addColumn(
            $setup->getTable('amasty_amshopby_filter_setting'),
            'rel_nofollow',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                'nullable' => false,
                'default' => '1',
                'comment' => 'Add rel="nofollow"',
            ]
        );
    }

    /**
     * @param SchemaSetupInterface $setup
     */
    private function addShowIconsOnProduct(SchemaSetupInterface $setup)
    {
        $setup->getConnection()->addColumn(
            $setup->getTable('amasty_amshopby_filter_setting'),
            'show_icons_on_product',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN,
                'nullable' => false,
                'default' => 0,
                'comment' => 'Show options images block on product view page'
            ]
        );
    }

    /**
     * @param SchemaSetupInterface $setup
     */
    private function dropHideOneOption(SchemaSetupInterface $setup)
    {
        $setup->getConnection()->dropColumn(
            $setup->getTable('amasty_amshopby_filter_setting'),
            'hide_one_option'
        );
    }

    /**
     * @param SchemaSetupInterface $setup
     */
    private function modifyLengthInCategoryFilter(SchemaSetupInterface $setup)
    {
        $table = $setup->getTable('amasty_amshopby_filter_setting');
        $connection = $setup->getConnection();

        $connection->modifyColumn(
            $table,
            'attributes_filter',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => 10000,
                'nullable' => false,
                'default' => '',
                'comment' => 'Attributes Filter'
            ]
        );

        $connection->modifyColumn(
            $table,
            'attributes_options_filter',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => 10000,
                'nullable' => false,
                'default' => '',
                'comment' => 'Attributes Options Filter'
            ]
        );
    }

    /**
     * @param SchemaSetupInterface $setup
     */
    private function modifyLengthInAllowedCategoriesFilter(SchemaSetupInterface $setup)
    {
        $table = $setup->getTable('amasty_amshopby_filter_setting');
        $connection = $setup->getConnection();

        $connection->modifyColumn(
            $table,
            'categories_filter',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'nullable' => false,
                'default' => '',
                'comment' => 'Categories Filter'
            ]
        );
    }

    /**
     * @param SchemaSetupInterface $setup
     */
    private function modifyOptionSettings(SchemaSetupInterface $setup)
    {
        $table = $setup->getTable('amasty_amshopby_option_setting');
        $connection = $setup->getConnection();

        $select = $connection->select()
            ->from($table, ['store_id', 'meta_title', 'title', 'value'])
            ->where('store_id NOT IN (?)', 0);

        $optionsInfoForStores = $connection->fetchAll($select);

        $select = $connection->select()
            ->from($table, ['store_id', 'meta_title', 'title', 'value'])
            ->where('store_id IN (?)', 0);

        $optionsInfoForDefaultStore = $connection->fetchAll($select);

        foreach ($optionsInfoForStores as $option) {
            foreach ($optionsInfoForDefaultStore as $optionForDefault) {
                if ($option['value'] == $optionForDefault['value']) {
                    if ($option['meta_title'] == $optionForDefault['meta_title']) {
                        $this->updateOptionData($setup, $table, $option['value'], $option['store_id'], 'meta_title');
                    }

                    if ($option['title'] == $optionForDefault['title']) {
                        $this->updateOptionData($setup, $table, $option['value'], $option['store_id'], 'title');
                    }
                }
            }
        }
    }

    /**
     * @param $setup
     * @param $table
     * @param $value
     * @param $storeId
     * @param $field
     */
    private function updateOptionData($setup, $table, $value, $storeId, $field)
    {
        $sql = 'UPDATE ' . $table . ' SET `' . $field . '` = "" WHERE `value` = '
            . $value . ' AND `store_id` = ' . $storeId . ';';
        $setup->getConnection()->query($sql);
    }

    /**
     * @param SchemaSetupInterface $setup
     */
    private function addShortDescription(SchemaSetupInterface $setup)
    {
        $setup->getConnection()->addColumn(
            $setup->getTable('amasty_amshopby_option_setting'),
            'short_description',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'nullable' => false,
                'size' => 10000,
                'default' => '',
                'comment' => 'Short description for product page or tooltip'
            ]
        );
    }
}

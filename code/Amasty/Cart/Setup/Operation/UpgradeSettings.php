<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Cart
 */


namespace Amasty\Cart\Setup\Operation;

use Magento\Framework\Setup\ModuleDataSetupInterface;

class UpgradeSettings
{
    private $changedSettings = [
        '"amasty_cart/display/type_loading"' => 'amasty_cart/general/type_loading',
        '"amasty_cart/display/show_qty_product"' => 'amasty_cart/general/show_qty_product',
        '"amasty_cart/general/display_options"' => 'amasty_cart/dialog_popup/display_options',
        '"amasty_cart/general/use_product_page"' => 'amasty_cart/confirm_popup/use_on_product_page',
        '"amasty_cart/general/product_button"' => 'amasty_cart/confirm_popup/product_button',
        '"amasty_cart/display/disp_configurable_image"' => 'amasty_cart/confirm_display/configurable_image'
    ];

    private $combineSettings = [
        '"amasty_cart/display/disp_product"' => 'image',
        '"amasty_cart/display/disp_count"' => 'count',
        '"amasty_cart/display/disp_checkout_button"' => 'checkout_button',
    ];

    /**
     * @param ModuleDataSetupInterface $setup
     * @throws \Zend_Db_Exception
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function execute(ModuleDataSetupInterface $setup)
    {
        try {
            $connection = $setup->getConnection();
            $tableName = $setup->getTable('core_config_data');

            $select = $setup->getConnection()->select()
                ->from($tableName, ['config_id', 'path'])
                ->where('path IN (' . implode(',', array_keys($this->changedSettings)) . ')');

            $settings = $connection->fetchPairs($select);

            foreach ($settings as $key => $value) {
                if (isset($this->changedSettings['"' . $value . '"'])) {
                    $connection->update(
                        $tableName,
                        ['path' => $this->changedSettings['"' . $value . '"']],
                        ['config_id = ?' => $key]
                    );
                }
            }

            $select = $setup->getConnection()->select()
                ->from($tableName, ['config_id', 'path'])
                ->where('path IN (' . implode(',', array_keys($this->combineSettings)) . ')');

            $settings = $connection->fetchPairs($select);
            $elements = [];
            foreach ($settings as $key => $value) {
                if (isset($this->combineSettings['"' . $value . '"'])) {
                    $elements[] = $this->combineSettings['"' . $value . '"'];
                }
            }
            if ($elements) {
                $connection->insertOnDuplicate(
                    $tableName,
                    [
                        'value' => implode(',', $elements),
                        'path'  => '"amasty_cart/confirm_display/display_elements"'
                    ]
                );
            }
        } catch (\Exception $ex) {
            null;//skip/ options is already moved
        }
    }
}

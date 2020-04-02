<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_ShopbySeo
 */


namespace Amasty\ShopbySeo\Model\Source\Canonical;

use \Amasty\ShopbySeo\Model\Customizer\Category\Seo;

/**
 * Class Root
 * @package Amasty\ShopbySeo\Model\Source\Canonical
 */
class Root implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Return array of options as value-label pairs
     *
     * @return array Format: array(array('value' => '<value>', 'label' => '<label>'), ...)
     */
    public function toOptionArray()
    {
        $options = [];
        foreach ($this->toArray() as $optionValue => $optionLabel) {
            $options[] = [
                'value' => $optionValue,
                'label' => $optionLabel
            ];
        }
        return $options;
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return [
            Seo::ROOT_CURRENT => __('Keep current URL'),
            Seo::ROOT_PURE => __('URL Key Only'),
            Seo::ROOT_FIRST_ATTRIBUTE => __('First Attribute Value'),
            Seo::ROOT_CUT_OFF_GET => __('Current URL without Get parameters')
        ];
    }
}

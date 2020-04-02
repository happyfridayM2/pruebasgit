<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Conf
 */
namespace Amasty\Conf\Model\Source;

class Reload implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = [
            [
                'value' => 'none',
                'label' => __('None')
            ],
            [
                'value' => 'name',
                'label' => __('Name')
            ],
            [
                'value' => 'description',
                'label' => __('Description')
            ],
            [
                'value' => 'short_description',
                'label' => __('Short Description')
            ],
            [
                'value' => 'attributes',
                'label' => __('Attributes block')
            ],
            [
                'value' => 'sku',
                'label' => __('SKU')
            ]
        ];

        return $options;
    }
}

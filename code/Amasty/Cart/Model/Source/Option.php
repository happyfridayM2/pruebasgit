<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Cart
 */
namespace Amasty\Cart\Model\Source;

class Option implements \Magento\Framework\Option\ArrayInterface
{
    const ONLY_REQUIRED = '0';
    const ALL_OPTIONS   = '1';

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = [
            [
                'value' => self::ONLY_REQUIRED,
                'label' => __('Show Only if There Are Required Options')
            ],
            [
                'value' => self::ALL_OPTIONS,
                'label' => __('Always Show All Custom Options')
            ]
        ];

        return $options;
    }
}

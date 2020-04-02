<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Conf
 */


namespace Amasty\Conf\Model\Source;

class MatrixMode implements \Magento\Framework\Option\ArrayInterface
{
    const NO = 0;
    const YES = 1;
    const YES_FOR_ALL = 2;

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = [
            [
                'value' => self::NO,
                'label' => __('No')
            ],
            [
                'value' => self::YES,
                'label' => __('For Specified Products')
            ],
            [
                'value' => self::YES_FOR_ALL,
                'label' => __('Yes for All Products')
            ]
        ];

        return $options;
    }
}

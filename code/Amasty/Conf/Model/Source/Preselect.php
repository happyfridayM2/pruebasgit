<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Conf
 */


namespace Amasty\Conf\Model\Source;

class Preselect implements \Magento\Framework\Option\ArrayInterface
{
    const DISABLED = 0;
    const FIRST_OPTIONS = 1;
    const CHEAPEST = 2;

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = [
            [
                'value' => self::DISABLED,
                'label' => __('No')
            ],
            [
                'value' => self::FIRST_OPTIONS,
                'label' => __('The First Options')
            ],
            [
                'value' => self::CHEAPEST,
                'label' => __('The Cheapest Product')
            ]
        ];

        return $options;
    }
}

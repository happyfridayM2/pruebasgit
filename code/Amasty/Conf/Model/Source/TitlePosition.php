<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Conf
 */


namespace Amasty\Conf\Model\Source;

class TitlePosition implements \Magento\Framework\Option\ArrayInterface
{
    const FLOAT = 'float';
    const INSIDE = 'inside';
    const OUTSIDE = 'outside';
    const OVER = 'over';

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = [
            [
                'value' => self::FLOAT,
                'label' => __('Float')
            ],
            [
                'value' => self::INSIDE,
                'label' => __('Inside')
            ],
            [
                'value' => self::OUTSIDE,
                'label' => __('Outside')
            ],
            [
                'value' => self::OVER,
                'label' => __('Over')
            ]
        ];

        return $options;
    }
}

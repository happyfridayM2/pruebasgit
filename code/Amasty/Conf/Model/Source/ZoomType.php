<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Conf
 */


namespace Amasty\Conf\Model\Source;

class ZoomType implements \Magento\Framework\Option\ArrayInterface
{
    const DISABLED = '';
    const OUTSIDE = 'window';
    const INSIDE = 'inner';
    const LENS = 'lens';
    
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
                'label' => __('Disabled')
            ],
            [
                'value' => self::OUTSIDE,
                'label' => __('Outside')
            ],
            [
                'value' => self::INSIDE,
                'label' => __('Inside')
            ],
            [
                'value' => self::LENS,
                'label' => __('Lens')
            ]
        ];

        return $options;
    }
}

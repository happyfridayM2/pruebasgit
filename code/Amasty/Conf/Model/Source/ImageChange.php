<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Conf
 */


namespace Amasty\Conf\Model\Source;

class ImageChange implements \Magento\Framework\Option\ArrayInterface
{
    const DISABLED = '';
    const MOUSE_OVER = 'mouseenter';
    const ON_CLICK = 'click';
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
                'value' => self::MOUSE_OVER,
                'label' => __('On Mouse Hover')
            ],
            [
                'value' => self::ON_CLICK,
                'label' => __('On Click')
            ]
        ];

        return $options;
    }
}

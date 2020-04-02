<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Conf
 */


namespace Amasty\Conf\Model\Source;

class LightboxEffect implements \Magento\Framework\Option\ArrayInterface
{
    const FADE = 'fade';
    const ZOOM = 'zoom';
    const ZOOM_IN_OUT = 'zoom-in-out';

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = [
            [
                'value' => '',
                'label' => __('None')
            ],
            [
                'value' => self::FADE,
                'label' => __('Disappearance')
            ],
            [
                'value' => self::ZOOM,
                'label' => __('Zoom')
            ],
            [
                'value' => self::ZOOM_IN_OUT,
                'label' => __('Zoom-in-out')
            ]
        ];

        return $options;
    }
}

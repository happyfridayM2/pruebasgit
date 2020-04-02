<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Conf
 */


namespace Amasty\Conf\Model\Source;

class LightboxEffectSlide implements \Magento\Framework\Option\ArrayInterface
{
    const FADE = 'fade';
    const SLIDE = 'slide';
    const TUBE = 'tube';
    const CIRCULAR = 'circular';
    const ZOOM_IN_OUT = 'zoom-in-out';
    const ROTATE = 'rotate';

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
                'value' => self::SLIDE,
                'label' => __('Slide')
            ],
            [
                'value' => self::TUBE,
                'label' => __('Tube')
            ],
            [
                'value' => self::CIRCULAR,
                'label' => __('Circular')
            ],
            [
                'value' => self::ZOOM_IN_OUT,
                'label' => __('Zoom-in-out')
            ],
            [
                'value' => self::ROTATE,
                'label' => __('Rotate')
            ]
        ];

        return $options;
    }
}

<?php
/**
 * @author Edgar J. Gómez
 * @copyright Copyright (c) 2017 Edgargomez (http://www.edgargomez.es)
 * @package Edgargomez_HelloWorld
 */
namespace Edgargomez\CookieMsg\Model\Source;

class CrossPosition implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array(
                'value' => 'topleft',
                'label' => __('Top Left')
            ),
            array(
                'value' => 'bottomleft',
                'label' => __('Bottom Left')
            ),
            array(
                'value' => 'topright',
                'label' => __('Top Right')
            ),
            array(
                'value' => 'bottomright',
                'label' => __('Bottom Right')
            )
        );
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return ['topleft' => __('Top Left'),
                'bottomleft' => __('Bottom Left'),
                'topright' => __('Top Right'),
                'bottomright' => __('Bottom Right')
        ];
    }
}
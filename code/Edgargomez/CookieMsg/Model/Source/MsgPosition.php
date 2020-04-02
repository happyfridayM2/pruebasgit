<?php
/**
 * @author Edgar J. Gómez
 * @copyright Copyright (c) 2017 Edgargomez (http://www.edgargomez.es)
 * @package Edgargomez_HelloWorld
 */
namespace Edgargomez\CookieMsg\Model\Source;

class MsgPosition implements \Magento\Framework\Option\ArrayInterface
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
                'value' => 'topfullwidth',
                'label' => __('Top Full Width')
            ),
            array(
                'value' => 'bottomfullwidth',
                'label' => __('Bottom Full Width')
            ),
            array(
                'value' => 'topleftbox',
                'label' => __('Top Left Box')
            ),
            array(
                'value' => 'bottomleftbox',
                'label' => __('Bottom Left Box')
            ),
            array(
                'value' => 'toprightbox',
                'label' => __('Top Right Box')
            ),
            array(
                'value' => 'bottomrightbox',
                'label' => __('Bottom Right Box')
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
        return ['topfullwidth' => __('Top Full Width'),
                'bottomfullwidth' => __('Bottom Full Width'),
                'topleftbox' => __('Top Left Box'),
                'bottomleftbox' => __('Bottom Left Box'),
                'toprightbox' => __('Top Right Box'),
                'bottomrightbox' => __('Bottom Right Box')
        ];
    }
}
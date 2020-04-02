<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Conf
 */


namespace Amasty\Conf\Model\Source;

class CarouselPosition implements \Magento\Framework\Option\ArrayInterface
{
    const UNDER_MAIN_IMAGE = 'under';
    const LEFT_SIDE_IMAGE = 'left';

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = [
            [
                'value' => 'under',
                'label' => __('Under the main image')
            ],
            [
                'value' => 'left',
                'label' => __('To the left of the main image')
            ]
        ];

        return $options;
    }
}

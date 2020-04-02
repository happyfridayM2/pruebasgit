<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_PageSpeedOptimizer
 */


namespace Amasty\PageSpeedOptimizer\Model\OptionSource;

use Magento\Framework\Option\ArrayInterface;

class GifOptimization implements ArrayInterface
{
    const DO_NOT_OPTIMIZE = 0;
    const GIFSCALE = 1;

    const TOOLS = [
        self::GIFSCALE => [
            'name' =>  'gifsicle',
            'command' => 'gifsicle %s -o %s',
            'check' => [
                'command' => 'gifsicle --help',
                'result' => 'Usage: gifsicle'
            ]
        ],
    ];

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $optionArray = [];
        foreach ($this->toArray() as $widgetType => $label) {
            $optionArray[] = ['value' => $widgetType, 'label' => $label];
        }
        return $optionArray;
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        $result = [
            self::DO_NOT_OPTIMIZE => __('Do not Optimize'),
        ];
        foreach (self::TOOLS as $toolId => $tool) {
            $result[$toolId] = $tool['name'];
        }

        return $result;
    }
}

<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_PageSpeedOptimizer
 */


namespace Amasty\PageSpeedOptimizer\Model\OptionSource;

use Magento\Framework\Option\ArrayInterface;

class PngOptimization implements ArrayInterface
{
    const DO_NOT_OPTIMIZE = 0;
    const OPTIPNG = 1;

    const TOOLS = [
        self::OPTIPNG => [
            'name' =>  'optipng',
            'command' => 'optipng %s',
            'check' => [
                'command' => 'optipng --help',
                'result' => 'optipng [options] files'
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

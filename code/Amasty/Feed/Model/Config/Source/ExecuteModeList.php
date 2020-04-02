<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Model\Config\Source;

/**
 * Class ExecuteModeList
 */
class ExecuteModeList implements \Magento\Framework\Option\ArrayInterface
{
    /**#@+
     * Feed generation types
     */
    const CRON = 'schedule';

    const MANUAL = 'manual';

    const CRON_GENERATED = 'By Schedule';

    const MANUAL_GENERATED = 'Manually';
    /**#@-*/

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $optionArray = [];
        foreach ($this->toArray() as $value => $label) {
            $optionArray[] = ['value' => $value, 'label' => $label];
        }
        return $optionArray;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            self::MANUAL => __('Manually'),
            self::CRON => __('By Schedule'),
        ];
    }
}

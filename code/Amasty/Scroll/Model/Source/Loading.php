<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Scroll
 */


namespace Amasty\Scroll\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class Loading
 *
 * @package Amasty\Scroll\Model\Source
 */
class Loading implements OptionSourceInterface
{
    const NONE = 'none';

    const AUTO = 'auto';

    const BUTTON = 'button';

    const COMBINED = 'combined';

    const DEFAULT_COMBINED_VALUE = 3;

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => self::NONE,
                'label' => __('None - module is disabled')
            ],
            [
                'value' => self::AUTO,
                'label' => __('Automatic - on page scroll')
            ],
            [
                'value' => self::BUTTON,
                'label' => __('Button - on button click')
            ],
            [
                'value' => self::COMBINED,
                'label' => __('Combined - automatic + button')
            ]
        ];
    }
}

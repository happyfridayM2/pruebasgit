<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */


namespace Amasty\Shopby\Model\Source\DisplayMode;

/**
 * Class StockFilter
 * @package Amasty\Shopby\Model\Source\DisplayMode
 */
class StockFilter extends \Amasty\Shopby\Model\Source\DisplayMode
{
    /**
     * @return bool
     */
    public function showSwatchOptions()
    {
        return false;
    }

    /**
     * @return array
     */
    protected function getOptions()
    {
        $options = parent::getOptions();
        unset($options[self::MODE_SLIDER]);
        unset($options[self::MODE_FROM_TO_ONLY]);
        return $options;
    }
}

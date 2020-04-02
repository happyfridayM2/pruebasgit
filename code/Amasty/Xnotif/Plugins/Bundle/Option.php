<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Xnotif
 */


namespace Amasty\Xnotif\Plugins\Bundle;

/**
 * Class Option
 */
class Option
{
    /**
     * @param $subject
     * @param \Closure $proceed
     * @param $selection
     * @param bool $includeContainerAddType
     * @return mixed|string
     */
    public function aroundGetSelectionQtyTitlePrice(
        $subject,
        \Closure $proceed,
        $selection,
        $includeContainerAddType = true
    ) {
        $priceTitle = $proceed($selection, $includeContainerAddType);
        $priceTitle = $this->addStatusToTitle($priceTitle, $selection);

        return $priceTitle;
    }

    /**
     * @param $subject
     * @param \Closure $proceed
     * @param $selection
     * @param bool $includeContainerAddType
     * @return mixed|string
     */
    public function aroundGetSelectionTitlePrice(
        $subject,
        \Closure $proceed,
        $selection,
        $includeContainerAddType = true
    ) {
        $priceTitle = $proceed($selection, $includeContainerAddType);
        $priceTitle = $this->addStatusToTitle($priceTitle, $selection);

        return $priceTitle;
    }

    /**
     * @param $priceTitle
     * @param $selection
     * @return mixed|string
     */
    private function addStatusToTitle($priceTitle, $selection)
    {
        if ($selection->getData('amasty_is_salable')) {
            $span = '</span>';
            $position = strpos($priceTitle, $span);
            $text = ' &nbsp; <span class="amxnotif-bundle-status">(' . __('Out of Stock') . ')</span>';
            if ($position !== false) {
                $priceTitle = substr_replace($priceTitle, $span . $text, $position, 7);
            } else {
                $priceTitle .= $text;
            }
        }

        return $priceTitle;
    }
}

<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */


namespace Amasty\Shopby\Block\Navigation\Top;

class Navigation extends \Magento\LayeredNavigation\Block\Navigation
{
    /**
     * For 2.3.4+ because toolbar should loading earlier than navigation
     * @return $this|\Magento\Framework\View\Element\AbstractBlock|\Magento\Framework\View\Element\Template
     */
    protected function _beforeToHtml()
    {
        return $this;
    }
}

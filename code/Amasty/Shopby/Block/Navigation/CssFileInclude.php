<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */


namespace Amasty\Shopby\Block\Navigation;

use Magento\Framework\View\Element\Template;

class CssFileInclude extends \Magento\Framework\View\Element\Template
{
    public function __construct(
        Template\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $context->getPageConfig()->addPageAsset('Amasty_Shopby::css/source/mkcss/am-shopby.css');
    }
}

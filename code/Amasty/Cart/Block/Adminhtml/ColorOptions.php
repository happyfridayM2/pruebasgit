<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Cart
 */

/**
 * Copyright © 2016 Amasty. All rights reserved.
 */
namespace Amasty\Cart\Block\Adminhtml;

class ColorOptions extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Amasty\Cart\Helper\Data
     */
    protected $helper;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Amasty\Cart\Helper\Data $helper,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->helper = $helper;
        $this->_scopeConfig = $context->getScopeConfig();
        $this->setTemplate('Amasty_Cart::script.phtml');
    }

    /**
     * @return \Amasty\Cart\Helper\Data
     */
    public function getHelper()
    {
        return $this->helper;
    }
}

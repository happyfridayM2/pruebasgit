<?php

namespace Connectif\Integration\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Checkout\Model\Cart as CustomerCart;
use Connectif\Integration\Helper\CartHelper;
use Magento\Framework\Escaper;
use Magento\Store\Model\ScopeInterface;
use Connectif\Integration\Helper\ConfigHelper;

class Cart extends Template
{
    protected $_cartHelper;
    protected $_escaper;
    protected $_customerCart;
    protected $_configHelper;

    /**
     * Constructor.
     * @param Context $context the context.
     * @param array $data optional data.
     */
    public function __construct(
        Context $context,
        CartHelper $cartHelper,
        ConfigHelper $configHelper,
        CustomerCart $customerCart,
        Escaper $escaper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_escaper = $escaper;
        $this->_cartHelper = $cartHelper;
        $this->_customerCart = $customerCart;
        $this->_configHelper = $configHelper;
    }

      /**
     * Render HTML for the page type if the module is enabled for the current
     * store.
     *
     * @return string
     */
    protected function _toHtml()
    {
        $isEnabled = $this->_configHelper->isEnabled(ScopeInterface::SCOPE_STORE);
        if (!$isEnabled) {
            return '';
        }
        return parent::_toHtml();
    }

    /**
     * Returns the customer current cart.
     */
    public function getCart()
    {
        $cart = $this->_customerCart->getQuote();
        $connectifCart = $this->_cartHelper->getCartData($cart);
        return $connectifCart;
    }

    public function getHtmlEscaper()
    {
        return $this->_escaper;
    }
}

<?php

namespace Connectif\Integration\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Store\Model\ScopeInterface;
use Connectif\Integration\Helper\ConfigHelper;

class Register extends Template
{
    protected $_cookieManager;
    protected $_cookieMetadataFactory;
    protected $_sessionManager;
    protected $_configHelper;

    /**
     * Constructor.
     * @param Context $context the context.
     * @param array $data optional data.
     */
    public function __construct(
        Context $context,
        CookieManagerInterface $cookieManager,
        CookieMetadataFactory $cookieMetadataFactory,
        ConfigHelper $configHelper,
        SessionManagerInterface $sessionManager,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_cookieManager = $cookieManager;
        $this->_cookieMetadataFactory = $cookieMetadataFactory;
        $this->_sessionManager = $sessionManager;
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
     * Return if customer has registered, we will use our cookie connectif_register for that
     * @return boolean
     */
    public function hasCustomerRegistered()
    {
        $hasRegistered = $this->_cookieManager->getCookie('connectif_register');
        if (!$hasRegistered) {
            return false;
        }
        $this->_cookieManager->deleteCookie(
            'connectif_register',
            $this->_cookieMetadataFactory
                ->createCookieMetadata()
                ->setPath($this->_sessionManager->getCookiePath())
                ->setDomain($this->_sessionManager->getCookieDomain())
        );
        return $hasRegistered;
    }
}

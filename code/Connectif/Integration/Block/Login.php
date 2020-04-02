<?php

namespace Connectif\Integration\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Store\Model\ScopeInterface;
use Connectif\Integration\Helper\ConfigHelper;

class Login extends Template
{
    protected $_cookieManager;
    protected $_cookieMetadataFactory;
    protected $_configHelper;
    protected $_sessionManager;

    /**
     * Constructor.
     * @param Context $context the context.
     * @param array $data optional data.
     */
    public function __construct(
        Context $context,
        CookieManagerInterface $cookieManager,
        CookieMetadataFactory $cookieMetadataFactory,
        SessionManagerInterface $sessionManager,
        ConfigHelper $configHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_cookieManager = $cookieManager;
        $this->_cookieMetadataFactory = $cookieMetadataFactory;       
        $this->_configHelper = $configHelper;
        $this->_sessionManager = $sessionManager;
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
     * Return if customer is logged in using our cookie connectif_login
     * @return boolean
     */
    public function isCustomerLoggedIn()
    {
        $isLoggedIn = $this->_cookieManager->getCookie('connectif_login');
        if (!$isLoggedIn) {
            return false;
        }
        $this->_cookieManager->deleteCookie(
            'connectif_login',
            $this->_cookieMetadataFactory
                ->createCookieMetadata()
                ->setPath($this->_sessionManager->getCookiePath())
                ->setDomain($this->_sessionManager->getCookieDomain())
        );
        return $isLoggedIn;
    }
}

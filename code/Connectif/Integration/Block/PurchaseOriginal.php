<?php

namespace Connectif\Integration\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\Escaper;
use Connectif\Integration\Helper\NewsletterHelper;
use Connectif\Integration\Helper\PurchaseHelper;
use Magento\Store\Model\ScopeInterface;
use Connectif\Integration\Helper\ConfigHelper;

class Purchase extends Template
{
    protected $_escaper;
    protected $_configHelper;
    protected $_purchaseHelper;
    protected $_checkoutSession;
    protected $_newsletterHelper;
    
    /**
     * Constructor.
     * @param Context $context the context.
     * @param array $data optional data.
     */
    public function __construct(
        Context $context,
        Escaper $escaper,
        PurchaseHelper $purchaseHelper,
        ConfigHelper $configHelper,
        CheckoutSession $checkoutSession,
        NewsletterHelper $newsletterHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_escaper = $escaper;
        $this->_purchaseHelper = $purchaseHelper;
        $this->_checkoutSession = $checkoutSession;
        $this->_configHelper = $configHelper;
        $this->_newsletterHelper = $newsletterHelper;
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
     * Return the last placed order meta data for the customer.
     */
    public function getLastOrder()
    {
        $order = $this->_checkoutSession->getLastRealOrder();
        $connectifOrder = $this->_purchaseHelper->getOrderData($order);
        return $connectifOrder;
    }

    public function isOrderAsGuest() {
        $order = $this->_checkoutSession->getLastRealOrder();
        return $order->getCustomerIsGuest();
    }

    public function getCustomerInfoFromOrder() {
        $order = $this->_checkoutSession->getLastRealOrder();
        $customer = array();
        $billingAddress = $order->getBillingAddress();
        $customer['primary_key'] = $order->getCustomerEmail();
        $customer['_name'] = $billingAddress->getFirstname();
        $customer['_surname'] = $billingAddress->getLastname();
        $customer['_emailStatus'] = $this->_newsletterHelper->isOptedIn($customer['primary_key']) ? 'subscribed' : 'unsubscribed';
        $countryId = $billingAddress->getCountryId();
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $customer['country'] =  $objectManager->create('\Magento\Directory\Model\Country')->load($countryId)->getName();
        $customer['city'] = $billingAddress->getCity();
        $customer['company'] = $billingAddress->getCompany();
        $customer['telephone'] = $billingAddress->getTelephone();
        $customer['fax'] = $billingAddress->getFax();
        $customer['zip'] = $billingAddress->getPostcode();
        $streets = $billingAddress->getStreet();
        if (count($streets) === 1) {
            $customer['street'] =  $streets[0];
        } else if (count($streets) === 2) {
            $customer['street'] =  $streets[0] . ' ' . $streets[1];
        }
        return $customer;
    }

    public function getHtmlEscaper()
    {
        return $this->_escaper;
    }
}

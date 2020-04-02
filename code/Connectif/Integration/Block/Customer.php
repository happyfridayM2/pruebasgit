<?php

namespace Connectif\Integration\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Connectif\Integration\Helper\ConfigHelper;
use Magento\Store\Model\ScopeInterface;
use Connectif\Integration\Helper\NewsletterHelper;
use Magento\Customer\Model\Session;
use Magento\Framework\Escaper;
use Magento\Customer\Model\Address;

class Customer extends Template
{
    protected $_configHelper;
    protected $_newsletterHelper;
    protected $_customerSession;
    protected $_escaper;
    protected $_address;

    /**
     * Constructor.
     * @param Context $context the context.
     * @param array $data optional data.
     */
    public function __construct(
        Context $context,
        ConfigHelper $configHelper,
        NewsletterHelper $newsletterHelper,
        Session $customerSession,
        Escaper $escaper,
        Address $address,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_configHelper = $configHelper;
        $this->_newsletterHelper = $newsletterHelper;
        $this->_customerSession = $customerSession;
        $this->_escaper = $escaper;
        $this->_address = $address;
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
     * Returns the client info data.
     */
    public function getClientInfoData()
    {
        if (!$this->_customerSession->isLoggedIn()) {
            return false;
        }
        $customer = $this->_customerSession->getCustomer();
        if (!$customer) {
            return false;
        }
        $connectifCustomer = array();
        $connectifCustomer['primary_key'] = $customer->getEmail();
        $connectifCustomer['_name'] = $customer->getFirstname();
        $connectifCustomer['_surname'] = $customer->getLastname();
        $connectifCustomer['_birthdate'] = $customer->getDob();
        // TODO: Maybe we shouldn't return unsubscribed if it was never opted in, it should be 'none' instead
        $connectifCustomer['_emailStatus'] = $this->_newsletterHelper->isOptedIn($customer->getEmail()) ? 'subscribed' : 'unsubscribed';
        $genderText = $customer->getResource()
            ->getAttribute('gender')
            ->getSource()
            ->getOptionText($customer->getData('gender'));
        $connectifCustomer['gender'] = $genderText;
        $customerAddress = $customer->getDefaultBilling();
        $address = $this->_address->load($customerAddress);
        if ($address) {
            $connectifCustomer['company'] = $address->getCompany();
            $connectifCustomer['zip'] = $address->getPostcode();
            $connectifCustomer['city'] = $address->getCity();
            $connectifCustomer['street'] = $address->getStreet()[0];
            $connectifCustomer['telephone'] = $address->getTelephone();
            $connectifCustomer['fax'] = $address->getFax();
            $connectifCustomer['country'] = $address->getCountry();
        }
        return $connectifCustomer;
    }

    public function getHtmlEscaper()
    {
        return $this->_escaper;
    }
}

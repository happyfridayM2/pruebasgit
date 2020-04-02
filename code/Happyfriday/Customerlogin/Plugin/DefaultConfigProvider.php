<?php

namespace Happyfriday\Customerlogin\Plugin;

use \Magento\Framework\App\Config\ScopeConfigInterface;
use \Magento\Payment\Model\Config;

class DefaultConfigProvider
{
	/**
     * @var ScopeConfigInterface
     */
    protected $_appConfigScopeConfigInterface;
    /**
     * @var Config
     */
    protected $_paymentModelConfig;

    protected $shipconfig;
 	
 	protected $storeManager;

 	protected $_currency;


    /**
     * @param ScopeConfigInterface $appConfigScopeConfigInterface
     * @param Config               $paymentModelConfig
     */
    public function __construct(
        ScopeConfigInterface $appConfigScopeConfigInterface,
        Config $paymentModelConfig,
        \Magento\Shipping\Model\Config $shipconfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Directory\Model\Currency $currency
    ) {
 
        $this->_appConfigScopeConfigInterface = $appConfigScopeConfigInterface;
        $this->_paymentModelConfig = $paymentModelConfig;
        $this->shipconfig = $shipconfig;
        $this->storeManager = $storeManager;
        $this->_currency = $currency;
    }
	
	public function afterGetConfig
	(
		\Magento\Checkout\Model\DefaultConfigProvider $subject,
		array $result
	) {
		$activeCarriers = $this->shipconfig->getActiveCarriers();
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;

        $currencyCode = $this->storeManager->getStore()->getCurrentCurrencyCode();
        $currencyData = $this->_currency->load($currencyCode);
        $currencySymbol = $currencyData->getCurrencySymbol();

		$defaultPaymentMethod = $this->_appConfigScopeConfigInterface->getValue('iwd_opc/extended/default_payment_method');

		$shippingMethods = [];
        $unwanted_shipping_methods = ['sfm_marketplace'];
		foreach ($activeCarriers as $carrier) {
			if (!in_array($carrier->getId(), $unwanted_shipping_methods)) {
				$carrierTitle = $this->_appConfigScopeConfigInterface
		                ->getValue('carriers/'.$carrier->getId().'/title');

		        $shippingMessage = 'Shipping in 1-5 business days';
				$shippingMethods[] = array(
					'label' => $carrierTitle,
					'value' => $carrierTitle,
					//'value' => $carrier->getId(),
					'message' => $shippingMessage
				);
			}
		}

		$payments = $this->_paymentModelConfig->getActiveMethods();
        $paymentMethods = array();
        $currentStore = $this->storeManager->getStore();
        $mediaUrl = $currentStore->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        $paypalImage = $mediaUrl . 'wysiwyg/paypal.png';
        $paypalMessage = 'With your credit or debit card';
        $redsysImage = $mediaUrl . 'wysiwyg/icon_cards.jpg';
        $redsysMessage = 'Fill in the fields to complete the payment';

        $unwanted_payment_methods = ['free', 'sfm_shopping_feed_order'];
        foreach ($payments as $paymentCode => $paymentModel) {
        	if (!in_array($paymentCode, $unwanted_payment_methods)) {
	            $paymentTitle = $this->_appConfigScopeConfigInterface
	                ->getValue('payment/'.$paymentCode.'/title');

	            if ($paymentCode == 'paypal_billing_agreement') {
		            $paymentMessage = $paypalMessage;
		            $paymentImage = $paypalImage;
		            $paymentCode = "paypal_express";
		            $paymentTitle = "Paypal";
	            }
	        	else if ($paymentCode == 'redsys') {
		            $paymentMessage = $redsysMessage;
		            $paymentImage = $redsysImage;
	            }
	            else {
	            	$paymentMessage = '';
		            $paymentImage = '';
	            }

	            $paymentMethods[] = array(
	                'label' => $paymentTitle,
	                'value' => $paymentCode,
	                'message' => $paymentMessage,
	                'image' => $paymentImage
	            );
        	}
        }

        $selectedShippingRate = $result['selectedShippingMethod']['base_amount'];
        $selectedShippingRate = number_format((float)$selectedShippingRate, 2, '.', '');

		$result['selectedShippingRate'] = $currencySymbol . $selectedShippingRate;
		$result['activeShippingMethods'] = $shippingMethods;
		$result['activePaymentMethods'] = $paymentMethods;
		$result['defaultPaymentMethod'] = $defaultPaymentMethod;

		return $result;
	}
}
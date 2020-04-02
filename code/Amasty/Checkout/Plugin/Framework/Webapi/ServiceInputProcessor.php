<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Checkout
 */


namespace Amasty\Checkout\Plugin\Framework\WebApi;

use Magento\Checkout\Model\Session as CheckoutSession;
use Amasty\Checkout\Model\ResourceModel\QuoteCustomFields as QuoteCustomFieldsResource;
use Amasty\Checkout\Model\QuoteCustomFieldsFactory;
use Amasty\Checkout\Model\ResourceModel\QuoteCustomFields\CollectionFactory;
use Amasty\Checkout\Api\Data\CustomFieldsConfigInterface;
use Amasty\Checkout\Model\CheckoutAddressDataManagement;

/**
 * Class ServiceInputProcessor
 */
class ServiceInputProcessor
{
    /**#@+*/
    const METHOD = 'method';
    const BILLING_TYPE_METHOD = 'billing';
    const SHIPPING_TYPE_METHOD = 'shipping';
    /**#@-*/

    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * @var QuoteCustomFieldsResource
     */
    private $quoteCustomFieldsResource;

    /**
     * @var QuoteCustomFieldsFactory
     */
    private $quoteCustomFieldsFactory;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var CheckoutAddressDataManagement
     */
    private $checkoutAddressManager;

    public function __construct(
        CheckoutSession $checkoutSession,
        QuoteCustomFieldsResource $quoteCustomFieldsResource,
        QuoteCustomFieldsFactory $quoteCustomFieldsFactory,
        CollectionFactory $collectionFactory,
        CheckoutAddressDataManagement $checkoutAddressManager
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->quoteCustomFieldsResource = $quoteCustomFieldsResource;
        $this->quoteCustomFieldsFactory = $quoteCustomFieldsFactory;
        $this->collectionFactory = $collectionFactory;
        $this->checkoutAddressManager = $checkoutAddressManager;
    }

    /**
     * @param \Magento\Framework\Webapi\ServiceInputProcessor $subject
     * @param string $serviceClassName
     * @param string $serviceMethodName
     * @param array $inputArray
     *
     * @return array
     */
    public function beforeProcess(
        \Magento\Framework\Webapi\ServiceInputProcessor $subject,
        $serviceClassName,
        $serviceMethodName,
        array $inputArray
    ) {

        if (isset($inputArray['address']['custom_attributes'])) {
            $customAttributes = $this->checkoutAddressManager
                ->prepareAddressData($inputArray['address']['custom_attributes']);
            $customAttributes[self::METHOD] = self::SHIPPING_TYPE_METHOD;
        } elseif (isset($inputArray['billingAddress']['customAttributes'])) {
            $customAttributes = $this->checkoutAddressManager
                ->prepareAddressData($inputArray['billingAddress']['customAttributes']);
            $customAttributes[self::METHOD] = self::BILLING_TYPE_METHOD;
        } elseif (isset($inputArray['address']['customAttributes'])) {
            $customAttributes = $this->checkoutAddressManager
                ->prepareAddressData($inputArray['address']['customAttributes']);
            $customAttributes[self::METHOD] = self::BILLING_TYPE_METHOD;
        } else {
            return [$serviceClassName, $serviceMethodName, $inputArray];
        }

        $countOfCustomFields = CustomFieldsConfigInterface::COUNT_OF_CUSTOM_FIELDS;
        $index = CustomFieldsConfigInterface::CUSTOM_FIELD_INDEX;

        for ($index; $index <= $countOfCustomFields; $index++) {
            $customFieldIndex = 'custom_field_' . $index;

            if (!$data = $this->getCustomFieldData($customAttributes, $customFieldIndex)) {
                continue;
            }

            /** @var \Magento\Quote\Model\Quote $quote */
            $quote = $this->checkoutSession->getQuote();

            /** @var \Amasty\Checkout\Model\ResourceModel\QuoteCustomFields\Collection $customFieldsCollection */
            $customFieldsCollection = $this->collectionFactory->create();

            /** @var \Amasty\Checkout\Model\QuoteCustomFields $quoteCustomField */
            $quoteCustomField = $this->quoteCustomFieldsFactory->create();

            $customFieldsCollection->addFilterByQuoteIdAndCustomField($quote->getId(), $customFieldIndex);

            if ($customFieldsCollection->getSize()) {
                $quoteCustomField = $customFieldsCollection->getFirstItem();
            }

            $data['name'] = $customFieldIndex;
            $data['quote_id'] = $quote->getId();

            $quoteCustomField->addData($data);
            $this->quoteCustomFieldsResource->save($quoteCustomField);
        }

        return [$serviceClassName, $serviceMethodName, $inputArray];
    }

    /**
     * @param array $customAttributes
     * @param string $customFieldIndex
     *
     * @return array
     */
    private function getCustomFieldData($customAttributes, $customFieldIndex)
    {
        $data = [];

        foreach ($customAttributes as $key => $value) {
            if (strpos($key, $customFieldIndex) !== false) {
                if ($customAttributes[self::METHOD] === self::SHIPPING_TYPE_METHOD) {
                    $data['shipping_value'] = $value;
                } elseif ($customAttributes[self::METHOD] === self::BILLING_TYPE_METHOD) {
                    $data['billing_value'] = $value;
                }
            }
        }

        return $data;
    }
}

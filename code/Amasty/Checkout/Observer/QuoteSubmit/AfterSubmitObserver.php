<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Checkout
 */


namespace Amasty\Checkout\Observer\QuoteSubmit;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;
use Amasty\Checkout\Api\AdditionalFieldsManagementInterface;
use Amasty\Checkout\Model\Subscription;
use Amasty\Checkout\Model\FeeRepository;
use Amasty\Checkout\Model\Delivery;
use Amasty\Checkout\Model\ResourceModel\Delivery as DeliveryResource;
use Amasty\Checkout\Model\Config;
use Amasty\Checkout\Model\ResourceModel\QuoteCustomFields\CollectionFactory;
use Amasty\Checkout\Model\OrderCustomFieldsFactory;
use Amasty\Checkout\Model\ResourceModel\OrderCustomFields;

/**
 * Class AfterSubmitObserver
 */
class AfterSubmitObserver implements ObserverInterface
{
    /**
     * @var AdditionalFieldsManagementInterface
     */
    private $fieldsManagement;

    /**
     * @var Subscription
     */
    private $subscription;

    /**
     * @var FeeRepository
     */
    private $feeRepository;

    /**
     * @var Delivery
     */
    private $delivery;

    /**
     * @var DeliveryResource
     */
    private $deliveryResource;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var CollectionFactory
     */
    private $quoteCollectionFactory;

    /**
     * @var OrderCustomFieldsFactory
     */
    private $orderCustomFieldsFactory;

    /**
     * @var OrderCustomFields
     */
    private $orderCustomFieldsResource;

    public function __construct(
        AdditionalFieldsManagementInterface $fieldsManagement,
        Subscription $subscription,
        FeeRepository $feeRepository,
        Delivery $delivery,
        DeliveryResource $deliveryResource,
        Config $config,
        CollectionFactory $quoteCollectionFactory,
        OrderCustomFieldsFactory $orderCustomFieldsFactory,
        OrderCustomFields $orderCustomFieldsResource
    ) {
        $this->fieldsManagement = $fieldsManagement;
        $this->subscription = $subscription;
        $this->feeRepository = $feeRepository;
        $this->delivery = $delivery;
        $this->deliveryResource = $deliveryResource;
        $this->config = $config;
        $this->quoteCollectionFactory = $quoteCollectionFactory;
        $this->orderCustomFieldsFactory = $orderCustomFieldsFactory;
        $this->orderCustomFieldsResource = $orderCustomFieldsResource;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(EventObserver $observer)
    {
        if (!$this->config->isEnabled()) {
            return $this;
        }
        /** @var  \Magento\Sales\Model\Order $order */
        $order = $observer->getEvent()->getOrder();
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $observer->getEvent()->getQuote();
        if (!$order) {
            return $this;
        }

        $orderId = $order->getId();
        $quoteId = $quote->getId();

        $fee = $this->feeRepository->getByQuoteId($quoteId);
        if ($fee->getId()) {
            $fee->setOrderId($orderId);
            $this->feeRepository->save($fee);
        }

        $delivery = $this->delivery->findByQuoteId($quoteId);

        if ($delivery->getId()) {
            $delivery->setData('order_id', $orderId);
            $this->deliveryResource->save($delivery);
        }

        $fields = $this->fieldsManagement->getByQuoteId($quoteId);

        $this->convertCustomFields($quoteId, $orderId);

        if (!$fields->getId()) {
            return $this;
        }

        if ($fields->getSubscribe()) {
            $this->subscription->subscribe($order->getCustomerEmail());
        }

        return $this;
    }

    /**
     * Convert Custom Fields from Quote to Order
     *
     * @param int $quoteId
     * @param int $orderId
     */
    private function convertCustomFields($quoteId, $orderId)
    {
        /** @var \Amasty\Checkout\Model\ResourceModel\QuoteCustomFields\Collection $quoteCustomFiledsCollection */
        $quoteCustomFiledsCollection = $this->quoteCollectionFactory->create();

        $quoteCustomFiledsCollection->addFieldByQuoteId($quoteId);

        /** @var \Amasty\Checkout\Model\QuoteCustomFields $quoteCustomFiled */
        foreach ($quoteCustomFiledsCollection->getItems() as $quoteCustomFiled) {
            /** @var \Amasty\Checkout\Model\OrderCustomFields $orderCustomField */
            $orderCustomField = $this->orderCustomFieldsFactory->create();

            $orderCustomField->setBillingValue($quoteCustomFiled->getBillingValue());
            $orderCustomField->setShippingValue($quoteCustomFiled->getShippingValue());
            $orderCustomField->setName($quoteCustomFiled->getName());
            $orderCustomField->setOrderId($orderId);

            $this->orderCustomFieldsResource->save($orderCustomField);
        }
    }
}

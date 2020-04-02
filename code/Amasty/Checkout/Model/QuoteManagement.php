<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Checkout
 */


namespace Amasty\Checkout\Model;

use Amasty\Checkout\Api\QuoteManagementInterface;
use Magento\Customer\Model\Address;
use Magento\Customer\Model\Session;
use Magento\Framework\ObjectManagerInterface;
use Magento\Quote\Api\BillingAddressManagementInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\ShippingAddressManagementInterface;
use Magento\Quote\Model\ResourceModel\Quote\Address as ResourceAddress;
use Psr\Log\LoggerInterface;

/**
 * Class QuoteManagement
 *
 * @SuppressWarnings(PHPMD.LongVariable)
 * @codingStandardsIgnoreStart
 */
class QuoteManagement implements QuoteManagementInterface
{
    /**
     * @var CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var ShippingAddressManagementInterface
     */
    private $shippingAddressManagement;

    /**
     * @var BillingAddressManagementInterface
     */
    private $billingAddressManagement;

    /**
     * @var Session
     */
    private $session;

    /**
     * @var ResourceAddress
     */
    private $address;

    /**
     * @var Amazon\Login\Helper\Session|null
     */
    private $amazonSession = null;

    public function __construct(
        LoggerInterface $logger,
        CartRepositoryInterface\Proxy $quoteRepository,
        ShippingAddressManagementInterface\Proxy $shippingAddressManagement,
        BillingAddressManagementInterface\Proxy $billingAddressManagement,
        ResourceAddress $address,
        Session $session,
        ObjectManagerInterface $objectManager
    ) {
        $this->logger = $logger;
        $this->quoteRepository = $quoteRepository;
        $this->shippingAddressManagement = $shippingAddressManagement;
        $this->billingAddressManagement = $billingAddressManagement;
        $this->address = $address;
        $this->session = $session;
        if (class_exists(\Amazon\Login\Helper\Session::class)) {
            $this->amazonSession = $objectManager->create(\Amazon\Login\Helper\Session::class);
        }
    }

    /**
     * @inheritdoc
     */
    function saveInsertedInfo(
        $cartId,
        AddressInterface $shippingAddressFromData = null,
        AddressInterface $newCustomerBillingAddress = null,
        $selectedPaymentMethod = null,
        $selectedShippingRate = null,
        $validatedEmailValue = null
    ) {
        $quote = null;

        if (($this->amazonSession === null || ($this->amazonSession && !$this->amazonSession->isAmazonLoggedIn())) && $this->session->isLoggedIn()) {
            list($shippingAddressFromData, $newCustomerBillingAddress) = $this->retrieveAddressFromCustomer(
                $cartId,
                $shippingAddressFromData,
                $newCustomerBillingAddress
            );
        }

        if ($validatedEmailValue) {
            $shippingAddressFromData->setEmail($validatedEmailValue);
        }

        if ($selectedShippingRate) {
            /** @var Quote $quote */
            $quote = $this->quoteRepository->getActive($cartId);
            $shippingAddressFromData->setShippingMethod($selectedShippingRate);
            $shippingAddressFromData->setShippingDescription($quote->getShippingAddress()->getShippingDescription());
        }

        $this->saveInfo($cartId, $shippingAddressFromData, $newCustomerBillingAddress, $selectedPaymentMethod, $quote);

        return true;
    }

    /**
     * @param int $cartId
     * @param AddressInterface|null $shippingAddressFromData
     * @param AddressInterface|null $newCustomerBillingAddress
     *
     * @return array
     */
    private function retrieveAddressFromCustomer(
        $cartId,
        AddressInterface $shippingAddressFromData = null,
        AddressInterface $newCustomerBillingAddress = null
    ) {
        if ($shippingAddressFromData && $newCustomerBillingAddress) {
            return [$shippingAddressFromData, $newCustomerBillingAddress];
        }

        $customerAddresses = $this->session->getCustomerData()->getAddresses();
        $billingAddress = [];
        $shippingAddress = [];

        /** @var Address $customerAddress */
        foreach ($customerAddresses as $customerAddress) {
            if ($customerAddress->isDefaultBilling()) {
                $billingAddress = $customerAddress->__toArray();
            }

            if ($customerAddress->isDefaultShipping()) {
                $shippingAddress = $customerAddress->__toArray();
            }
        }

        if ($newCustomerBillingAddress === null) {
            /** @var AddressInterface $newCustomerBillingAddress */
            $newCustomerBillingAddress = $this->billingAddressManagement->get($cartId);
            $newCustomerBillingAddress->addData($billingAddress);
        }

        if ($shippingAddressFromData === null) {
            /** @var AddressInterface $shippingAddressFromData */
            $shippingAddressFromData = $this->shippingAddressManagement->get($cartId);
            $shippingAddressFromData->addData($shippingAddress);
        }

        return [$shippingAddressFromData, $newCustomerBillingAddress];
    }

    /**
     * @param int $cartId
     * @param AddressInterface|null $shippingAddressFromData
     * @param AddressInterface|null $newCustomerBillingAddress
     * @param string|null $selectedPaymentMethod
     * @param Quote $quote
     */
    private function saveInfo(
        $cartId,
        AddressInterface $shippingAddressFromData = null,
        AddressInterface $newCustomerBillingAddress = null,
        $selectedPaymentMethod = null,
        Quote $quote = null
    ) {
        try {
            if ($shippingAddressFromData) {
                $this->shippingAddressManagement->assign($cartId, $shippingAddressFromData);
            }

            if ($newCustomerBillingAddress) {
                $this->address->save($newCustomerBillingAddress);
            }

            if ($selectedPaymentMethod) {
                if (!$quote) {
                    /** @var Quote $quote */
                    $quote = $this->quoteRepository->getActive($cartId);
                }

                $quote->getPayment()->setMethod($selectedPaymentMethod);
                $quote->setDataChanges(true);
                $this->quoteRepository->save($quote);
            }
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }
    }
}

<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Checkout
 */


namespace Amasty\Checkout\Model;

use Amasty\Checkout\Api\GuestQuoteManagementInterface;
use Amasty\Checkout\Api\QuoteManagementInterface;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Model\QuoteIdMask;
use Magento\Quote\Model\QuoteIdMaskFactory;

/**
 * Class GuestQuoteManagement
 *
 * @SuppressWarnings(PHPMD.LongVariable)
 * @codingStandardsIgnoreStart
 */
class GuestQuoteManagement implements GuestQuoteManagementInterface
{
    /**
     * @var QuoteIdMaskFactory
     */
    private $quoteIdMaskFactory;

    /**
     * @var QuoteManagementInterface
     */
    private $quoteManagement;

    public function __construct(
        QuoteIdMaskFactory $quoteIdMaskFactory,
        QuoteManagementInterface $quoteManagement
    ) {
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
        $this->quoteManagement = $quoteManagement;
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
        /** @var $quoteIdMask QuoteIdMask */
        $quoteIdMask = $this->quoteIdMaskFactory->create()->load($cartId, 'masked_id');

        return $this->quoteManagement->saveInsertedInfo(
            $quoteIdMask->getQuoteId(),
            $shippingAddressFromData,
            $newCustomerBillingAddress,
            $selectedPaymentMethod,
            $selectedShippingRate,
            $validatedEmailValue
        );
    }
}

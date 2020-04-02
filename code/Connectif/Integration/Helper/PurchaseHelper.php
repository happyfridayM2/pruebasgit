<?php

namespace Connectif\Integration\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Connectif\Integration\Helper\ProductHelper;

/**
 * Helper class for Purchase related features.
 */
class PurchaseHelper extends AbstractHelper
{
    protected $_productHelper;

    /**
     * @param Context $context
     */
    public function __construct(
        Context $context,
        ProductHelper $productHelper
    ) {
        parent::__construct($context);
        $this->_productHelper = $productHelper;
    }
    /**
     * Get all order products
     */
    public function getOrderData($order)
    {
        $orderItems = $order->getAllVisibleItems();
        $orderProducts = array();
        $totalQuantity = 0;

        foreach ($orderItems as $item) {
            if (isset($item->getProductOptions()['simple_sku'])) {
                $product = $this->_productHelper->getProductDataBySKU($item->getProductOptions()['simple_sku']);
            } else {
                $product = $this->_productHelper->getProductDataById($item->getProductId());
            }
            
            $product['quantity'] = (int) $item->getQtyOrdered();
            $product['price'] = (float) $item->getQtyOrdered() * (float) $product['unit_price'];
            $totalQuantity += $product['quantity'];
            array_push($orderProducts, $product);
        }

        $payment = $order->getPayment();
        $paymentMethod = $payment->getMethodInstance()->getTitle();

        $cartId = $order->getQuoteId();

        $connectifOrder = array();
        $connectifOrder['cartId'] = $cartId;
        $connectifOrder['purchaseId'] = $order->getIncrementId();
        $connectifOrder['purchaseDate'] = date(DATE_ISO8601, strtotime($order->getCreatedAtStoreDate()));
        $connectifOrder['totalQuantity'] = $totalQuantity;
        $connectifOrder['totalPrice'] = (float) $order->getGrandTotal();
        $connectifOrder['paymentMethod'] = $paymentMethod;
        $connectifOrder['products'] = $orderProducts;
        return $connectifOrder;
    }
}

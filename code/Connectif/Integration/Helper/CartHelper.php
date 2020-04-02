<?php

namespace Connectif\Integration\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Connectif\Integration\Helper\ProductHelper;

/**
 * Helper class for Cart related features.
 */
class CartHelper extends AbstractHelper
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
     * Get all cart products
     */
    public function getCartData($cart)
    {
        $cartProducts = array();
        $connectifCart = array();
        $totalQuantity = 0;
        
        if (!isset($cart)) {
            return $connectifCart;
        }

        $cartItems = $cart->getAllVisibleItems();
        foreach ($cartItems as $key => $item) {
            // TODO add module config option to set only mandatory fields on prodcuts in cart
            $product = $this->_productHelper->getProductData($item->getProduct());
            $product['quantity'] = (int) $item->getQty();
            $totalQuantity += $product['quantity'];
            $product['price'] = (float) $item->getQty() * (float) $product['unit_price'];
            array_push($cartProducts, $product);
        }

        $cartId = $cart->getEntityId();

        $connectifCart['cartId'] = $cartId;
        $connectifCart['totalQuantity'] = $totalQuantity;
        $connectifCart['totalPrice'] = (float) $cart->getGrandTotal();
        $connectifCart['products'] = $cartProducts;
        return $connectifCart;
    }
}

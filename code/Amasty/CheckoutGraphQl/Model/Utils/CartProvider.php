<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_CheckoutGraphQl
 */


namespace Amasty\CheckoutGraphQl\Model\Utils;

use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Quote\Model\Quote;
use Magento\QuoteGraphQl\Model\Cart\GetCartForUser;

class CartProvider
{
    const CART_ID_KEY = 'cart_id';

    /**
     * @var GetCartForUser
     */
    private $getCartForUser;

    /**
     * @var ProductMetadataInterface
     */
    private $productMetadata;

    public function __construct(
        GetCartForUser $getCartForUser,
        ProductMetadataInterface $productMetadata
    ) {
        $this->getCartForUser = $getCartForUser;
        $this->productMetadata = $productMetadata;
    }

    /**
     * @param string $cartId
     * @param ContextInterface $context
     * @return Quote
     * @throws GraphQlAuthorizationException
     * @throws GraphQlNoSuchEntityException
     * @throws NoSuchEntityException
     */
    public function getCartForUser($cartId, $context): Quote
    {
        if (version_compare($this->productMetadata->getVersion(), '2.3.3', '<')) {
            return $this->getCartForUser->execute($cartId, $context->getUserId());
        } else {
            return $this->getCartForUser->execute(
                $cartId,
                $context->getUserId(),
                $context->getExtensionAttributes()->getStore()->getId()
            );
        }
    }
}

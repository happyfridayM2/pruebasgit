<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_CheckoutGraphQl
 */


namespace Amasty\CheckoutGraphQl\Model\Resolver;

use Amasty\Checkout\Api\Data\FeeInterface;
use Amasty\Checkout\Api\FeeRepositoryInterface;
use Amasty\CheckoutGraphQl\Model\Utils\CartProvider;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

class GetGiftWrapInformation implements ResolverInterface
{
    /**
     * @var CartProvider
     */
    private $cartProvider;

    /**
     * @var FeeRepositoryInterface
     */
    private $feeRepository;

    public function __construct(CartProvider $cartProvider, FeeRepositoryInterface $feeRepository)
    {
        $this->cartProvider = $cartProvider;
        $this->feeRepository = $feeRepository;
    }

    /**
     * @param Field $field
     * @param ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return FeeInterface|Value|mixed
     * @throws GraphQlInputException
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        if (empty($args['input'][CartProvider::CART_ID_KEY])) {
            throw new GraphQlInputException(__('Required parameter "%1" is missing', CartProvider::CART_ID_KEY));
        }

        $cart = $this->cartProvider->getCartForUser($args['input'][CartProvider::CART_ID_KEY], $context);

        try {
            $fee = $this->feeRepository->getByQuoteId($cart->getId());
        } catch (LocalizedException $e) {
            throw new GraphQlInputException(__($e->getMessage()), $e);
        }

        return $fee;
    }
}

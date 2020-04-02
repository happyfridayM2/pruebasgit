<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_CheckoutGraphQl
 */


namespace Amasty\CheckoutGraphQl\Model\Resolver;

use Amasty\Checkout\Model\Config;
use Amasty\CheckoutGraphQl\Model\Utils\CartProvider;
use Amasty\CheckoutGraphQl\Model\Utils\GiftMessageProvider;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\GiftMessage\Api\CartRepositoryInterface;

class AddGiftMessageForWholeOrder implements ResolverInterface
{
    /**
     * @var CartProvider
     */
    private $cartProvider;

    /**
     * @var CartRepositoryInterface
     */
    private $cartRepository;

    /**
     * @var GiftMessageProvider
     */
    private $giftMessageProvider;

    /**
     * @var Config
     */
    private $configProvider;

    public function __construct(
        CartProvider $cartProvider,
        CartRepositoryInterface $cartRepository,
        GiftMessageProvider $giftMessageProvider,
        Config $configProvider
    ) {
        $this->cartProvider = $cartProvider;
        $this->cartRepository = $cartRepository;
        $this->giftMessageProvider = $giftMessageProvider;
        $this->configProvider = $configProvider;
    }

    /**
     * @param Field $field
     * @param ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return array|Value|mixed
     * @throws GraphQlInputException
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        if (!$this->configProvider->getMagentoConfigValue('sales/gift_options/allow_order')) {
            return __('Gift message for whole order is not allowed.');
        }

        if (empty($args['input'][CartProvider::CART_ID_KEY])) {
            throw new GraphQlInputException(__('Required parameter "%1" is missing', CartProvider::CART_ID_KEY));
        }

        $cart = $this->cartProvider->getCartForUser($args['input'][CartProvider::CART_ID_KEY], $context);
        $message = $this->giftMessageProvider->prepareGiftMessage($args['input']);

        try {
            $this->cartRepository->save($cart->getId(), $message);
        } catch (LocalizedException $e) {
            throw new GraphQlInputException(__($e->getMessage()), $e);
        }

        return __('Gift message for whole order was applied.');
    }
}

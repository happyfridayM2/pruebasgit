<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_CheckoutGraphQl
 */


namespace Amasty\CheckoutGraphQl\Model\Resolver;

use Amasty\Checkout\Api\GiftWrapInformationManagementInterface;
use Amasty\Checkout\Model\Config;
use Amasty\CheckoutGraphQl\Model\Utils\CartProvider;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\Phrase;

class UpdateGiftWrapInformation implements ResolverInterface
{
    const CHECKED_KEY = 'checked';

    /**
     * @var CartProvider
     */
    private $cartProvider;

    /**
     * @var Config
     */
    private $configProvider;

    /**
     * @var GiftWrapInformationManagementInterface
     */
    private $giftWrapInformationManagement;

    public function __construct(
        CartProvider $cartProvider,
        Config $configProvider,
        GiftWrapInformationManagementInterface $giftWrapInformationManagement
    ) {
        $this->cartProvider = $cartProvider;
        $this->configProvider = $configProvider;
        $this->giftWrapInformationManagement = $giftWrapInformationManagement;
    }

    /**
     * @param Field $field
     * @param ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return Value|Phrase|mixed
     * @throws GraphQlInputException
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        if (!$this->configProvider->isGiftWrapEnabled()) {
            return __('Gift wrap is not allowed.');
        }

        if (empty($args['input'][CartProvider::CART_ID_KEY])) {
            throw new GraphQlInputException(__('Required parameter "%1" is missing', CartProvider::CART_ID_KEY));
        }

        if (!isset($args['input'][self::CHECKED_KEY])) {
            throw new GraphQlInputException(__('Required parameter "%1" is missing', self::CHECKED_KEY));
        }

        $cart = $this->cartProvider->getCartForUser($args['input'][CartProvider::CART_ID_KEY], $context);

        try {
            $this->giftWrapInformationManagement->update($cart->getId(), $args['input'][self::CHECKED_KEY]);
        } catch (LocalizedException $e) {
            throw new GraphQlInputException(__($e->getMessage()), $e);
        }

        return __('Gift wrap status was changed.');
    }
}

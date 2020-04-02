<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_CheckoutGraphQl
 */


namespace Amasty\CheckoutGraphQl\Model\Resolver;

use Amasty\Checkout\Api\DeliveryInformationManagementInterface;
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

class UpdateDeliveryInformation implements ResolverInterface
{
    const DATE_KEY = 'date';
    const TIME_KEY = 'time';
    const COMMENT_KEY = 'comment';

    /**
     * @var CartProvider
     */
    private $cartProvider;

    /**
     * @var Config
     */
    private $configProvider;

    /**
     * @var DeliveryInformationManagementInterface
     */
    private $deliveryInformationManagement;

    public function __construct(
        CartProvider $cartProvider,
        Config $configProvider,
        DeliveryInformationManagementInterface $deliveryInformationManagement
    ) {
        $this->cartProvider = $cartProvider;
        $this->configProvider = $configProvider;
        $this->deliveryInformationManagement = $deliveryInformationManagement;
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
        if (!$this->configProvider->getDeliveryDateConfig('enabled')) {
            return __('Delivery date is not allowed.');
        }

        if (empty($args['input'][CartProvider::CART_ID_KEY])) {
            throw new GraphQlInputException(__('Required parameter "%1" is missing', CartProvider::CART_ID_KEY));
        }

        if (empty($args['input'][self::DATE_KEY])) {
            throw new GraphQlInputException(__('Required parameter "%1" is missing', self::DATE_KEY));
        }

        $time = empty($args['input'][self::TIME_KEY]) ? '' : $args['input'][self::TIME_KEY];
        $comment = '';

        if ($this->configProvider->getDeliveryDateConfig('delivery_comment_enable')
            && !empty($args['input'][self::COMMENT_KEY])) {
            $comment = $args['input'][self::COMMENT_KEY];
        }

        $date = $args['input'][self::DATE_KEY];
        $cart = $this->cartProvider->getCartForUser($args['input'][CartProvider::CART_ID_KEY], $context);

        try {
            $this->deliveryInformationManagement->update($cart->getId(), $date, $time, $comment);
        } catch (LocalizedException $e) {
            throw new GraphQlInputException(__($e->getMessage()), $e);
        }

        return __('Delivery date was changed.');
    }
}

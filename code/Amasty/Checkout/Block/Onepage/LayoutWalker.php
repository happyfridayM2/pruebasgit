<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Checkout
 */


namespace Amasty\Checkout\Block\Onepage;

/**
 * Class LayoutWalker
 */
class LayoutWalker
{
    /**
     * @var array
     */
    private $layoutArray;

    /**
     * Path templates
     *
     * @var array
     */
    private $componentPathRegistry = [
        '{GIFT_WRAP}' => '{ADDITIONAL_STEP}.>>.checkboxes.>>.gift_wrap',
        '{SHIPPING_ADDRESS_FIELDSET}' => '{SHIPPING_ADDRESS}.>>.shipping-address-fieldset',
        '{AMCHECKOUT_DELIVERY_DATE}' => '{CHECKOUT}.>>.steps.>>.shipping-step.>>.amcheckout-delivery-date',
        '{SHIPPING_ADDRESS}' => '{CHECKOUT}.>>.steps.>>.shipping-step.>>.shippingAddress',
        '{GIFT_MESSAGE_CONTAINER}' => '{ADDITIONAL_STEP}.>>.checkboxes.>>.gift_message_container',
        '{PAYMENT}' => '{BILLING_STEP}.>>.payment',
        '{CART_ITEMS}' => '{SIDEBAR}.>>.summary.>>.cart_items',
        '{BILLING_STEP}' => '{CHECKOUT}.>>.steps.>>.billing-step',
        '{ADDITIONAL_STEP}' => '{SIDEBAR}.>>.additional', //additional summary fields
        '{SIDEBAR}' => '{CHECKOUT}.>>.sidebar',
        '{CHECKOUT}' => 'components.checkout'
    ];

    public function __construct(array $layoutArray)
    {
        $this->layoutArray = $layoutArray;
    }

    /**
     * isset
     *
     * @param string $path
     *
     * @return bool
     */
    public function isExist($path)
    {
        $path = $this->parseArrayPath($path);

        return $this->issetWalker($this->layoutArray, $path);
    }

    /**
     * @param string $path
     *
     * @return $this
     */
    public function setValue($path, $value)
    {
        $path = $this->parseArrayPath($path);
        $this->arrayWalker($this->layoutArray, $path, $value);

        return $this;
    }

    /**
     * @param string $path
     *
     * @return bool|null
     */
    public function getValue($path)
    {
        $path = $this->parseArrayPath($path);

        return $this->getWalker($this->layoutArray, $path);
    }

    /**
     * unset
     *
     * @param string $path
     *
     * @return $this
     */
    public function unsetByPath($path)
    {
        $path = $this->parseArrayPath($path);
        $this->unsetWalker($this->layoutArray, $path);

        return $this;
    }

    /**
     * @return array
     */
    public function getResult()
    {
        return $this->layoutArray;
    }

    /**
     * @param string $keyPath
     *
     * @return array
     */
    private function parseArrayPath($keyPath)
    {
        $keyPath = preg_replace('/[\s\n\r]/', '', $keyPath);
        $keyPath = str_replace(
            array_keys($this->componentPathRegistry),
            array_values($this->componentPathRegistry),
            $keyPath
        );
        $keyPath = str_replace('>>', 'children', $keyPath);

        return explode('.', $keyPath);
    }

    /**
     * @param array $haystack
     * @param array $path
     * @param string|int|float|bool|array|null $value
     */
    protected function arrayWalker(&$haystack, array $path, $value)
    {
        $currentKey = array_shift($path);
        if (!isset($haystack[$currentKey])) {
            $haystack[$currentKey] = [];
        }
        if (empty($path)) {
            //end of path, walk completed
            $haystack[$currentKey] = $value;
            return;
        }

        $this->arrayWalker($haystack[$currentKey], $path, $value);
    }

    /**
     * @param array $haystack
     * @param array $path
     */
    protected function unsetWalker(&$haystack, array $path)
    {
        $currentKey = array_shift($path);
        if (!isset($haystack[$currentKey])) {
            return;
        }

        if (empty($path)) {
            //end of path, walk completed
            unset($haystack[$currentKey]);
            return;
        }

        $this->unsetWalker($haystack[$currentKey], $path);
    }

    /**
     * @param array $haystack
     * @param array $path
     *
     * @return bool
     */
    protected function issetWalker(&$haystack, array $path)
    {
        $currentKey = array_shift($path);
        if (!isset($haystack[$currentKey])) {
            return false;
        }

        if (empty($path)) {
            //end of path, walk completed
            return true;
        }

        return $this->issetWalker($haystack[$currentKey], $path);
    }

    /**
     * @param array $haystack
     * @param array $path
     *
     * @return string|int|float|bool|array|null
     */
    protected function getWalker(&$haystack, array $path)
    {
        $currentKey = array_shift($path);
        if (!isset($haystack[$currentKey])) {
            return null;
        }

        if (empty($path)) {
            //end of path, walk completed
            return $haystack[$currentKey];
        }

        return $this->getWalker($haystack[$currentKey], $path);
    }
}

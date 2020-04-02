<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Conf
 */


namespace Amasty\Conf\Plugin\Checkout\Model;

use Magento\Checkout\Model\Cart as MagentoCart;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductFactory;
use Magento\Framework\Exception\LocalizedException;
use Amasty\Conf\Helper\Data;

class Cart
{
    const AMCONFIGURABLE_OPTION = 'amconfigurable-option';
    const CONFIGURABLE_OPTION = 'configurable-option';

    /**
     * @var \Magento\Framework\Locale\ResolverInterface
     */
    private $locale;

    /**
     * @var ProductFactory
     */
    private $productFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Data
     */
    private $helper;

    public function __construct(
        \Magento\Framework\Locale\ResolverInterface $locale,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        ProductFactory $productFactory,
        Data $helper
    ) {
        $this->locale = $locale;
        $this->productFactory = $productFactory;
        $this->storeManager = $storeManager;
        $this->helper = $helper;
    }

    /**
     * @param MagentoCart $subject
     * @param \Closure $closure
     * @param int|Product $productInfo
     * @param \Magento\Framework\DataObject|int|array $requestInfo
     * @return MagentoCart
     */
    public function aroundAddProduct(MagentoCart $subject, \Closure $closure, $productInfo, $requestInfo)
    {
        if (isset($requestInfo[self::AMCONFIGURABLE_OPTION])) {
            $storeId = $this->storeManager->getStore()->getId();
            $flagAddedToCart = false;
            $tmpRequest = $this->getTmpRequest($requestInfo);
            foreach ($requestInfo[self::AMCONFIGURABLE_OPTION] as $optionValues) {
                try {
                    $this->prepareRequest($optionValues, $tmpRequest);
                    if ($tmpRequest['qty'] <= 0) {
                        continue;
                    }

                    if ($productInfo instanceof Product) {
                        $productInfo = $productInfo->getId();
                    }

                    //should reinitialize product( without repository! )
                    $product = $this->productFactory->create();
                    $product->setData('store_id', $storeId)->load($productInfo);

                    $closure($product, $tmpRequest);
                    $flagAddedToCart = true;
                } catch (\Exception $ex) {
                    //skip this product and add another
                    continue;
                }
            }

            $this->throwExceptionIfNeed($flagAddedToCart);
        } else {
            $closure($productInfo, $requestInfo);
        }

        return $subject;
    }

    /**
     * @param MagentoCart $subject
     * @param \Closure $closure
     * @param int $itemId
     * @param null $requestInfo
     * @param null $updatingParams
     * @return mixed
     * @throws LocalizedException
     */
    public function aroundUpdateItem(
        MagentoCart $subject,
        \Closure $closure,
        $itemId,
        $requestInfo = null,
        $updatingParams = null
    ) {
        if (isset($requestInfo[self::AMCONFIGURABLE_OPTION])) {
            $tmpRequest = $this->getTmpRequest($requestInfo);
            $flagUpdatedCart = false;
            foreach ($requestInfo[self::AMCONFIGURABLE_OPTION] as $optionValues) {
                try {
                    $this->prepareRequest($optionValues, $tmpRequest);
                    if ($tmpRequest['qty'] <= 0) {
                        continue;
                    }

                    $result = $closure($itemId, $tmpRequest, $updatingParams);
                    $flagUpdatedCart = true;
                } catch (\Exception $ex) {
                    //skip this product and add another
                    continue;
                }
            }
            $this->throwExceptionIfNeed($flagUpdatedCart);
        } else {
            $result = $closure($itemId, $requestInfo, $updatingParams);
        }

        return $result;
    }

    /**
     * @param array $requestInfo
     * @return array
     */
    private function getTmpRequest($requestInfo)
    {
        $tmpRequest = is_object($requestInfo) ? $requestInfo->getData() : $requestInfo;
        unset($tmpRequest[self::CONFIGURABLE_OPTION]);
        unset($tmpRequest[self::AMCONFIGURABLE_OPTION]);

        return $tmpRequest;
    }

    /**
     * @param array $optionValues
     * @param array $tmpRequest
     */
    private function prepareRequest($optionValues, &$tmpRequest)
    {
        $options = $this->helper->decode($optionValues);
        $qty = $options['qty'];
        unset($options['qty']);
        $tmpRequest['super_attribute'] = [];

        foreach ($options as $attribute => $value) {
            $tmpRequest['super_attribute'][$attribute] = $value;
        }
        $filter = new \Zend_Filter_LocalizedToNormalized(
            ['locale' => $this->locale->getLocale()]
        );
        $qty = $filter->filter($qty);
        $tmpRequest['qty'] = $qty;
    }

    /**
     * @param bool $flagChangeCart
     * @throws LocalizedException
     */
    private function throwExceptionIfNeed($flagChangeCart)
    {
        if (!$flagChangeCart) {
            throw new LocalizedException(__('Please specify the quantity of product(s).'));
        }
    }
}

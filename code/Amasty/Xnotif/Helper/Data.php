<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Xnotif
 */


namespace Amasty\Xnotif\Helper;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\App\Action\Action;
use Magento\ProductAlert\Block\Product\View;
use Magento\CatalogInventory\Api\Data\StockItemInterface;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;

    /**
     * @var \Magento\Framework\View\Element\BlockFactory
     */
    private $blockFactory;

    /**
     * @var \Magento\CatalogInventory\Api\StockRegistryInterface
     */
    private $stockRegistry;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Config
     */
    private $config;

    public function __construct(
        \Magento\Framework\Registry $registry,
        \Magento\Framework\View\Element\BlockFactory $blockFactory,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Magento\Framework\App\Helper\Context $context,
        Config $config,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        parent::__construct($context);
        $this->registry = $registry;
        $this->blockFactory = $blockFactory;
        $this->stockRegistry = $stockRegistry;
        $this->storeManager = $storeManager;
        $this->config = $config;
    }

    /**
     * @param ProductInterface $product
     * @param View $alertBlock
     *
     * @return string
     */
    public function observeStockAlertBlock(ProductInterface $product, View $alertBlock)
    {
        $html = '';
        $currentProduct = $this->registry->registry('current_product');
        if (!$product->getId() || !$currentProduct) {
            return $html;
        }

        /*check if it is child product for replace product registered to child product.*/
        $isChildProduct = ($currentProduct->getId() != $product->getId());
        if ($isChildProduct) {
            $alertBlock->setData('parent_product_id', $currentProduct->getId());
            $alertBlock->setOriginalProduct($product);
        }
        $alertBlock->setSignupUrl($this->getSignupUrl(
            'stock',
            $product->getId(),
            $alertBlock->getData('parent_product_id')
        ));

        if ($alertBlock && !$product->getData('amxnotif_hide_alert')) {
            if (!$this->isLoggedIn()) {
                $alertBlock->setTemplate('Amasty_Xnotif::product/view_email.phtml');
            }

            $alertBlock->setData('amxnotif_observer_triggered', 1);
            $html = $alertBlock->toHtml();
            $alertBlock->setData('amxnotif_observer_triggered', null);
        }

        return $html;
    }

    /**
     * @param ProductInterface $product
     *
     * @return string
     */
    public function getStockAlert(ProductInterface $product)
    {
        if (!$product || !$product->getId() || !$this->config->allowForCurrentCustomerGroup('stock')) {
            return '';
        }

        $alertBlock =  $this->createDefaultAlertBlock();
        return $this->observeStockAlertBlock($product, $alertBlock);
    }

    /**
     * @return \Magento\Framework\View\Element\BlockInterface
     */
    protected function createDefaultAlertBlock()
    {
        $alertBlock = $this->blockFactory->createBlock(
            \Magento\ProductAlert\Block\Product\View::class,
            []
        );

        $alertBlock->setTemplate('Magento_ProductAlert::product/view.phtml');
        $alertBlock->setHtmlClass('alert stock link-stock-alert');
        $alertBlock->setSignupLabel(__('Sign up to get notified when this configuration is back in stock'));

        return $alertBlock;
    }

    /**
     * @param ProductInterface $product
     *
     * @return string
     */
    public function getPriceAlert(ProductInterface $product)
    {
        $html = '';
        $configurableProduct = $this->registry->registry('current_product');
        if ($configurableProduct) {
            $this->registry->unregister('current_product');
            $this->registry->register('current_product', $product);

            $alertBlock = $this->blockFactory->createBlock(
                \Magento\ProductAlert\Block\Product\View::class,
                [
                    'name' => 'productalert.price.' . $product->getId()
                ]
            );

            if ($alertBlock) {
                $alertBlock->setData('parent_product_id', $configurableProduct->getId());
                $alertBlock->setOriginalProduct($product);
                $alertBlock->setTemplate('Magento_ProductAlert::product/view.phtml');
                $alertBlock->setSignupLabel(__('Notify me when the price drops'));
            }

            $this->registry->unregister('current_product');
            $this->registry->register('current_product', $configurableProduct);
            $html = $this->observePriceAlertBlock($product, $alertBlock);
        }

        return $html;
    }

    /**
     * @param ProductInterface $product
     * @param View $alertBlock
     *
     * @return string
     */
    public function observePriceAlertBlock(ProductInterface $product, View $alertBlock)
    {
        if (!$product->getId()) {
            return '';
        }

        if ($alertBlock && !$this->isLoggedIn()) {
            /*set template with email input*/
            $alertBlock->setTemplate('Amasty_Xnotif::product/price/view_email.phtml');
            $alertBlock->setOriginalProduct($product);
        }
        $alertBlock->setSignupUrl($this->getSignupUrl(
            'price',
            $product->getId(),
            $alertBlock->getData('parent_product_id')
        ));

        $alertBlock->setData('amxnotif_observer_triggered', 1);
        $html = $alertBlock->toHtml();
        $alertBlock->setData('amxnotif_observer_triggered', null);

        return $html;
    }

    /**
     * @return mixed
     */
    public function getProduct()
    {
        return $this->registry->registry('product');
    }

    /**
     * @param $block
     *
     * @return mixed
     */
    public function getOriginalProduct($block)
    {
        $product = $block->getOriginalProduct();
        if (!$product) {
            $product = $this->getProduct();
        }

        return $product;
    }

    /**
     * @param $type
     * @param int $productId
     * @param null|int $parentId
     * @param bool $addUencInUrl
     *
     * @return string
     */
    public function getSignupUrl($type, $productId, $parentId = null, $addUencInUrl = true)
    {
        $params = ['product_id' => $productId];
        if ($addUencInUrl) {
            $params[Action::PARAM_NAME_URL_ENCODED] = $this->getEncodedUrl();
        }

        if ($parentId) {
            $params['parent_id'] = $parentId;
        }

        return $this->_getUrl('xnotif/email/' . $type, $params);
    }

    /**
     * @param $type
     * @return string
     */
    public function getEmailUrl($type)
    {
        return $this->_getUrl('xnotif/email/' . $type);
    }

    /**
     * @param $route
     * @param array $params
     * @return string
     */
    public function getUrl($route, $params = [])
    {
        return parent::_getUrl($route, $params);
    }

    /**
     * @param $item
     * @param $websiteId
     *
     * @return bool
     */
    public function isItemSalable($item, $websiteId = null)
    {
        if ($websiteId === null) {
            $websiteId = $this->storeManager->getWebsite()->getId();
        }
        /** @var \Magento\CatalogInventory\Api\Data\StockStatusInterface $stockStatus */
        $stockStatus = $this->stockRegistry->getStockStatusBySku($item->getData('sku'), $websiteId);
        $stockStatus->setWebsiteId($websiteId);

        return $stockStatus->getStockStatus();
    }

    /**
     * @param $item
     * @return null|string|string[]
     */
    public function getGroupedAlert($item)
    {
        $html = $this->getStockAlert($item);

        //remove form tag from content
        $html = preg_replace("/<\\/?" . 'form' . "(.|\\s)*?>/", '', $html);

        return $html;
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @param $websiteId
     *
     * @return int
     */
    public function getProductQty(\Magento\Catalog\Model\Product $product, $websiteId)
    {
        $stockStatus = $this->stockRegistry->getStockStatusBySku($product->getSku(), $websiteId);
        $quantity = $stockStatus->getQty();

        return $quantity;
    }

    /**
     * @return bool
     */
    public function isGDRPEnabled()
    {
        return (bool)$this->config->isGDRPEnabled();
    }

    /**
     * @return string
     */
    public function getGDRPText()
    {
        return $this->config->getGDRPText();
    }

    /**
     * @return string
     */
    public function getPlaceholder()
    {
        return (string)$this->config->getPlaceholder();
    }

    /**
     * @param string $url
     * @return string
     */
    public function getEncodedUrl($url = null)
    {
        if (!$url) {
            $url = $this->_urlBuilder->getCurrentUrl();
        }

        return $this->urlEncoder->encode($url);
    }

    /**
     * @return bool
     */
    public function isLoggedIn()
    {
        return $this->config->isLoggedIn();
    }
}

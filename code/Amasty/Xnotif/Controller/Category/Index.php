<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Xnotif
 */


namespace Amasty\Xnotif\Controller\Category;

use Magento\ConfigurableProduct\Block\Product\View\Type\Configurable;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable as ConfigurableModel;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\View\LayoutInterface;
use Magento\Framework\Url\Helper\Data as UrlHelper;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\Catalog\Api\Data\ProductInterface;

class Index extends \Magento\Framework\App\Action\Action
{
    /**
     * @var Configurable
     */
    private $configurableBlock;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var \Magento\CatalogInventory\Model\StockRegistry
     */
    private $stockRegistry;

    /**
     * @var \Amasty\Xnotif\Helper\Data
     */
    private $helper;

    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;

    /**
     * @var \Amasty\Xnotif\Plugins\Catalog\Block\Product\AbstractProduct
     */
    private $abstractProduct;

    /**
     * @var LayoutInterface
     */
    private $layout;

    /**
     * @var UrlHelper
     */
    private $urlHelper;

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    private $jsonEncoder;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var ProductCollectionFactory
     */
    private $productCollectionFactory;

    public function __construct(
        Context $context,
        Configurable $configurableBlock,
        ProductRepositoryInterface $productRepository,
        \Magento\CatalogInventory\Model\StockRegistry $stockRegistry,
        \Amasty\Xnotif\Helper\Data $helper,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        LayoutInterface $layout,
        \Amasty\Xnotif\Plugins\Catalog\Block\Product\AbstractProduct $abstractProduct,
        UrlHelper $urlHelper,
        StoreManagerInterface $storeManager,
        ProductCollectionFactory $productCollectionFactory
    ) {
        parent::__construct($context);
        $this->configurableBlock = $configurableBlock;
        $this->productRepository = $productRepository;
        $this->stockRegistry = $stockRegistry;
        $this->helper = $helper;
        $this->registry = $registry;
        $this->abstractProduct = $abstractProduct;
        $this->layout = $layout;
        $this->urlHelper = $urlHelper;
        $this->jsonEncoder = $jsonEncoder;
        $this->storeManager = $storeManager;
        $this->productCollectionFactory = $productCollectionFactory;
    }

    public function execute()
    {
        $aStockStatus = [];
        $productCollection = $this->initProducts();
        if ($productCollection && $productCollection->getSize()) {
            foreach ($productCollection as $product) {
                $tmpProduct = $this->registry->registry('current_product');
                $this->registry->unregister('current_product');
                $this->registry->register('current_product', $product);

                $this->configurableBlock->setProduct($product);
                $allowAttributes = $this->configurableBlock->getAllowAttributes($product);

                $this->configurableBlock->unsAllowProducts();
                foreach ($this->configurableBlock->getAllowProducts() as $simpleProduct) {
                    if ($this->helper->isItemSalable($simpleProduct)) {
                        continue;
                    }

                    $key = $this->getKeyByProduct($allowAttributes, $simpleProduct);
                    if ($key) {
                        $this->updateXnotifInfo($aStockStatus, $simpleProduct, $key, $product->getId());
                    }
                }

                $this->registry->unregister('current_product');
                $this->registry->register('current_product', $tmpProduct);
            }
        }

        return $this->getResponse()->representJson(
            $this->jsonEncoder->encode($aStockStatus)
        );
    }

    /**
     * @param array $allowAttributes
     * @param \Magento\Catalog\Model\Product $simpleProduct
     *
     * @return string
     */
    protected function getKeyByProduct($allowAttributes, \Magento\Catalog\Model\Product $simpleProduct)
    {
        $key = [];
        foreach ($allowAttributes as $attribute) {
            $key[] = $simpleProduct->getData(
                $attribute->getData('product_attribute')->getData(
                    'attribute_code'
                )
            );
        }

        return implode(',', $key);
    }

    /**
     * Initialize product instance from request data
     *
     * @return ProductCollection|false
     */
    private function initProducts()
    {
        $productIds = $this->getRequestProductIds();
        if ($productIds) {
            try {
                return $this->productCollectionFactory
                    ->create()
                    ->addIdFilter($productIds)
                    ->addAttributeToFilter(ProductInterface::TYPE_ID, ConfigurableModel::TYPE_CODE);
            } catch (NoSuchEntityException $e) {
                return false;
            }
        }

        return false;
    }

    /**
     * @return array
     */
    protected function getRequestProductIds()
    {
        return explode(',', $this->getRequest()->getParam('product'));
    }

    /**
     * @param array $aStockStatus
     * @param \Magento\Catalog\Model\Product $product
     * @param string $key
     * @param int $parentId
     */
    private function updateXnotifInfo(&$aStockStatus, $product, $key, $parentId)
    {
        $stockAlert = $this->abstractProduct->generateAlertHtml(
            $this->layout,
            $product,
            false
        );
        $stockAlert = $this->replaceUenc($stockAlert);
        $aStockStatus[$parentId][$key] = [
            'is_in_stock'   => false,
            'custom_status' => __('Out of Stock')->render(),
            'product_id'    => $product->getId(),
            'stockalert'    => $stockAlert
        ];
    }

    /**
     * Replace uenc for correct redirect after subscribe
     *
     * @param string $stockAlert
     * @return string
     */
    private function replaceUenc($stockAlert)
    {
        $currentUenc = $this->urlHelper->getEncodedUrl();
        $refererUrl = $this->getRequest()->getHeader('referer');
        $newUenc = $this->urlHelper->getEncodedUrl($refererUrl);
        $stockAlert = str_replace($currentUenc, $newUenc, $stockAlert);

        return $stockAlert;
    }
}

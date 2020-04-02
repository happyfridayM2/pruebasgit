<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_ShopbyBrand
 */


namespace Amasty\ShopbyBrand\Block\Catalog\Product\ProductList;

use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;

class MoreFrom extends \Magento\Catalog\Block\Product\AbstractProduct
{
    const DEFAULT_PRODUCT_LIMIT = 7;

    /**
     * @var CollectionFactory
     */
    private $productCollectionFactory;

    /**
     * @var \Amasty\ShopbyBrand\Helper\Data
     */
    private $helper;

    /**
     * @var \Magento\CatalogInventory\Helper\Stock
     */
    private $stockHelper;

    /**
     * Item collection
     *
     * @var \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    protected $_itemCollection;

    /**
     * @var \Magento\Catalog\Model\Product\Attribute\Source\Status
     */
    private $productStatus;

    /**
     * @var \Magento\Catalog\Model\Product\Visibility
     */
    private $productVisibility;

    /**
     * @var \Magento\Framework\Data\Helper\PostHelper
     */
    private $postHelper;

    public function __construct(
        Context $context,
        CollectionFactory $productCollectionFactory,
        \Amasty\ShopbyBrand\Helper\Data $helper,
        \Magento\CatalogInventory\Helper\Stock $stockHelper,
        \Magento\Catalog\Model\Product\Attribute\Source\Status $productStatus,
        \Magento\Catalog\Model\Product\Visibility $productVisibility,
        \Magento\Framework\Data\Helper\PostHelper $postHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->productCollectionFactory = $productCollectionFactory;
        $this->helper = $helper;
        $this->stockHelper = $stockHelper;
        $this->productStatus = $productStatus;
        $this->productVisibility = $productVisibility;
        $this->postHelper = $postHelper;
    }

    /**
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    public function getItemCollection()
    {
        if (!$this->_itemCollection && $this->isEnabled()) {
            $this->_prepareData();
        }

        return $this->_itemCollection;
    }

    /**
     * @return $this
     */
    protected function _prepareData()
    {
        /** @var \Magento\Catalog\Model\Product $product */
        $product = $this->_coreRegistry->registry('product');
        $attributeCode = $this->helper->getBrandAttributeCode();
        $attributeValue = $product->getData($attributeCode);

        if (!$attributeValue) {
            return $this;
        }
        $attributeValue = explode(',', $attributeValue);

        $this->initProductCollection(
            $attributeCode,
            $attributeValue,
            $product->getId()
        );

        return $this;
    }

    /**
     * @param string $attributeCode
     * @param array $attributeValue
     * @param int $currentProductId
     */
    private function initProductCollection($attributeCode, $attributeValue, $currentProductId)
    {
        $this->_itemCollection = $this->productCollectionFactory->create()
            ->addAttributeToSelect('*')
            ->addAttributeToFilter($attributeCode, ['in' => $attributeValue])
            ->addFieldToFilter('entity_id', ['neq' => $currentProductId])
            ->addAttributeToFilter('status', ['in' => $this->productStatus->getVisibleStatusIds()])
            ->setVisibility($this->productVisibility->getVisibleInCatalogIds())
            ->addStoreFilter()
            ->setPage(1, $this->getProductsLimit());

        $this->stockHelper->addInStockFilterToCollection($this->_itemCollection);
        $this->_itemCollection->getSelect()->orderRand();

        $this->_itemCollection->load();

        foreach ($this->_itemCollection as $product) {
            $product->setDoNotUseCategoryId(true);
        }
    }

    /**
     * @return int
     */
    private function getProductsLimit()
    {
        return $this->helper->getModuleConfig('more_from_brand/count') ? : self::DEFAULT_PRODUCT_LIMIT;
    }

    /**
     * @return string
     */
    public function _toHtml()
    {
        if ($this->getItemCollection() && $this->getItemCollection()->getSize()) {
            return parent::_toHtml();
        }

        return '';
    }

    /**
     * @return $this
     */
    protected function _beforeToHtml()
    {
        if ($this->isEnabled()) {
            $this->_prepareData();
        }

        return parent::_beforeToHtml();
    }

    /**
     * @return bool
     */
    protected function isEnabled()
    {
        return $this->helper->getModuleConfig('more_from_brand/enable');
    }

    /**
     * @return \Magento\Framework\Phrase|mixed
     */
    public function getTitle()
    {
        $title = $this->helper->getModuleConfig('more_from_brand/title');
        preg_match_all('@\{(.+?)\}@', $title, $matches);
        if (isset($matches[1]) && !empty($matches[1])) {
            foreach ($matches[1] as $match) {
                $value = '';
                switch ($match) {
                    case 'brand_name':
                        $value = $this->getBrandName();
                        break;
                }
                $title = str_replace('{' . $match . '}', $value, $title);
            }
        }

        $title = $title ?: __('More from this Brand');

        return $title;
    }

    /**
     * @return string
     */
    private function getBrandName()
    {
        $value = '';
        /** @var \Magento\Catalog\Model\Product $product */
        $product = $this->_coreRegistry->registry('product');
        $attributeCode = $this->helper->getBrandAttributeCode();
        $attributeValue = $product->getData($attributeCode);
        $attribute = $product->getResource()->getAttribute($attributeCode);
        if ($attribute && $attribute->usesSource()) {
            $value = $attribute->getSource()->getOptionText($attributeValue);
        }

        if (is_array($value)) {
            $value = implode(', ', $value);
        }

        return $value;
    }

    /**
     * @return \Magento\Framework\Data\Helper\PostHelper
     */
    public function getPostHelper()
    {
        return $this->postHelper;
    }

    /**
     * @return \Magento\Catalog\Helper\Product\Compare
     */
    public function getCompareHelper()
    {
        return $this->_compareProduct;
    }

    /**
     * @return \Magento\Wishlist\Helper\Data
     */
    public function getWishlistHelper()
    {
        return $this->_wishlistHelper;
    }
}

<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Cart
 */


/**
 * Copyright © 2016 Amasty. All rights reserved.
 */
namespace Amasty\Cart\Block\Product;

use Amasty\Cart\Model\Source\Section;

class Related extends \Magento\Catalog\Block\Product\ProductList\Related
{
    /**
     * @var \Amasty\Cart\Helper\Data
     */
    private $helper;

    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Checkout\Model\ResourceModel\Cart $checkoutCart,
        \Magento\Catalog\Model\Product\Visibility $productVisibility,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Module\Manager $moduleManager,
        \Amasty\Cart\Helper\Data $helper,
        array $data = []
    ) {
        parent::__construct($context, $checkoutCart, $productVisibility, $checkoutSession, $moduleManager, $data);
        $this->helper = $helper;
        $this->_scopeConfig = $context->getScopeConfig();
        $this->setBlockType('related');
    }

    public function getHelper()
    {
        return $this->helper;
    }

    /**
     * Prepare related items data
     *
     * @return \Magento\Catalog\Block\Product\ProductList\Related
     */
    protected function _prepareData()
    {
        $product = $this->_coreRegistry->registry('product');
        /* @var $product \Magento\Catalog\Model\Product */

        $this->_itemCollection = $product->getRelatedProductCollection()->addAttributeToSelect(
            $this->_catalogConfig->getProductAttributes()
        )->setPositionOrder()->addStoreFilter();

        /*add limit to collection*/
        $limit = $this->helper->getProductsQtyLimit();
        $this->_itemCollection->getSelect()->limit($limit);
        $this->_itemCollection->load();

        foreach ($this->_itemCollection as $product) {
            $product->setDoNotUseCategoryId(true);
        }

        return $this;
    }

    /**
     * @return \Magento\Framework\Phrase
     */
    public function getAddMessage()
    {
        switch ($this->getCartType()) {
            case Section::QUOTE:
                $result = __('Add to Quote');
                break;
            case Section::CART:
            default:
                $result = __('Add to Cart');
        }

        return $result;
    }
}

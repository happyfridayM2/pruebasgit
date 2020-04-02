<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Cart
 */


namespace Amasty\Cart\Block\Product;

use Amasty\Cart\Model\Source\Section;

class Crosssell extends \Magento\Catalog\Block\Product\ProductList\Crosssell
{
    /**
     * @var \Amasty\Cart\Helper\Data
     */
    private $helper;

    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Amasty\Cart\Helper\Data $helper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->helper = $helper;
        $this->setBlockType('crosssell');
    }

    public function getHelper()
    {
        return $this->helper;
    }

    /**
     * Prepare crosssell items data
     *
     * @return \Magento\Catalog\Block\Product\ProductList\Crosssell
     */
    protected function _prepareData()
    {
        $product = $this->_coreRegistry->registry('product');
        /* @var $product \Magento\Catalog\Model\Product */

        $this->_itemCollection = $product->getCrossSellProductCollection()->addAttributeToSelect(
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
     * Count items
     *
     * @return int
     * @codeCoverageIgnore
     */
    public function getItemCount()
    {
        return count($this->getItems());
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

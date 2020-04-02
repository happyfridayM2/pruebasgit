<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Cart
 */


namespace Amasty\Cart\Block\Product;

use Magento\Catalog\Block\Product\ReviewRendererInterface as ReviewRendererInterface;
use Magento\Catalog\Model\Product;

class Minipage extends \Magento\Catalog\Block\Product\View
{
    public function _construct()
    {
        $this->setTemplate('Amasty_Cart::product/minipage.phtml');
        parent::_construct();
    }

    /**
     * @return Product
     */
    public function getProduct()
    {
        return $this->getData('product');
    }

    /**
     * @return \Magento\Framework\View\Result\PageFactory
     */
    public function getPageFactory()
    {
        return $this->getData('pageFactory');
    }

    /**
     * @return \Magento\Catalog\Block\Product\ImageBuilder
     */
    public function getImageBuilder()
    {
        return $this->getData('imageBuilder');
    }

    /**
     * @return string
     */
    public function renderPriceHtml()
    {
        $html = '';
        $block = $this->_layout->getBlock('product.price.final');
        if (!$block) {
            $page = $this->getPageFactory()->create(false, ['isIsolated' => true]);
            $page->addHandle('catalog_product_view');

            $type = $this->getProduct()->getTypeId();
            $page->addHandle('catalog_product_view_type_' . $type);
            $block = $page->getLayout()->getBlock('product.price.final');
        }

        if ($block) {
            $html = $block->toHtml();
        }

        return $html;
    }

    /**
     * @return string
     */
    public function getOptions()
    {
        return $this->getData('optionsHtml');
    }

    /**
     * @return string
     */
    public function getRatingSummary($product)
    {
        $block = $this->getLayout()->createBlock(
            \Magento\Review\Block\Product\ReviewRenderer::class,
            'amasty.productreview',
            [ 'data' => [
                'product' => $product
            ] ]
        );

        return $block->getReviewsSummaryHtml($product, ReviewRendererInterface::SHORT_VIEW);
    }

    /**
     * @param $product
     * @param $imageId
     * @param array $attributes
     *
     * @return string
     */
    public function getImageBlock($product, $imageId, $attributes = [])
    {
        $block = $this->getImageBuilder()->setProduct($product)
            ->setImageId($imageId)
            ->setAttributes($attributes)
            ->create();

        $html = $block->toHtml();

        return $html;
    }
}

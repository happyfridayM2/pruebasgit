<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Scroll
 */


namespace Amasty\Scroll\Observer;

use Magento\Framework\View\Page\Config as PageConfig;
use Magento\Framework\UrlInterface;
use Magento\Framework\Escaper;

class LayoutGenerateBlocksAfterObserver implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var PageConfig
     */
    private $pageConfig;

    /**
     * @var Escaper
     */
    private $escaper;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    public function __construct(
        PageConfig $pageConfig,
        Escaper $escaper,
        UrlInterface $urlBuilder
    ) {
        $this->pageConfig = $pageConfig;
        $this->escaper = $escaper;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     *
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $productListBlock = $this->getCategoryProductListBlock($observer);
        if ($productListBlock) {
            $toolbarBlock = $productListBlock->getToolbarBlock();
            if ($toolbarBlock) {
                /** @var \Magento\Theme\Block\Html\Pager $pagerBlock */
                $pagerBlock = $toolbarBlock->getChildBlock('product_list_toolbar_pager');
                if ($pagerBlock) {
                    $layer = $productListBlock->getLayer();
                    $productListBlock->prepareSortableFieldsByCategory($layer->getCurrentCategory());
                    $toolbarBlock->setAvailableOrders($productListBlock->getAvailableOrders());
                    $toolbarBlock->setDefaultOrder($productListBlock->getSortBy());
                    $toolbarBlock->setDefaultDirection($productListBlock->getDefaultDirection());
                    if ($toolbarBlock->getCurrentOrder() == 'relevance') {
                        $layer->getProductCollection()->setOrder(
                            $toolbarBlock->getCurrentOrder(),
                            $toolbarBlock->getCurrentDirection()
                        );
                    }
                    $pagerBlock
                        ->setAvailableLimit($toolbarBlock->getAvailableLimit())
                        ->setCollection($layer->getProductCollection());
                    $lastPage = $pagerBlock->getLastPageNum();
                    $currentPage = $pagerBlock->getCurrentPage();

                    if ($currentPage > 1) {
                        $url = $this->getPageUrl($pagerBlock->getPageVarName(), $currentPage - 1);
                        $this->pageConfig->addRemotePageAsset($url, 'link_rel', ['attributes' => ['rel' => 'prev']]);
                    }

                    if ($currentPage < $lastPage) {
                        $url = $this->getPageUrl($pagerBlock->getPageVarName(), $currentPage + 1);
                        $this->pageConfig->addRemotePageAsset($url, 'link_rel', ['attributes' => ['rel' => 'next']]);
                    }
                }
            }
        }

        return $this;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     *
     * @return \Magento\Catalog\Block\Product\ListProduct
     */
    private function getCategoryProductListBlock(\Magento\Framework\Event\Observer $observer)
    {
        $productListBlock = $observer->getLayout()->getBlock('category.products.list');
        if (!$productListBlock) {
            foreach ($observer->getLayout()->getAllBlocks() as $block) {
                if ($block instanceof \Magento\Catalog\Block\Product\ListProduct) {
                    $productListBlock = $block;
                    break;
                }
            }
        }

        return $productListBlock;
    }

    /**
     * @param string $key
     * @param int value
     *
     * @return  string
     */
    private function getPageUrl($key, $value)
    {
        $currentUrl = $this->urlBuilder->getCurrentUrl();
        $currentUrl = $this->escaper->escapeUrl($currentUrl);
        $result = preg_replace('/(\W)' . $key . '=\d+/', "$1$key=$value", $currentUrl, -1, $count);
        if ($value == 1) {
            $result = str_replace($key . '=1&amp;', '', $result); //not last & not single param
            $result = str_replace('&amp;' . $key . '=1', '', $result); //last param
            $result = str_replace('?' . $key . '=1', '', $result); //single param
        } elseif (!$count) {
            $delimiter = (strpos($currentUrl, '?') === false) ? '?' : '&amp;';
            $result .= $delimiter . $key . '=' . $value;
        }

        return $result;
    }
}

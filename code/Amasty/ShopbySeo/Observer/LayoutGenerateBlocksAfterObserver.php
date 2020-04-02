<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_ShopbySeo
 */


namespace Amasty\ShopbySeo\Observer;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\View\Page\Config;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\Escaper;

/**
 * Class LayoutGenerateBlocksAfterObserver
 * @package Amasty\ShopbySeo\Observer
 */
class LayoutGenerateBlocksAfterObserver implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var Config
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
        ScopeConfigInterface $scopeConfig,
        Config $pageConfig,
        Escaper $escaper,
        UrlInterface $urlBuilder
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->pageConfig = $pageConfig;
        $this->escaper = $escaper;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var \Magento\Theme\Block\Html\Pager $pagerBlock */
        $pagerBlock = $this->getPagerBlock($observer);
        if ($pagerBlock) {
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

        return $this;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return \Magento\Theme\Block\Html\Pager|null
     */
    private function getPagerBlock(\Magento\Framework\Event\Observer $observer)
    {
        $pagerBlock = null;
        $productListBlock = $this->getCategoryProductListBlock($observer);
        if ($productListBlock && $this->isShowPrevNextLinks()) {
            $toolbarBlock = $productListBlock->getToolbarBlock();
            if ($toolbarBlock) {
                /** @var \Magento\Theme\Block\Html\Pager $pagerBlock */
                $pagerBlock = $toolbarBlock->getChildBlock('product_list_toolbar_pager');
                if ($pagerBlock) {
                    $pagerBlock
                        ->setLimit($toolbarBlock->getLimit())
                        ->setAvailableLimit($toolbarBlock->getAvailableLimit())
                        ->setCollection($productListBlock->getLayer()->getProductCollection());
                }
            }
        }

        return $pagerBlock;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
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
     * @return bool
     */
    private function isShowPrevNextLinks()
    {
        return (bool)$this->scopeConfig
            ->getValue('amasty_shopby_seo/other/prev_next', ScopeInterface::SCOPE_STORE);
    }

    /**
     * @param string $key
     * @param int value
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

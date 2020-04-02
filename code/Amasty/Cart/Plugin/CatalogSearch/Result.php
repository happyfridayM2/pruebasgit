<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Cart
 */
namespace Amasty\Cart\Plugin\CatalogSearch;

use Amasty\Cart\Block\Config;

class Result
{
    /**
     * @var \Amasty\Cart\Helper\Data
     */
    private $helper;

    /**
     * @var \Magento\Framework\View\LayoutFactory
     */
    private $layoutFactory;

    public function __construct(
        \Amasty\Cart\Helper\Data $helper,
        \Magento\Framework\View\LayoutFactory $layoutFactory
    ) {
        $this->helper = $helper;
        $this->layoutFactory = $layoutFactory;
    }

    /**
     * @param \Magento\CatalogSearch\Block\Result $subject
     * @param $result
     *
     * @return string
     */
    public function afterGetProductListHtml(
        \Magento\CatalogSearch\Block\Result $subject,
        $result
    ) {
        $enable = $this->helper->getModuleConfig('general/enable');

        if ($enable) {
            $layout = $this->layoutFactory->create();
            $block = $layout->createBlock(
                Config::class,
                'amasty.cart.config',
                [ 'data' => [] ]
            );

            $html = $block->setPageType(Config::CATEGORY_PAGE)->toHtml();
            $result .= $html;
        }

        return  $result;
    }
}

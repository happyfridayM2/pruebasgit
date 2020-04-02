<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Cart
 */
namespace Amasty\Cart\Plugin\Product\Category;

use Amasty\Cart\Block\Config;

class Addtocart
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
     * @param \Magento\Catalog\Block\Category\View $subject
     * @param $result
     *
     * @return string
     */
    public function afterGetProductListHtml(
        \Magento\Catalog\Block\Category\View $subject,
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

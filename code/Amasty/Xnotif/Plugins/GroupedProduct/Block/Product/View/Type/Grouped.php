<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Xnotif
 */


namespace Amasty\Xnotif\Plugins\GroupedProduct\Block\Product\View\Type;

use Magento\Catalog\Model\Product;
use Magento\GroupedProduct\Block\Product\View\Type\Grouped as GroupedNative;
use Magento\Framework\View\Layout;
use Magento\Framework\View\Element\Template;

/**
 * Class Grouped
 */
class Grouped
{
    const TEMPLATE_FILE = 'Amasty_Xnotif::product/view/type/grouped_js.phtml';

    /**
     * @var \Amasty\Xnotif\Helper\Data
     */
    private $helper;

    /**
     * @var bool
     */
    private $enabled = false;

    /**
     * @var Layout
     */
    private $layout;

    public function __construct(
        \Amasty\Xnotif\Helper\Data $helper,
        Layout $layout
    ) {
        $this->helper = $helper;
        $this->layout = $layout;
    }

    /**
     * @param GroupedNative $subject
     * @param \Closure $proceed
     * @param Product $product
     *
     * @return string
     */
    public function aroundGetProductPrice(GroupedNative $subject, \Closure $proceed, Product $product)
    {
        $html = $proceed($product);
        if (!$this->helper->isItemSalable($product)) {
            $html .= $this->helper->getGroupedAlert($product);
            $this->enabled = true;
        }

        return $html;
    }

    /**
     * @param GroupedNative $subject
     * @param string $html
     *
     * @return string
     */
    public function afterToHtml(GroupedNative $subject, $html)
    {
        if ($this->enabled) {
            $html .= $this->layout->createBlock(Template::class)
                ->setTemplate(self::TEMPLATE_FILE)
                ->toHtml();
        }

        return $html;
    }
}

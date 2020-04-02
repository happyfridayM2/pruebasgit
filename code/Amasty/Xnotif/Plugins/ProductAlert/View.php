<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Xnotif
 */


namespace Amasty\Xnotif\Plugins\ProductAlert;

use Magento\Catalog\Api\Data\ProductInterface;

class View
{
    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;

    /**
     * @var \Amasty\Xnotif\Helper\Data
     */
    private $helper;

    /**
     * @var \Amasty\Xnotif\Helper\Config
     */
    private $config;

    public function __construct(
        \Magento\Framework\Registry $registry,
        \Amasty\Xnotif\Helper\Data $helper,
        \Amasty\Xnotif\Helper\Config $config
    ) {
        $this->registry = $registry;
        $this->helper = $helper;
        $this->config = $config;
    }

    /**
     * @param \Magento\Productalert\Block\Product\View $subject
     * @param $html
     * @return string
     */
    public function afterToHtml(\Magento\ProductAlert\Block\Product\View $subject, $html)
    {
        $type = $subject->getNameInLayout();
        if ($html == '') {
            return $html;
        }

        if ($type == 'productalert.stock' && !$subject->getData('amxnotif_observer_triggered')) {
            if (!$this->config->allowForCurrentCustomerGroup('stock')) {
                return '';
            }

            $product = $this->getProduct();
            if (!$this->helper->isItemSalable($product)) {
                $html = $this->helper->observeStockAlertBlock($product, $subject);

                return $html;
            }
        }

        if ($type == 'productalert.price' && !$subject->getData('amxnotif_observer_triggered')) {
            if (!$this->config->allowForCurrentCustomerGroup('price')) {
                return '';
            }

            $product = $this->getProduct();
            $html = $this->helper->observePriceAlertBlock($product, $subject);
        }

        return $html;
    }

    /**
     * @return ProductInterface
     */
    protected function getProduct()
    {
        return $this->registry->registry('current_product');
    }
}

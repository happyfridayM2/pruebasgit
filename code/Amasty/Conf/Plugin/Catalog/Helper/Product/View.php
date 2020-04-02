<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Conf
 */


namespace Amasty\Conf\Plugin\Catalog\Helper\Product;

use Magento\Catalog\Helper\Product\View as MagentoView;
use Magento\Framework\View\Result\Page as ResultPage;

class View
{
    /**
     * @var \Amasty\Conf\Helper\Data
     */
    private $helper;

    public function __construct(
        \Amasty\Conf\Helper\Data $helper
    ) {
        $this->helper = $helper;
    }

    public function beforeInitProductLayout(
        MagentoView $subject,
        ResultPage $resultPage,
        $product,
        $params = null
    ) {
        if ($this->helper->getModuleConfig('general/enable_zoom_lightbox')) {
            $resultPage->addHandle('amconf_catalog_product_view');
        }

        return [$resultPage, $product, $params];
    }
}

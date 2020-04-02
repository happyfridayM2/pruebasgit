<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Xnotif
 */


namespace Amasty\Xnotif\Block\Catalog\Category;

use \Magento\ProductAlert\Block\Product\View as ProductAlert;

/**
 * Class StockSubscribe
 */
class StockSubscribe extends ProductAlert
{
    /**
     * @var \Amasty\Xnotif\Helper\Data
     */
    private $moduleHelper;

    /**
     * @var \Magento\Framework\Url\Helper\Data
     */
    private $urlHelper;

    /**
     * @var \Amasty\Xnotif\Helper\Config
     */
    private $config;

    public function __construct(
        \Amasty\Xnotif\Helper\Data $moduleHelper,
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\ProductAlert\Helper\Data $helper,
        \Amasty\Xnotif\Helper\Config $config,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\Helper\PostHelper $coreHelper,
        \Magento\Framework\Url\Helper\Data $urlHelper,
        array $data = []
    ) {
        parent::__construct($context, $helper, $registry, $coreHelper, $data);
        $this->urlHelper = $urlHelper;
        $this->moduleHelper = $moduleHelper;
        $this->config = $config;
    }

    /**
     * Check if popup on
     *
     * @return int
     */
    public function usePopupForSubscribe()
    {
        return $this->config->isPopupForSubscribeEnabled();
    }

    /**
     * @return \Amasty\Xnotif\Helper\Data
     */
    public function getModuleHelper()
    {
        return $this->moduleHelper;
    }

    /**
     * @return string
     */
    public function getEncodedUrl()
    {
        return $this->urlHelper->getEncodedUrl();
    }
}

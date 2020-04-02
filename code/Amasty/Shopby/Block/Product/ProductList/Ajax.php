<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */


namespace Amasty\Shopby\Block\Product\ProductList;

use Magento\Framework\View\Element\Template;
use Amasty\Shopby\Model\Layer\FilterList;
use \Magento\Framework\DataObject\IdentityInterface;

/**
 * @api
 */
class Ajax extends \Magento\Framework\View\Element\Template implements IdentityInterface
{
    const CACHE_TAG = 'client_';

    /**
     * @var \Amasty\Shopby\Helper\Data
     */
    private $helper;

    /**
     * @var \Magento\Catalog\Model\Layer
     */
    private $layer;

    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;

    /**
     * @var \Magento\Framework\Module\Manager
     */
    private $moduleManager;

    /**
     * @var \Amasty\ShopbyBase\Helper\Data
     */
    private $baseHelper;

    public function __construct(
        Template\Context $context,
        \Magento\Catalog\Model\Layer\Resolver $layerResolver,
        \Amasty\Shopby\Helper\Data $helper,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Module\Manager $moduleManager,
        \Amasty\ShopbyBase\Helper\Data $baseHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->layer = $layerResolver->get();
        $this->helper = $helper;
        $this->registry = $registry;
        $this->moduleManager = $moduleManager;
        $this->baseHelper = $baseHelper;
    }

    /**
     * @return bool
     */
    public function isGoogleTagManager()
    {
        return $this->moduleManager->isEnabled('Magento_GoogleTagManager');
    }

    /**
     * @return bool
     */
    public function canShowBlock()
    {
        return $this->helper->isAjaxEnabled();
    }

    /**
     * @return string
     */
    public function submitByClick()
    {
        return $this->helper->collectFilters();
    }

    /**
     * @return string[]
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->baseHelper->isMobile() ? 'mobile' : 'desktop'];
    }

    /**
     * @return bool
     */
    public function scrollUp()
    {
        return $this->_scopeConfig->isSetFlag('amshopby/general/ajax_scroll_up');
    }

    /**
     * Retrieve active filters
     *
     * @return array
     */
    protected function getActiveFilters()
    {
        $filters = $this->layer->getState()->getFilters();
        if (!is_array($filters)) {
            $filters = [];
        }
        return $filters;
    }

    /**
     * Retrieve Clear Filters URL
     *
     * @return string
     */
    public function getClearUrl()
    {
        return $this->helper->getAjaxCleanUrl($this->getActiveFilters());
    }

    /**
     * @return int
     */
    public function getCurrentCategoryId()
    {
        return $this->helper->getCurrentCategory()->getId();
    }

    /**
     * @return int
     */
    public function isCategorySingleSelect()
    {
        $allFilters = $this->registry->registry(FilterList::ALL_FILTERS_KEY, []);
        foreach ($allFilters as $filter) {
            if ($filter instanceof \Amasty\Shopby\Model\Layer\Filter\Category) {
                return (int)!$filter->isMultiselect();
            }
        }

        return 0;
    }

    /**
     * Get config
     *
     * @param string $path
     * @return string
     */
    public function getConfig($path)
    {
        return $this->_scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return string
     */
    public function getGtmAccountId()
    {
        return $this->getConfig(\Magento\GoogleTagManager\Helper\Data::XML_PATH_CONTAINER_ID);
    }
}

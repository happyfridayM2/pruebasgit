<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */


namespace Amasty\Shopby\Block\Navigation;

use Magento\Framework\View\Element\Template;

/**
 * @api
 */
class ApplyButton extends \Magento\Framework\View\Element\Template
{

    /**
     * Path to template file in theme.
     *
     * @var string
     */
    protected $_template = 'navigation/apply_button.phtml';

    /**
     * @var \Amasty\Shopby\Helper\Data
     */
    private $helper;

    /**
     * @var string
     */
    private $navigationSelector;

    /**
     * @var string
     */
    private $position;

    /**
     * @var \Magento\Catalog\Model\Layer
     */
    private $layer;

    public function __construct(
        Template\Context $context,
        \Amasty\Shopby\Helper\Data $helper,
        \Magento\Catalog\Model\Layer\Resolver $layerResolver,
        array $data = []
    ) {
        $this->layer = $layerResolver->get();
        $this->helper = $helper;
        parent::__construct($context, $data);
    }

    /**
     * @return bool
     */
    public function isAjaxEnabled()
    {
        return $this->helper->isAjaxEnabled();
    }

    /**
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function blockEnabled()
    {
        $existBlock  = $this->getLayout()->getBlock('catalog.leftnav')
            || $this->getLayout()->getBlock('catalogsearch.leftnav');
        $visible = $this->helper->collectFilters() && $existBlock;

        return $visible;
    }

    /**
     * @param string $selector
     */
    public function setNavigationSelector($selector)
    {
        $this->navigationSelector = $selector;
    }

    /**
     * @return string
     */
    public function getNavigationSelector()
    {
        return $this->navigationSelector;
    }

    /**
     * @param $position
     */
    public function setButtonPosition($position)
    {
        $this->position = $position;
    }

    /**
     * @return string
     */
    public function getButtonPosition()
    {
        return $this->position;
    }

    /**
     * Retrieve active filters
     *
     * @return array
     */
    public function getActiveFilters()
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
}

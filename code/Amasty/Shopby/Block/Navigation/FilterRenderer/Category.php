<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */


namespace Amasty\Shopby\Block\Navigation\FilterRenderer;

use Amasty\ShopbyBase\Api\Data\FilterSettingInterface;
use Amasty\Shopby\Helper\FilterSetting;
use Amasty\Shopby\Helper\Data as ShopbyHelper;
use Amasty\Shopby\Model\Source\DisplayMode;
use Amasty\Shopby\Model\Source\SubcategoriesExpand;
use Amasty\Shopby\Model\Source\SubcategoriesView;
use Magento\Catalog\Model\Layer\Resolver;
use Magento\Framework\Exception\LocalizedException;

/**
 * @api
 */
class Category extends \Magento\Framework\View\Element\Template
{
    const DEFAULT_LEVEL = 1;

    const TEMPLATE_STORAGE_PATH = 'layer/filter/category/items/renderer/';

    /**
     * @var  FilterSetting
     */
    protected $settingHelper;

    /**
     * @var ShopbyHelper
     */
    protected $helper;

    /**
     * @var \Amasty\Shopby\Helper\Category
     */
    protected $categoryHelper;

    /**
     * @var \Magento\Catalog\Model\Layer
     */
    protected $layer;

    /**
     * @var array
     */
    private $countByPath = [];

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        FilterSetting $settingHelper,
        ShopbyHelper $helper,
        Resolver $resolver,
        \Amasty\Shopby\Helper\Category $categoryHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->settingHelper = $settingHelper;
        $this->helper = $helper;
        $this->layer = $resolver->get();
        $this->categoryHelper = $categoryHelper;
    }

    /**
     * @param string $path = null
     * @return string
     */
    public function render($path = null)
    {
        $this->setPath($path);
        $this->setTemplate($this->getFilterTemplate());

        return $this->toHtml();
    }

    /**
     * @param FilterSettingInterface $filterSetting
     * @return string
     */
    private function getFilterTemplate()
    {
        $filterSetting = $this->getFilterSetting();
        switch (true) {
            case $filterSetting->getDisplayMode() == DisplayMode::MODE_DROPDOWN:
                $template = 'dropdown.phtml';
                break;
            default:
                $template = 'labels.phtml';
                break;
        }

        return self::TEMPLATE_STORAGE_PATH .$template;
    }

    /**
     * Render all children for current category path
     *
     * @param string $path
     * @return string
     */
    public function renderChildrenItems($path)
    {
        return $this->getLayout()
            ->createBlock(self::class)
            ->setFilter($this->getFilter())
            ->setLevel($this->getLevel() + self::DEFAULT_LEVEL)
            ->render($path);
    }

    /**
     * @param \Amasty\Shopby\Model\Layer\Filter\Item $filterItem
     * @return int
     */
    public function checkedFilter(\Amasty\Shopby\Model\Layer\Filter\Item $filterItem)
    {
        return $this->helper->isFilterItemSelected($filterItem)
            || $filterItem->getValue() == $this->layer->getCurrentCategory()->getId();
    }

    /**
     * Retrieve active filters
     *
     * @return string
     */
    public function collectFilters()
    {
        return $this->helper->collectFilters();
    }

    /**
     * @return \Amasty\Shopby\Model\Layer\Filter\Category
     * @throws LocalizedException
     */
    public function getFilter()
    {
        if (!$this->getData('filter') instanceof \Amasty\Shopby\Model\Layer\Filter\Category) {
            throw new LocalizedException(__('Wrong Filter Type'));
        }

        return $this->getData('filter');
    }

    /**
     * @return int
     */
    public function getLevel()
    {
        return $this->getData('level') ?: self::DEFAULT_LEVEL;
    }

    /**
     * @param string $data
     * @return string
     */
    public function escapeId($data)
    {
        return str_replace(",", "_", $data);
    }

    /**
     * @return string
     */
    public function getInputType()
    {
        return $this->getFilterSetting()->isMultiselect() ? 'checkbox' : 'radio';
    }

    /**
     * Retrieve setting for category layer filter
     *
     * @return \Amasty\ShopbyBase\Api\Data\FilterSettingInterface
     */
    public function getFilterSetting()
    {
        if (!$this->getData('filter_setting')) {
            $setting = $this->settingHelper->getSettingByLayerFilter($this->getFilter());
            $this->setData('filter_setting', $setting);
        }

        return $this->getData('filter_setting');
    }

    /**
     * @param null $currentPath
     * @return bool
     */
    public function isExpandByClick($currentPath = null)
    {
        return $this->getChildren($currentPath)
            && $this->getFilterSetting()->getSubcategoriesExpand() == SubcategoriesExpand::BY_CLICK
            && $this->getFilterSetting()->getSubcategoriesView() == SubcategoriesView::FOLDING;
    }

    /**
     * @param $currentPath
     * @return int
     */
    public function getChildren($currentPath)
    {
        return $this->getFilter()->getItems()->getItemsCount($currentPath);
    }

    /**
     * @param $filterItems
     * @param $path
     * @return bool
     */
    public function isParent($filterItems, $path)
    {
        foreach ($filterItems->getItems($path) as $filterItem) {
            if ($filterItem->getCount() > 0) {
                $this->countByPath[$path ?: 0] = true;
                break;
            }
        }

        return isset($this->countByPath[$path ?: 0]);
    }

    /**
     * @return \Amasty\Shopby\Helper\Category
     */
    public function getCategoryHelper()
    {
        return $this->categoryHelper;
    }

    /**
     * @return bool
     */
    public function isFolding()
    {
        return !$this->getFilterSetting()->isApplyFlyOut();
    }
}

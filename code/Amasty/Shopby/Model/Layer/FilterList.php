<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */


namespace Amasty\Shopby\Model\Layer;

use Amasty\Shopby\Model\Layer\Filter\Category;
use Amasty\Shopby\Model\Source\FilterPlacedBlock;
use Amasty\ShopbyBase\Api\Data\FilterSettingInterface;
use Magento\Catalog\Model\Layer;
use Magento\Catalog\Model\Layer\Filter\FilterInterface;
use Amasty\Shopby\Model\Source\VisibleInCategory;
use Amasty\ShopbyBase\Model\ResourceModel\FilterSetting\CollectionExtendedFactory;

class FilterList extends Layer\FilterList
{
    const PLACE_SIDEBAR = 'sidebar';
    const PLACE_TOP     = 'top';
    const ALL_FILTERS_KEY  = 'amasty_shopby_all_filters';
    const ONE_COLUMN_LAYOUT = '1column';

    /**
     * @var \Amasty\Shopby\Helper\FilterSetting
     */
    private $filterSetting;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    private $request;

    /**
     * @var string
     */
    private $currentPlace;

    /**
     * @var bool
     */
    private $filtersLoaded  = false;

    /**
     * @var bool
     */
    private $filtersMatched = false;

    /**
     * @var bool
     */
    private $filtersApplied = false;

    /**
     * @var CollectionExtendedFactory
     */
    private $collectionExtendedFactory;

    /**
     * @var  \Magento\Framework\Registry
     */
    private $registry;

    /**
     * @var \Amasty\Shopby\Model\Request
     */
    private $shopbyRequest;

    /**
     * @var \Amasty\Shopby\Helper\Config
     */
    private $config;

    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        Layer\FilterableAttributeListInterface $filterableAttributes,
        \Amasty\Shopby\Helper\FilterSetting $filterSettingHelper,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\Registry $registry,
        CollectionExtendedFactory $collectionExtendedFactory,
        \Amasty\Shopby\Model\Request $shopbyRequest,
        \Amasty\Shopby\Helper\Config $config,
        array $filters = [],
        $place = self::PLACE_SIDEBAR
    ) {
        $this->currentPlace = $place;
        $this->filterSetting = $filterSettingHelper;
        $this->request = $request;
        $this->registry = $registry;
        $this->collectionExtendedFactory = $collectionExtendedFactory;
        $this->shopbyRequest = $shopbyRequest;
        $this->config = $config;
        parent::__construct($objectManager, $filterableAttributes, $filters);
    }

    /**
     * @param Layer $layer
     *
     * @return array|Layer\Filter\AbstractFilter[]
     */
    public function getFilters(Layer $layer)
    {
        if (!$this->filtersLoaded) {
            $filters = $this->getAllFilters($layer);
            $this->filters = $this->filterByPlace($filters, $layer);
            $this->filtersLoaded = true;
        }
        $this->matchFilters($this->filters, $layer);
        return $this->filters;
    }

    /**
     * Get both top and left filters. And keep it in registry.
     *
     * @param Layer $layer
     * @return \Magento\Catalog\Model\Layer\Filter\AbstractFilter[]
     */
    public function getAllFilters(Layer $layer)
    {
        $allFilters = $this->registry->registry(self::ALL_FILTERS_KEY);
        if ($allFilters === null) {
            $allFilters = $this->generateAllFilters($layer);
            $this->registry->register(self::ALL_FILTERS_KEY, $allFilters);
        }

        $allFilters = $this->removeCategoryFilter($allFilters);

        return $allFilters;
    }

    /**
     * @param Layer $layer
     *
     * @return array
     */
    protected function generateAllFilters(Layer $layer)
    {
        $filters = parent::getFilters($layer);
        $listAdditionalFilters = $this->getAdditionalFilters($layer);
        $allFilters = $this->insertAdditionalFilters($filters, $listAdditionalFilters);
        usort($allFilters, [$this, 'sortingByPosition']);

        return $allFilters;
    }

    /**
     * @param array $allFilters
     *
     * @return array
     */
    protected function removeCategoryFilter($allFilters)
    {
        if (!$this->config->isCategoryFilterEnabled()) {
            foreach ($allFilters as $id => $filter) {
                if ($filter instanceof Category) {
                    unset($allFilters[$id]);
                }
            }
        }

        return $allFilters;
    }

    /**
     * @param array $filters
     * @param Layer $layer
     * @return array
     */
    protected function filterByPlace(array $filters, Layer $layer)
    {
        $filters = array_filter($filters, function ($filter) use ($layer) {
            if ($this->isOneColumnLayout($layer)) {
                //Move all filters to open place in one column design
                return $this->currentPlace == self::PLACE_SIDEBAR;
            }

            $position = $this->getFilterBlockPosition($filter);
            return $position == FilterPlacedBlock::POSITION_BOTH
                || ($position == FilterPlacedBlock::POSITION_SIDEBAR && $this->currentPlace == self::PLACE_SIDEBAR)
                || ($position == FilterPlacedBlock::POSITION_TOP && $this->currentPlace == self::PLACE_TOP);
        });

        return $filters;
    }

    /**
     * @param FilterInterface $filter
     *
     * @return int
     */
    protected function getFilterBlockPosition(FilterInterface $filter)
    {
        return $this->filterSetting->getSettingByLayerFilter($filter)->getBlockPosition();
    }

    /**
     * @param Layer $layer
     * @return bool
     */
    protected function isOneColumnLayout(Layer $layer)
    {
        return $layer->getCurrentCategory()->getData('page_layout') == self::ONE_COLUMN_LAYOUT;
    }

    /**
     * @param array $listFilters
     * @param Layer $layer
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function matchFilters(array $listFilters, Layer $layer)
    {
        if ($this->filtersMatched || $layer->getProductCollection()->isLoaded()) {
            return false;
        }

        $matchedFilters = [];
        foreach ($listFilters as $idx => $filter) {
            $setting = $this->filterSetting->getSettingByLayerFilter($filter);
            if (!$this->checkFilterVisibility($setting, $layer->getCurrentCategory()->getId())) {
                continue;
            }

            $this->applyFilters($layer);

            if (!$this->checkFilterByDependency($setting)) {
                continue;
            }

            $matchedFilters[] = $filter;
        }

        $this->filtersMatched = true;
        $this->filters = $matchedFilters;

        return true;
    }

    /**
     * @param FilterSettingInterface $setting
     * @param $currentCategoryId
     *
     * @return bool
     */
    protected function checkFilterVisibility(FilterSettingInterface $setting, $currentCategoryId)
    {
        $visible = true;
        if ($setting->getVisibleInCategories() === VisibleInCategory::ONLY_IN_SELECTED_CATEGORIES
            && !in_array($currentCategoryId, $setting->getCategoriesFilter())
        ) {
            $visible = false;
        }

        if ($setting->getVisibleInCategories() === VisibleInCategory::HIDE_IN_SELECTED_CATEGORIES
            && in_array($currentCategoryId, $setting->getCategoriesFilter())
        ) {
            $visible = false;
        }

        return $visible;
    }

    /**
     * @param FilterSettingInterface $setting
     *
     * @return bool
     */
    protected function checkFilterByDependency(FilterSettingInterface $setting)
    {
        $matched = true;
        if ($attributesFilter = $setting->getAttributesFilter()) {
            $stateAttributes = $this->getStateAttributesIds();
            $intersects = array_intersect($attributesFilter, $stateAttributes);
            if (!$intersects) {
                $matched = false;
            }
        }

        if ($attributesOptionsFilter = $setting->getAttributesOptionsFilter()) {
            $stateAttributesOptions = $this->getActiveOptionIds();
            $intersects = array_intersect($attributesOptionsFilter, $stateAttributesOptions);
            if (!$intersects) {
                $matched = false;
            }
        }

        return $matched;
    }

    /**
     * At this point filters could not be applied (especially at search page).
     * @param Layer $layer
     */
    private function applyFilters(Layer $layer)
    {
        if ($this->filtersApplied) {
            return;
        }

        foreach ($this->getAllFilters($layer) as $filter) {
            $isAppliedCheckTrait = \Amasty\Shopby\Model\Layer\Filter\Traits\FilterTrait::class;
            if (in_array($isAppliedCheckTrait, class_uses($filter))) {
                //filter has multiply applying prevention mechanism
                $filter->apply($this->request);
            }
        }

        $this->filtersApplied = true;
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function getStateAttributesIds()
    {
        $ids = [];

        foreach ($this->shopbyRequest->getRequestParams() as $key => $param) {
            $filterModelId = $this->getFilterModelId($key);
            if ($filterModelId) {
                $ids[] = $filterModelId;
            }
        }

        return array_unique($ids);
    }

    /**
     * @param string $key
     *
     * @return int
     */
    protected function getFilterModelId($key)
    {
        $filter = $this->collectionExtendedFactory->get()->getItemByCode('attr_' . $key);
        $filterModel = $filter ? $filter->getAttributeModel() : false;

        return $filterModel ? $filterModel->getId() : 0;
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function getActiveOptionIds()
    {
        $ids = [];

        foreach ($this->shopbyRequest->getRequestParams() as $param) {
            if (isset($param[0])) {
                $ids[] = explode(',', $param[0]);
            }
        }

        if (count($ids)) {
            $ids = array_unique(array_merge(...$ids));
        }

        return $ids;
    }

    /**
     * @param Layer $layer
     *
     * @return array
     */
    protected function getAdditionalFilters(Layer $layer)
    {
        $additionalFilters = [];
        if ($this->isCustomFilterEnabled('stock') && $this->config->isEnabledShowOutOfStock()) {
            $additionalFilters[] = $this->objectManager->create(
                \Amasty\Shopby\Model\Layer\Filter\Stock::class,
                ['layer' => $layer]
            );
        }

        if ($this->isCustomFilterEnabled('rating')) {
            $additionalFilters[] = $this->objectManager->create(
                \Amasty\Shopby\Model\Layer\Filter\Rating::class,
                ['layer' => $layer]
            );
        }

        if ($this->isCustomFilterEnabled('am_is_new')) {
            $additionalFilters[] = $this->objectManager->create(
                \Amasty\Shopby\Model\Layer\Filter\IsNew::class,
                ['layer' => $layer]
            );
        }

        if ($this->isCustomFilterEnabled('am_on_sale')) {
            $additionalFilters[] = $this->objectManager->create(
                \Amasty\Shopby\Model\Layer\Filter\OnSale::class,
                ['layer' => $layer]
            );
        }

        return $additionalFilters;
    }

    /**
     * @param string $filterKey
     * @return bool
     */
    protected function isCustomFilterEnabled($filterKey)
    {
        return (bool)$this->config->getModuleConfig($filterKey . '_filter/enabled');
    }

    /**
     * @param $listStandartFilters
     * @param $listAdditionalFilters
     * @return array
     */
    protected function insertAdditionalFilters($listStandartFilters, $listAdditionalFilters)
    {
        if (count($listAdditionalFilters) == 0) {
            return $listStandartFilters;
        }

        return array_merge($listStandartFilters, $listAdditionalFilters);
    }

    /**
     * @param $first
     * @param $second
     * @return bool
     */
    public function sortingByPosition($first, $second)
    {
        return $this->getFilterPosition($first) > $this->getFilterPosition($second);
    }

    /**
     * @param $filter
     * @return int
     */
    public function getFilterPosition($filter)
    {
        if ($filter->hasAttributeModel()) {
            $position = $filter->getAttributeModel()->getPosition();
        } else {
            $position = $filter->getPosition();
        }

        return $position;
    }

    /**
     * @param \Magento\Catalog\Model\ResourceModel\Eav\Attribute $attribute
     * @return string
     */
    protected function getAttributeFilterClass(\Magento\Catalog\Model\ResourceModel\Eav\Attribute $attribute)
    {
        $filterClassName = parent::getAttributeFilterClass($attribute);

        if ($attribute->getBackendType() === 'decimal' && $attribute->getAttributeCode() !== 'price') {
            $filterClassName = $this->filterTypes[self::DECIMAL_FILTER];
        }

        return $filterClassName;
    }
}

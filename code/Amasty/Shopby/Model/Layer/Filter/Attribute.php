<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */
namespace Amasty\Shopby\Model\Layer\Filter;

use Magento\Catalog\Model\Layer;
use Magento\Framework\Exception\StateException;
use Magento\Search\Model\SearchEngine;
use Amasty\Shopby\Helper\FilterSetting;
use Magento\Store\Model\StoreManagerInterface;
use Amasty\ShopbyBase\Api\Data\FilterSettingInterface;
use Magento\Catalog\Model\Layer\Filter\AbstractFilter;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Amasty\Shopby\Model\Layer\Filter\Traits\FilterTrait;
use Amasty\Shopby\Helper\Group as GroupHelper;
use Magento\Framework\Filter\StripTags as TagFilter;
use Magento\Catalog\Model\Layer\Filter\ItemFactory as FilterItemFactory;
use Magento\Catalog\Model\Layer\Filter\Item\DataBuilder as ItemDataBuilder;
use Amasty\Shopby\Model\Search\RequestGenerator as ShopbyRequestGenerator;
use Amasty\Shopby\Model\Search\Adapter\Mysql\AggregationAdapter as MysqlAggregationAdapter;
use \Magento\Store\Model\Store;

/**
 * Layer attribute filter
 */
class Attribute extends AbstractFilter
{
    use FilterTrait;

    /**
     * @var TagFilter
     */
    private $tagFilter;

    /**
     * @var FilterSettingInterface
     */
    private $filterSetting;

    /**
     * @var MysqlAggregationAdapter
     */
    private $aggregationAdapter;

    /**
     * @var SearchEngine
     */
    private $searchEngine;

    /**
     * @var  FilterSetting
     */
    private $settingHelper;

    /**
     * @var  ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var \Amasty\Shopby\Model\Request
     */
    private $shopbyRequest;

    /**
     * @var GroupHelper
     */
    private $groupHelper;

    /**
     * @var \Amasty\ShopbyBase\Helper\OptionSetting
     */
    private $optionSettingHelper;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    private $messageManager;

    public function __construct(
        FilterItemFactory $filterItemFactory,
        StoreManagerInterface $storeManager,
        Layer $layer,
        ItemDataBuilder $itemDataBuilder,
        TagFilter $tagFilter,
        MysqlAggregationAdapter $aggregationAdapter,
        SearchEngine $searchEngine,
        FilterSetting $settingHelper,
        ScopeConfigInterface $scopeConfig,
        \Amasty\Shopby\Model\Request $shopbyRequest,
        GroupHelper $groupHelper,
        \Amasty\ShopbyBase\Helper\OptionSetting $optionSettingHelper,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        array $data = []
    ) {
        parent::__construct(
            $filterItemFactory,
            $storeManager,
            $layer,
            $itemDataBuilder,
            $data
        );

        $this->tagFilter = $tagFilter;
        $this->settingHelper = $settingHelper;
        $this->aggregationAdapter = $aggregationAdapter;
        $this->shopbyRequest = $shopbyRequest;
        $this->groupHelper = $groupHelper;
        $this->scopeConfig = $scopeConfig;
        $this->searchEngine = $searchEngine;
        $this->optionSettingHelper = $optionSettingHelper;
        $this->messageManager = $messageManager;
    }

    /**
     * Apply attribute option filter to product collection.
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */

    public function apply(\Magento\Framework\App\RequestInterface $request)
    {
        if ($this->isApplied()) {
            return $this;
        }

        $requestedOptionsString = $this->shopbyRequest->getFilterParam($this);

        if (empty($requestedOptionsString)) {
            return $this;
        }

        $requestedOptions = explode(',', $requestedOptionsString);

        $this->setCurrentValue($requestedOptions);
        $this->addState($requestedOptions);

        if (!$this->isMultiSelectAllowed() && count($requestedOptions) > 1) {
            $requestedOptions = array_slice($requestedOptions, 0, 1);
        }

        $attribute = $this->getAttributeModel();
        $id = $attribute->getAttributeId();
        $groups = $id ? $this->groupHelper->getGroupsByAttributeId($id) : [];

        /** @var \Amasty\Shopby\Model\ResourceModel\Fulltext\Collection $productCollection */
        $productCollection = $this->getLayer()->getProductCollection();
        if ($this->getFilterSetting()->isUseAndLogic()) {
            foreach ($requestedOptions as $key => $value) {
                $optionsFromGroup = $this->groupHelper->getFakeGroupOptionIdsByCode($groups, $value);
                $value = $optionsFromGroup ?: $value;

                $fakeAttributeCode = $this->getFakeAttributeCodeForApply($attribute->getAttributeCode(), $key);
                $productCollection->addFieldToFilter($fakeAttributeCode, $value);
            }
        } else {
            $optionValues = $requestedOptions;
            foreach ($optionValues as $key => $value) {
                $optionsFromGroup = $this->groupHelper->getFakeGroupOptionIdsByCode($groups, $value);
                if ($optionsFromGroup) {
                    unset($optionValues[$key]);
                    // @codingStandardsIgnoreLine
                    $optionValues = array_merge($optionValues, $optionsFromGroup);
                }
            }

            $productCollection->addFieldToFilter($attribute->getAttributeCode(), $optionValues);
        }

        return $this;
    }

    /**
     * @param array $values
     */
    private function addState(array $values)
    {
        if (!$this->shouldAddState()) {
            return;
        }

        $labels = [];

        $attribute = $this->getAttributeModel();

        foreach ($values as $value) {
            $labelGroup = null;
            if ($attributeId = $attribute->getAttributeId()) {
                $labelGroup = $this->groupHelper->getGroupLabel($attributeId, $value);
            }
            if ($labelGroup) {
                $labels[] = $labelGroup;
            } else {
                $labels[] = $this->getOptionText($value);
            }
        }

        foreach ($labels as $label) {
            $item = $this->_createItem($label, $values);
            $this->getLayer()->getState()
                ->addFilter(
                    $item
                );
        }
    }

    /**
     * @return bool
     */
    public function shouldAddState()
    {
        // Could be overwritten in plugins.
        return true;
    }

    /**
     * @return bool
     */
    private function isMultiSelectAllowed()
    {
        return $this->getFilterSetting()->isMultiselect();
    }

    /**
     * @return FilterSettingInterface
     */
    protected function getFilterSetting()
    {
        if ($this->filterSetting === null) {
            $this->filterSetting = $this->settingHelper->getSettingByLayerFilter($this);
        }
        return $this->filterSetting;
    }

    /**
     * @param string $attributeCode
     * @param $key
     * @return string
     */
    private function getFakeAttributeCodeForApply($attributeCode, $key)
    {
        if ($key > 0) {
            $attributeCode .= ShopbyRequestGenerator::FAKE_SUFFIX . $key;
        }

        return $attributeCode;
    }

    /**
     * @return int
     */
    public function getItemsCount()
    {
        return count($this->getItems());
    }

    /**
     * @param array $a
     * @param array $b
     * @return int
     */
    public function sortOption($a, $b)
    {
        $pattern = '@^(\d+)@';
        if (preg_match($pattern, $a['label'], $ma) && preg_match($pattern, $b['label'], $mb)) {
            $r = $ma[1] - $mb[1];
            if ($r != 0) {
                return $r;
            }
        }

        return strcasecmp($a['label'], $b['label']);
    }

    /**
     * Get data array for building attribute filter items.
     *
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _getItemsData()
    {
        $selected = !!$this->shopbyRequest->getFilterParam($this);
        if ($selected && !$this->isVisibleWhenSelected()) {
            return [];
        }

        $options = $this->getOptions();
        $optionsFacetedData = $this->getOptionsFacetedData();

        if (!$optionsFacetedData) {
            return [];
        }

        $this->addItemsToDataBuilder($options, $optionsFacetedData);

        $itemsData = $this->getItemsFromDataBuilder();

        return $itemsData;
    }

    /**
     * @return array
     */
    private function getOptions()
    {
        $attribute = $this->getAttributeModel();
        $options = $attribute->getFrontend()->getSelectOptions();

        if ($this->getFilterSetting()->hasAttributeGroups()) {
            /**
             * @var \Amasty\Shopby\Api\Data\GroupAttrInterface[] $groups
             */
            $groups = $this->getFilterSetting()->getAttributeGroups();
            $groupOptions = [];
            $allGroupedOptions = [];
            foreach ($groups as $group) {
                $groupOptions[] = [
                    'label' => $group->getName(),
                    'value' => $group->getGroupCode()
                ];
                if ($group->hasOptions()) {
                    foreach ($group->getOptions() as $option) {
                        $allGroupedOptions[] = $option->getOptionId();
                    }
                }
            }

            if (count($allGroupedOptions)) {
                foreach ($options as $key => $value) {
                    if (in_array($value['value'], $allGroupedOptions)) {
                        unset($options[$key]);
                    }
                }
            }

            $options = array_merge($groupOptions, $options);
        }

        if ($this->getFilterSetting()->getSortOptionsBy() == \Amasty\Shopby\Model\Source\SortOptionsBy::NAME) {
            usort($options, [$this, 'sortOption']);
        }

        $this->sortOptionsByFeatured($options);

        return $options;
    }

    /**
     * Additional Sort options by is_featured setting
     *
     * @param $options
     * @return array
     */
    public function sortOptionsByFeatured(&$options)
    {
        $attribute = $this->getAttributeModel();
        $filterCode = \Amasty\Shopby\Helper\FilterSetting::ATTR_PREFIX . $attribute->getAttributeCode();
        $featuredOptionArray = [];
        $nonFeaturedOptionArray = [];
        $featuredOptions = $this->optionSettingHelper->getAllFeaturedOptionsArray();
        foreach ($options as $option) {
            if ($this->isOptionFeatured($featuredOptions, $filterCode, $option)) {
                $featuredOptionArray[] = $option;
            } else {
                $nonFeaturedOptionArray[] = $option;
            }
        }
        $options = array_merge($featuredOptionArray, $nonFeaturedOptionArray);
        if (count($featuredOptionArray)
            && count($nonFeaturedOptionArray)
            && !$this->filterSetting->getNumberUnfoldedOptions()
        ) {
            $this->filterSetting->setNumberUnfoldedOptions(count($featuredOptionArray));
        }

        return $options;
    }

    /**
     * @param array $options
     * @param string $filterCode
     * @param array $option
     * @return bool
     */
    private function isOptionFeatured($options, $filterCode, $option)
    {
        $isFeatured = false;
        if (isset($options[$filterCode][$option['value']][$this->getStoreId()])) {
            $isFeatured = (bool)$options[$filterCode][$option['value']][$this->getStoreId()];
        } elseif (isset($options[$filterCode][$option['value']][Store::DEFAULT_STORE_ID])) {
            $isFeatured = (bool)$options[$filterCode][$option['value']][Store::DEFAULT_STORE_ID];
        }

        return $isFeatured;
    }

    /**
     * @return array
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getOptionsFacetedData()
    {
        $optionsFacetedData = $this->generateOptionsFacetedData();
        $optionsFacetedData = $this->adjustFacetedDataToGroup($optionsFacetedData);

        if (count($optionsFacetedData)) {
            $optionsFacetedData = $this->convertOptionsFacetedData($optionsFacetedData);
        }

        return $optionsFacetedData;
    }

    /**
     * @param array $optionsFacetedData
     *
     * @return array
     */
    protected function convertOptionsFacetedData($optionsFacetedData)
    {
        $attributeValue = $this->shopbyRequest->getFilterParam($this);
        $values = explode(",", $attributeValue);
        foreach ($values as $value) {
            if (!empty($value) && !array_key_exists($value, $optionsFacetedData)) {
                $optionsFacetedData[$value] = ['value' => $value, 'count' => 0];
            }
        }

        return $optionsFacetedData;
    }

    /**
     * @return array
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function generateOptionsFacetedData()
    {
        /** @var \Amasty\Shopby\Model\ResourceModel\Fulltext\Collection $productCollectionOrigin */
        $productCollectionOrigin = $this->getLayer()->getProductCollection();
        $attribute = $this->getAttributeModel();

        try {
            $optionsFacetedData = $productCollectionOrigin->getFacetedData(
                $attribute->getAttributeCode(),
                $this->getAlteredQueryResponse()
            );
        } catch (StateException $e) {
            if (!$this->messageManager->hasMessages()) {
                $this->messageManager->addErrorMessage(
                    __(
                        'Make sure that "%1" attribute can be used in layered navigation',
                        $attribute->getAttributeCode()
                    )
                );
            }
            $optionsFacetedData = [];
        }

        return $optionsFacetedData;
    }

    /**
     * @return \Magento\Framework\Search\ResponseInterface|null
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getAlteredQueryResponse()
    {
        $alteredQueryResponse = null;

        if ($this->hasCurrentValue() && !$this->getFilterSetting()->isUseAndLogic()) {
            $requestBuilder = $this->getRequestBuilder();
            $queryRequest = $requestBuilder->create();
            $alteredQueryResponse = $this->searchEngine->search($queryRequest);
        }

        return $alteredQueryResponse;
    }

    /**
     * @return \Amasty\Shopby\Model\Request\Builder
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function getRequestBuilder()
    {
        $requestBuilder = $this->getMemRequestBuilder();
        $attributeCode = $this->getAttributeModel()->getAttributeCode();
        $requestBuilder->removePlaceholder($attributeCode);
        $requestBuilder->setAggregationsOnly($attributeCode);

        return $requestBuilder;
    }

    /**
     * @param array $optionsFacetedData
     *
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function adjustFacetedDataToGroup(array $optionsFacetedData)
    {
        if (!$optionsFacetedData) {
            return $optionsFacetedData;
        }

        $groups = $this->groupHelper->getGroupsByAttributeId($this->getAttributeModel()->getId());

        foreach ($groups as $group) {
            $key = GroupHelper::LAST_POSSIBLE_OPTION_ID - $group->getId();

            if (isset($optionsFacetedData[$key])) {
                $code = $group->getGroupCode();
                $optionsFacetedData[$code] = $optionsFacetedData[$key];
                unset($optionsFacetedData[$key]);
            }
        }

        return $optionsFacetedData;
    }

    /**
     * @param array $options
     * @param array $optionsFacetedData
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function addItemsToDataBuilder($options, $optionsFacetedData)
    {
        if (!$options) {
            return;
        }
        foreach ($options as $option) {
            if (empty($option['value'])) {
                continue;
            }

            $isFilterableAttribute = $this->getAttributeIsFilterable($this->getAttributeModel());
            if (isset($optionsFacetedData[$option['value']])
                || $isFilterableAttribute != static::ATTRIBUTE_OPTIONS_ONLY_WITH_RESULTS
            ) {
                $count = 0;
                if (isset($optionsFacetedData[$option['value']]['count'])) {
                    $count = $optionsFacetedData[$option['value']]['count'];
                }
                $this->itemDataBuilder->addItemData(
                    $this->tagFilter->filter($option['label']),
                    $option['value'],
                    $count
                );
            }
        }
    }

    /**
     * Get items data according to attribute settings.
     * @return array
     */
    private function getItemsFromDataBuilder()
    {
        $itemsData = $this->itemDataBuilder->build();
        if (count($itemsData) == 1
            && !$this->isOptionReducesResults(
                $itemsData[0]['count'],
                $this->getLayer()->getProductCollection()->getSize()
            )
        ) {
            $itemsData = $this->getReducedItemsData($itemsData);
        }

        return $itemsData;
    }
}

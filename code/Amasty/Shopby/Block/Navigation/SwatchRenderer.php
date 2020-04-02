<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */


namespace Amasty\Shopby\Block\Navigation;

use Amasty\Shopby\Model\Source\DisplayMode;
use Magento\Catalog\Model\Layer\Filter\Item as FilterItem;
use Magento\Catalog\Model\ResourceModel\Layer\Filter\AttributeFactory;
use Magento\Eav\Model\Entity\Attribute;
use Amasty\Shopby\Helper\FilterSetting as FilterSettingHelper;
use Magento\Eav\Model\Entity\Attribute\Option;
use Magento\Catalog\Model\Layer\Filter\AbstractFilter;
use Magento\Swatches\Block\LayeredNavigation\RenderLayered;
use \Magento\Store\Model\Store;
use Amasty\Shopby\Api\Data\GroupAttrInterface;
use Magento\Swatches\Model\Swatch;

/**
 * Class SwatchRenderer
 * @package Amasty\Shopby\Block\Navigation
 */
class SwatchRenderer extends RenderLayered implements RendererInterface
{
    const SWATCH_TYPE_OPTION_IMAGE = 'option_image';
    const VAR_COUNT = 'amasty_shopby_count';
    const VAR_SELECTED = 'amasty_shopby_selected';

    const FILTERABLE_NO_RESULTS = '2';

    /**
     * @var \Amasty\Shopby\Helper\UrlBuilder
     */
    private $urlBuilderHelper;

    /**
     * @var FilterSettingHelper
     */
    private $filterSettingHelper;

    /**
     * @var \Amasty\ShopbyBase\Api\Data\FilterSettingInterface
     */
    private $filterSetting;

    /**
     * @var \Amasty\Shopby\Helper\Data
     */
    private $helper;

    /**
     * @var \Amasty\ShopbyBase\Helper\OptionSetting
     */
    private $optionSettingHelper;

    /**
     * @var \Amasty\Shopby\Helper\Group
     */
    private $groupHelper;

    /**
     * @var string
     */
    protected $_template = 'layer/filter/swatch/default.phtml';

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        Attribute $eavAttribute,
        AttributeFactory $layerAttribute,
        \Magento\Swatches\Helper\Data $swatchHelper,
        \Magento\Swatches\Helper\Media $mediaHelper,
        \Amasty\Shopby\Helper\UrlBuilder $urlBuilderHelper,
        \Amasty\Shopby\Helper\Data $helper,
        \Amasty\ShopbyBase\Helper\OptionSetting $optionSettingHelper,
        FilterSettingHelper $filterSettingHelper,
        \Amasty\Shopby\Helper\Group $groupHelper,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $eavAttribute,
            $layerAttribute,
            $swatchHelper,
            $mediaHelper,
            $data
        );
        $this->groupHelper = $groupHelper;
        $this->filterSettingHelper = $filterSettingHelper;
        $this->helper = $helper;
        $this->optionSettingHelper = $optionSettingHelper;
        $this->urlBuilderHelper = $urlBuilderHelper;
    }

    /**
     * @param string $attributeCode
     * @param int $optionId
     * @return string
     */
    public function buildUrl($attributeCode, $optionId)
    {
        return $this->urlBuilderHelper->buildUrl($this->filter, $optionId);
    }

    /**
     * @return \Amasty\ShopbyBase\Api\Data\FilterSettingInterface
     */
    public function getFilterSetting()
    {
        if ($this->filterSetting === null) {
            $this->filterSetting = $this->filterSettingHelper->getSettingByLayerFilter($this->filter);
        }
        return $this->filterSetting;
    }

    /**
     * @return array
     */
    public function getSwatchData()
    {
        $swatchData = parent::getSwatchData();
        unset($swatchData['options']['']);
        foreach ($this->getMultiSelectSwatches(array_keys($swatchData['options'])) as $id => $value) {
            $swatchData['swatches'][$id] = $value;
        }

        if ($this->getFilterSetting()->hasAttributeGroups()) {
            $swatchDataGroup = $this->getGroupSwatchData($swatchData);
            $swatchData['options'] += $swatchDataGroup['options'];
            $swatchData['swatches'] += $swatchDataGroup['swatches'];
        }

        if ($this->getFilterSetting()->getSortOptionsBy() == \Amasty\Shopby\Model\Source\SortOptionsBy::NAME) {
            uasort($swatchData['options'], [$this, 'sortSwatchData']);
        }

        $swatchData['options'] = $this->sortingByFeatures($swatchData);

        return $swatchData;
    }

    /**
     * @param $swatchData
     * @return array
     */
    private function sortingByFeatures($swatchData)
    {
        $attribute = $this->getFilterSetting()->getAttributeModel();
        $filterCode = \Amasty\Shopby\Helper\FilterSetting::ATTR_PREFIX . $attribute->getAttributeCode();
        $featuredOptionArray = [];
        $optionKeys = array_keys($swatchData['options']);
        $featuredOptions = $this->optionSettingHelper->getAllFeaturedOptionsArray();
        foreach ($swatchData['swatches'] as $key => $option) {
            if ($this->isOptionFeatured($featuredOptions, $filterCode, $option)) {
                $keyPosition = array_search($key, $optionKeys);
                if ($keyPosition) {
                    unset($optionKeys[$keyPosition]);
                }
                $featuredOptionArray[] = $key;
            }
            $swatchData['options'][$key]['key'] = $key;
        }
        $optionKeys = array_merge($featuredOptionArray, $optionKeys);

        $options = [];
        foreach ($optionKeys as $key) {
            $options[$key] = $swatchData['options'][$key];
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
        if (isset($option['option_id'])) {
            if (isset($options[$filterCode][$option['option_id']][$this->getStoreId()])) {
                $isFeatured = (bool)$options[$filterCode][$option['option_id']][$this->getStoreId()];
            } elseif (isset($options[$filterCode][$option['option_id']][Store::DEFAULT_STORE_ID])) {
                $isFeatured = (bool)$options[$filterCode][$option['option_id']][Store::DEFAULT_STORE_ID];
            }
        }

        return $isFeatured;
    }

    /**
     * Retrieve current store id scope
     *
     * @return int
     */
    public function getStoreId()
    {
        $storeId = $this->_getData('store_id');
        if ($storeId === null) {
            $storeId = $this->_storeManager->getStore()->getId();
        }
        return $storeId;
    }

    /**
     * @param array $optionIds
     * @return array
     */
    private function getMultiSelectSwatches($optionIds)
    {
        $attribute = $this->getFilterSetting()->getAttributeModel();
        return $this->helper->getSwatchesFromImages($optionIds, $attribute);
    }

    /**
     * Fix Magento logic
     *
     * @param FilterItem $filterItem
     * @return bool
     */
    protected function isOptionVisible(FilterItem $filterItem)
    {
        return !$this->isOptionDisabled($filterItem) || $this->isShowEmptyResults();
    }

    /**
     * Fix Magento logic
     *
     * @return bool
     */
    protected function isShowEmptyResults()
    {
        return $this->eavAttribute->getIsFilterable() === self::FILTERABLE_NO_RESULTS;
    }

    /**
     * @param FilterItem $filterItem
     * @param Option $swatchOption
     * @return array
     */
    protected function getOptionViewData(FilterItem $filterItem, Option $swatchOption)
    {
        $data = parent::getOptionViewData($filterItem, $swatchOption);
        $data[self::VAR_COUNT] = $filterItem->getCount();
        $data[self::VAR_SELECTED] = $this->isFilterItemSelected($filterItem);

        return $data;
    }

    /**
     * @param $a
     * @param $b
     * @return int
     */
    public function sortSwatchData($a, $b)
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
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getTooltipHtml()
    {
        return $this->getLayout()->createBlock(
            \Amasty\Shopby\Block\Navigation\Widget\Tooltip::class
        )
            ->setFilterSetting($this->getFilterSetting())
            ->toHtml();
    }

    /**
     * @return string
     */
    public function toHtml()
    {
        $html = parent::toHtml();

        if ($this->getFilterSetting()->isShowTooltip()) {
            $html .= $this->getTooltipHtml();
        }

        $html .= $this->filterSettingHelper->getShowMoreButtonBlock($this->getFilterSetting())->toHtml();
        return $html;
    }

    /**
     * @param \Amasty\Shopby\Model\Layer\Filter\Item $filterItem
     * @return int
     */
    public function isFilterItemSelected(\Amasty\Shopby\Model\Layer\Filter\Item $filterItem)
    {
        return $this->helper->isFilterItemSelected($filterItem);
    }

    /**
     * @return bool
     */
    public function collectFilters()
    {
        return $this->helper->collectFilters();
    }

    /**
     * @return \Magento\Catalog\Model\Layer\Filter\AbstractFilter
     */
    public function getFilter()
    {
        return $this->filter;
    }

    /**
     * @param $data
     * @return array
     */
    private function getGroupSwatchData($data)
    {
        if ($this->getFilterSetting()->hasAttributeGroups()) {
            $attributeGroups = $this->getFilterSetting()->getAttributeGroups();
            $attributeOptions = [];
            $attributeSwatches = [];
            $showNoResults = (int)$this->getFilterSetting()->getAttributeModel()->getIsFilterable()
                != AbstractFilter::ATTRIBUTE_OPTIONS_ONLY_WITH_RESULTS;
            foreach ($attributeGroups as $group) {
                /**
                 * @var GroupAttrInterface $group
                 */
                $group->setName($this->groupHelper->chooseGroupLabel($group->getName()));
                if ($currentOption = $this->getFilterOptionGroup($this->filter->getItems(), $group)) {
                    $attributeOptions[$group->getGroupCode()] = $currentOption;
                } elseif ($showNoResults) {
                    $attributeOptions[$group->getGroupCode()] = $this->getUnusedOptionGroup($group);
                }
                $attributeSwatches[$group->getGroupCode()] = $this->getUnusedSwatchGroup($group);
            }
            $data['options'] = $attributeOptions;
            $data['swatches'] = $attributeSwatches;
        }

        return $data;
    }

    /**
     * @param $swatchOption
     * @return array
     */
    protected function getUnusedOptionGroup($swatchOption)
    {
        $customStyle = '';
        $linkToOption = $this->buildUrl($this->eavAttribute->getAttributeCode(), $swatchOption->getGroupCode());
        return [
            'label' => $swatchOption->getName(),
            'link' => $linkToOption,
            'custom_style' => $customStyle,
            self::VAR_COUNT => 0,
            self::VAR_SELECTED => 0
        ];
    }

    /**
     * @param $swatchOption
     * @return array
     */
    protected function getUnusedSwatchGroup($swatchOption)
    {
        $data = [
            "option_id" => $swatchOption->getId(),
             "type" => $swatchOption->getType(),
             "value" => $swatchOption->getVisual()
        ];

        if ($data['type'] == Swatch::SWATCH_TYPE_TEXTUAL
            && in_array(
                $this->getFilterSetting()->getDisplayMode(),
                [DisplayMode::MODE_IMAGES_LABELS, DisplayMode::MODE_IMAGES]
            )
        ) {
            $data['type'] = SwatchRenderer::SWATCH_TYPE_OPTION_IMAGE;
        }

        return $data;
    }

    /**
     * @param $filterItems
     * @param $option
     * @return array|bool
     */
    private function getFilterOptionGroup($filterItems, $option)
    {
        $resultOption = false;
        $filterItem = $this->getFilterItemById($filterItems, $option->getGroupCode());
        if ($filterItem && $this->isOptionVisible($filterItem)) {
            $resultOption = $this->getOptionViewDataGroup($filterItem, $option);
        }

        return $resultOption;
    }

    /**
     * @param FilterItem $filterItem
     * @param $option
     * @return array
     */
    private function getOptionViewDataGroup(FilterItem $filterItem, $option)
    {
        $data = $this->getUnusedOptionGroup($option);
        $data[self::VAR_COUNT] = $filterItem->getCount();
        $data[self::VAR_SELECTED] = $this->isFilterItemSelected($filterItem);

        return $data;
    }

    /**
     * @return string
     */
    public function getSearchForm()
    {
        return $this->getLayout()->createBlock(
            \Amasty\Shopby\Block\Navigation\Widget\SearchForm::class
        )
            ->assign('filterCode', $this->getFilterSetting()->getFilterCode())
            ->setFilter($this->filter)
            ->toHtml();
    }
}

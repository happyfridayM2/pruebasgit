<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_ShopbyBase
 */


namespace Amasty\ShopbyBase\Model;

use Amasty\ShopbyBase\Api\Data\FilterSettingInterface;
use Amasty\ShopbyBase\Model\Source\DisplayMode;
use Amasty\ShopbyBase\Model\Source\ShowProductQuantities;
use Amasty\ShopbySeo\Helper\Meta;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Store\Model\ScopeInterface;
use Amasty\ShopbyBrand\Block\Widget\BrandListAbstract;
use Amasty\ShopbySeo\Model\Source\IndexMode;
use Amasty\ShopbyBase\Helper\Data;
use Amasty\Shopby\Model\Source\SubcategoriesView;
use Amasty\ShopbyBase\Model\Integration\IntegrationFactory;

class FilterSetting extends \Magento\Framework\Model\AbstractModel implements FilterSettingInterface, IdentityInterface
{
    const CACHE_TAG = 'amshopby_filter_setting';

    protected $_eventPrefix = 'amshopby_filter_setting';

    /**
     * @var \Magento\Catalog\Helper\Data
     */
    protected $catalogHelper;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Amasty\ShopbyBase\Api\Group\Attribute\DataFactoryProviderInterface
     */
    protected $groupAttrDataProviderFactory = null;

    /**
     * @var Data
     */
    private $baseHelper;

    /**
     * @var IntegrationFactory
     */
    private $integrationFactory;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Helper\Data $catalogHelper,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        Data $baseHelper,
        IntegrationFactory $integrationFactory,
        \Amasty\ShopbyBase\Api\Group\Attribute\DataFactoryProviderInterface $groupAttributeDataFactoryProvider = null,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->catalogHelper = $catalogHelper;
        $this->scopeConfig = $scopeConfig;
        $this->groupAttrDataProviderFactory = $groupAttributeDataFactoryProvider;
        $this->baseHelper = $baseHelper;
        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection,
            $data
        );
        $this->integrationFactory = $integrationFactory;
    }

    /**
     * Protected FilterSetting constructor
     */
    protected function _construct()
    {
        $this->_init(\Amasty\ShopbyBase\Model\ResourceModel\FilterSetting::class);
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->getData(self::FILTER_SETTING_ID);
    }

    /**
     * @return string
     */
    public function getDisplayMode()
    {
        return $this->getData(self::DISPLAY_MODE);
    }

    /**
     * @return string
     */
    public function getFilterCode()
    {
        return $this->getData(self::FILTER_CODE);
    }

    /**
     * @return string
     */
    public function getFollowMode()
    {
        if ($this->getData(self::FOLLOW_MODE) === null) {
            $this->setData(self::FOLLOW_MODE, IndexMode::MODE_NEVER);
        }

        return $this->getData(self::FOLLOW_MODE);
    }

    /**
     * @return string
     */
    public function getRelNofollow()
    {
        return $this->getData(self::REL_NOFOLLOW);
    }

    /**
     * @return bool
     */
    public function isAddNofollow()
    {
        if (!$this->getShopbySeoHelper()->isEnableRelNofollow()
            || !$this->getRelNofollow()
        ) {
            return false;
        }

        if ($this->isPageNofollow()) {
            $result = true;
        } elseif (!$this->getRelNofollow()) {
            $result = false;
        } else {
            $result = $this->isNofollowByMode();
        }

        return $result;
    }

    /**
     * @return bool
     */
    public function isNofollowByMode()
    {
        switch ($this->getFollowMode()) {
            case IndexMode::MODE_NEVER:
                $result = true;
                break;
            case IndexMode::MODE_SINGLE_ONLY:
                $result = $this->isNofollowBySingleMode();
                break;
            case IndexMode::MODE_ALWAYS:
                $result = false;
                break;
            default:
                $result = false;
                break;
        }

        return $result;
    }

    /**
     * @return bool
     */
    protected function isNofollowBySingleMode()
    {
        if ($helper = $this->getShopbyHelper()) {
            $result = $this->isFiltersApply($helper);
        }

        return $result ?? false;
    }

    /**
     * @param \Amasty\Shopby\Helper\Data $helper
     * @return bool
     */
    private function isFiltersApply($helper)
    {
        $count = count($helper->getSelectedFiltersSettings());

        if ($helper->isBrandPage()) {
            --$count;
        }

        return (bool)$count;
    }

    /**
     * @return mixed
     */
    private function getShopbyHelper()
    {
        return $this->integrationFactory->get(\Amasty\Shopby\Helper\Data::class, true);
    }

    /**
     * @return mixed
     */
    public function getShopbySeoHelper()
    {
        return $this->integrationFactory->get(\Amasty\ShopbySeo\Helper\Data::class, true);
    }

    /**
     * @return bool
     */
    protected function isPageNofollow()
    {
        return strpos($this->_registry->registry(Meta::AMSHOPBYSEO_FOLLOW), 'nofollow') !== false;
    }

    /**
     * @return bool|null
     */
    public function getAddFromToWidget()
    {
        return $this->getData(self::ADD_FROM_TO_WIDGET);
    }

    /**
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * @return string
     */
    public function getIndexMode()
    {
        if ($this->getData(self::INDEX_MODE) === null) {
            $this->setData(self::INDEX_MODE, IndexMode::MODE_NEVER);
        }
        return $this->getData(self::INDEX_MODE);
    }

    /**
     * @param string $currencySymbol
     * @return string
     */
    public function getUnitsLabel($currencySymbol = '')
    {
        if ($this->getUnitsLabelUseCurrencySymbol()) {
            return $currencySymbol;
        }
        return parent::getUnitsLabel();
    }

    /**
     * @return bool
     */
    public function isMultiselect()
    {
        $allProducts = $this->_registry->registry(Data::SHOPBY_CATEGORY_INDEX);
        $isBrandOnAllProducts = $this->getFilterCode() == $this->getBrandCode()
            && isset($allProducts);

        return $this->getData(self::IS_MULTISELECT)
            && $this->isDisplayTypeAllowsMultiselect()
            && !$isBrandOnAllProducts;
    }

    /**
     * @return string
     */
    public function getBrandCode()
    {
        $brandCode = $this->scopeConfig->getValue(
            BrandListAbstract::PATH_BRAND_ATTRIBUTE_CODE,
            ScopeInterface::SCOPE_STORE
        );

        return 'attr_' . $brandCode;
    }

    /**
     * @return bool
     */
    public function isSeoSignificant()
    {
        return $this->getData(self::IS_SEO_SIGNIFICANT);
    }

    /**
     * @return bool
     */
    public function isExpanded()
    {
        $expandValue = $this->getData(self::EXPAND_VALUE);

        return ($expandValue == 2 && !$this->baseHelper->isMobile()) || $expandValue == 1;
    }

    /**
     * @return int
     */
    public function getSortOptionsBy()
    {
        return $this->getData(self::SORT_OPTIONS_BY);
    }

    /**
     * @return int
     */
    public function getShowProductQuantities()
    {
        return $this->getData(self::SHOW_PRODUCT_QUANTITIES);
    }

    /**
     * @return bool|int
     */
    public function isShowProductQuantities()
    {
        $showProductQuantities = $this->getShowProductQuantities();
        if ($showProductQuantities == ShowProductQuantities::SHOW_DEFAULT
            || $showProductQuantities === null
        ) {
            $showProductQuantities = $this->catalogHelper->shouldDisplayProductCountOnLayer();
        } else {
            $showProductQuantities = $showProductQuantities != ShowProductQuantities::SHOW_NO;
        }

        return $showProductQuantities;
    }

    /**
     * @return bool
     */
    public function isShowTooltip()
    {
        $isFilterTooltipsEnabled = $this->scopeConfig->isSetFlag(
            'amshopby/tooltips/enabled',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        $tooltip = $this->getTooltip();

        return $isFilterTooltipsEnabled && !empty($tooltip);
    }

    /**
     * @return bool
     */
    public function isShowSearchBox($optionsCount)
    {
        return $this->getData(self::IS_SHOW_SEARCH_BOX)
            && (!$this->getLimitOptionsShowSearchBox() || $optionsCount > $this->getLimitOptionsShowSearchBox());
    }

    /**
     * @return mixed
     */
    public function getNumberUnfoldedOptions()
    {
        return $this->getData(self::NUMBER_UNFOLDED_OPTIONS);
    }

    /**
     * @return mixed
     */
    public function getTooltip()
    {
        return $this->getData(self::TOOLTIP);
    }

    /**
     * @return string
     */
    public function getVisibleInCategories()
    {
        return $this->getData(self::VISIBLE_IN_CATEGORIES);
    }

    /**
     * @return array
     */
    public function getCategoriesFilter()
    {
        $this->getResource()->lookupCategoriesFilter($this);
        return $this->getData(self::CATEGORIES_FILTER);
    }

    /**
     * @return array
     */
    public function getAttributesFilter()
    {
        $this->getResource()->lookupAttributesFilter($this);
        return $this->getData(self::ATTRIBUTES_FILTER);
    }

    /**
     * @return array
     */
    public function getAttributesOptionsFilter()
    {
        $this->getResource()->lookupAttributesOptionsFilter($this);
        return $this->getData(self::ATTRIBUTES_OPTIONS_FILTER);
    }

    /**
     * @return bool
     */
    public function isUseAndLogic()
    {
        return $this->getData(self::IS_USE_AND_LOGIC) && $this->isMultiselect();
    }

    /**
     * @return string
     */
    public function getBlockPosition()
    {
        return $this->getData(self::BLOCK_POSITION);
    }

    /**
     * @return bool
     */
    public function getShowIconsOnProduct()
    {
        return $this->getData(self::SHOW_ICONS_ON_PRODUCT);
    }

    /**
     * @return bool
     */
    public function getUnitsLabelUseCurrencySymbol()
    {
        return $this->getData(self::USE_CURRENCY_SYMBOL);
    }

    /**
     * @param int $id
     * @return $this
     */
    public function setId($id)
    {
        return $this->setData(self::FILTER_SETTING_ID, $id);
    }

    /**
     * @param int $displayMode
     * @return $this
     */
    public function setDisplayMode($displayMode)
    {
        return $this->setData(self::DISPLAY_MODE, $displayMode);
    }

    /**
     * @param int $featuredOnly
     * @return $this
     */
    public function setShowFeaturedOnly($featuredOnly)
    {
        $this->setData(self::SHOW_FEATURED_ONLY, $featuredOnly);
        return $this;
    }

    /**
     * @param int $displayMode
     * @return $this
     */
    public function setCategoryTreeDisplayMode($displayMode)
    {
        $this->setData(self::CATEGORY_TREE_DISPLAY_MODE, $displayMode);
        return $this;
    }

    /**
     * @param string $filterCode
     * @return $this
     */
    public function setFilterCode($filterCode)
    {
        return $this->setData(self::FILTER_CODE, $filterCode);
    }

    /**
     * @param int $indexMode
     * @return $this
     */
    public function setIndexMode($indexMode)
    {
        return $this->setData(self::INDEX_MODE, $indexMode);
    }

    /**
     * @param int $followMode
     * @return $this
     */
    public function setFollowMode($followMode)
    {
        return $this->setData(self::FOLLOW_MODE, $followMode);
    }

    /**
     * @param int $relNofollow
     * @return $this
     */
    public function setRelNofollow($relNofollow)
    {
        return $this->setData(self::REL_NOFOLLOW, $relNofollow);
    }

    /**
     * @param bool $isMultiselect
     * @return $this
     */
    public function setIsMultiselect($isMultiselect)
    {
        return $this->setData(self::IS_MULTISELECT, $isMultiselect);
    }

    /**
     * @param bool $isSeoSignificant
     * @return $this
     */
    public function setIsSeoSignificant($isSeoSignificant)
    {
        return $this->setData(self::IS_SEO_SIGNIFICANT, $isSeoSignificant);
    }

    /**
     * @param bool $isExpanded
     * @return $this
     */
    public function setIsExpanded($isExpanded)
    {
        return $this->setData(self::EXPAND_VALUE, $isExpanded);
    }

    /**
     * @param bool $addFromToWidget
     *
     * @return FilterSettingInterface
     */
    public function setAddFromToWidget($addFromToWidget)
    {
        return $this->setData(self::ADD_FROM_TO_WIDGET, $addFromToWidget);
    }

    /**
     * @param int $sortOptionsBy
     *
     * @return FilterSettingInterface
     */
    public function setSortOptionsBy($sortOptionsBy)
    {
        return $this->setData(self::SORT_OPTIONS_BY, $sortOptionsBy);
    }

    /**
     * @param int $showProductQuantities
     *
     * @return FilterSettingInterface
     */
    public function setShowProductQuantities($showProductQuantities)
    {
        return $this->setData(self::SHOW_PRODUCT_QUANTITIES, $showProductQuantities);
    }

    /**
     * @param bool $isShowSearchBox
     *
     * @return FilterSettingInterface
     */
    public function setIsShowSearchBox($isShowSearchBox)
    {
        return $this->setData(self::IS_SHOW_SEARCH_BOX, $isShowSearchBox);
    }

    /**
     * @param int $numberOfUnfoldedOptions
     *
     * @return FilterSettingInterface
     */
    public function setNumberUnfoldedOptions($numberOfUnfoldedOptions)
    {
        return $this->setData(self::NUMBER_UNFOLDED_OPTIONS, $numberOfUnfoldedOptions);
    }

    /**
     * @param string $tooltip
     *
     * @return $this
     */
    public function setTooltip($tooltip)
    {
        return $this->setData(self::TOOLTIP, $tooltip);
    }

    /**
     * @param string $visibleInCategories
     * @return $this
     */
    public function setVisibleInCategories($visibleInCategories)
    {
        return $this->setData(self::VISIBLE_IN_CATEGORIES, $visibleInCategories);
    }

    /**
     * @param array $categoriesFilter
     * @return $this
     */
    public function setCategoriesFilter($categoriesFilter)
    {
        return $this->setData(self::CATEGORIES_FILTER, $categoriesFilter);
    }

    /**
     * @param array $attributesFilter
     * @return $this
     */
    public function setAttributesFilter($attributesFilter)
    {
        return $this->setData(self::ATTRIBUTES_FILTER, $attributesFilter);
    }

    /**
     * @param array $attributesOptionsFilter
     * @return $this
     */
    public function setAttributesOptionsFilter($attributesOptionsFilter)
    {
        return $this->setData(self::ATTRIBUTES_OPTIONS_FILTER, $attributesOptionsFilter);
    }

    /**
     * @param bool $isUseAndLogic
     *
     * @return $this
     */
    public function setIsUseAndLogic($isUseAndLogic)
    {
        return $this->setData(self::IS_USE_AND_LOGIC, $isUseAndLogic);
    }

    /**
     * @return bool
     */
    protected function isDisplayTypeAllowsMultiselect()
    {
        return in_array($this->getDisplayMode(), $this->getMultiSelectModes());
    }

    /**
     * @return array
     */
    public function getMultiSelectModes()
    {
        return [
            DisplayMode::MODE_DEFAULT,
            DisplayMode::MODE_DROPDOWN,
            DisplayMode::MODE_IMAGES,
            DisplayMode::MODE_IMAGES_LABELS,
            DisplayMode::MODE_TEXT_SWATCH
        ];
    }

    /**
     * @param int $blockPosition
     *
     * @return $this
     */
    public function setBlockPosition($blockPosition)
    {
        return $this->setData(self::BLOCK_POSITION, $blockPosition);
    }

    /**
     * @param bool $isShowLinks
     * @return $this
     */
    public function setShowIconsOnProduct($isShowLinks)
    {
        return $this->setData(self::SHOW_ICONS_ON_PRODUCT, $isShowLinks);
    }

    /**
     * @param \Magento\Eav\Model\Entity\Attribute $attribute
     * @return $this
     */
    public function setAttributeModel($attribute)
    {
        $this->setData('attribute_model', $attribute);
        return $this;
    }

    /**
     * @return null | \Magento\Eav\Model\Entity\Attribute
     */
    public function getAttributeModel()
    {
        return $this->getData('attribute_model');
    }

    /**
     * @return bool
     */
    public function hasAttributeGroups()
    {
        $groups = $this->getAttributeGroups();
        return !empty($groups);
    }

    /**
     * @return array
     */
    public function getAttributeGroups()
    {
        if ($this->groupAttrDataProviderFactory && $this->getAttributeModel()) {
            $dataProvider = $this->groupAttrDataProviderFactory->create();
            $attributeId = $this->getAttributeModel()->getId();
            return $dataProvider->getGroupsByAttributeId($attributeId);
        }

        return [];
    }

    /**
     * @return mixed
     */
    public function getEnableOverflowScroll()
    {
        $overflowScrollConfig = $this->scopeConfig->getValue(
            'amshopby/general/enable_overflow_scroll',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        $enableOverflowScroll = $this->getSubcategoriesView() == SubcategoriesView::FLY_OUT
            ? false
            : $overflowScrollConfig;

        return $enableOverflowScroll;
    }

    /**
     * @param string $label
     * @return $this
     */
    public function setUnitsLabel($label)
    {
        return $this->setData(self::UNITS_LABEL);
    }

    /**
     * @param int $useCurrency
     * @return $this
     */
    public function setUnitsLabelUseCurrencySymbol($useCurrency)
    {
        return $this->setData(self::USE_CURRENCY_SYMBOL, $useCurrency);
    }

    /**
     * @return int
     */
    public function getShowFeaturedOnly()
    {
        return $this->getData(self::SHOW_FEATURED_ONLY);
    }

    /**
     * @return int
     */
    public function getCategoryTreeDisplayMode()
    {
        return $this->getData(self::CATEGORY_TREE_DISPLAY_MODE);
    }

    /**
     * @return bool
     */
    public function isApplyFlyOut()
    {
        $subcategoriesView = $this->getSubcategoriesView();

        return ($subcategoriesView == SubcategoriesView::FLY_OUT)
            || ($subcategoriesView == SubcategoriesView::FLY_OUT_FOR_DESKTOP_ONLY
                && !$this->baseHelper->isMobile());
    }
}

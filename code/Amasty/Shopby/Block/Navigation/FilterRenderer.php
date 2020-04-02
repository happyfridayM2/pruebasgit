<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */
namespace Amasty\Shopby\Block\Navigation;

use Amasty\Shopby\Model\Source\SubcategoriesView;
use Amasty\ShopbyBase\Api\Data\FilterSettingInterface;
use Amasty\Shopby\Helper\FilterSetting;
use Amasty\Shopby\Helper\Data as ShopbyHelper;
use Amasty\ShopbyBase\Helper\Data as Basehelper;
use Amasty\Shopby\Helper\UrlBuilder;
use Amasty\Shopby\Model\Layer\Filter\Item;
use Amasty\Shopby\Model\Source\DisplayMode;
use Magento\Catalog\Model\Layer\Filter\FilterInterface;
use Magento\Catalog\Model\Layer\Resolver;

/**
 * @api
 */
class FilterRenderer extends \Magento\LayeredNavigation\Block\Navigation\FilterRenderer implements RendererInterface
{
    const TOP_NAV_RENDERER_NAME = 'amshopby.catalog.topnav.renderer';

    /**
     * @var  FilterSetting
     */
    protected $settingHelper;

    /**
     * @var  UrlBuilder
     */
    protected $urlBuilder;

    /**
     * @var  FilterInterface
     */
    protected $filter;

    /**
     * @var \Amasty\ShopbyBase\Api\Data\FilterSettingInterface
     */
    private $filterSetting;

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
     * @var Basehelper
     */
    private $baseHelper;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        FilterSetting $settingHelper,
        UrlBuilder $urlBuilder,
        ShopbyHelper $helper,
        \Amasty\Shopby\Helper\Category $categoryHelper,
        Resolver $resolver,
        Basehelper $baseHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->settingHelper = $settingHelper;
        $this->urlBuilder = $urlBuilder;
        $this->helper = $helper;
        $this->categoryHelper = $categoryHelper;
        $this->baseHelper = $baseHelper;
        $this->layer = $resolver->get();
    }

    /**
     * @param FilterInterface $filter
     * @return string
     */
    public function render(FilterInterface $filter)
    {
        $this->filter = $filter;
        $setting = $this->settingHelper->getSettingByLayerFilter($filter);

        if ($filter instanceof \Amasty\Shopby\Model\Layer\Filter\Category) {
            $categoryTreeHtml = $this->getCategoryTreeHtml($filter);
            $this->assign('categoryTreeHtml', $categoryTreeHtml);
            $template = $this->getCustomTemplateForCategoryFilter($setting);
        } else {
            $template = $this->getTemplateByFilterSetting($setting);
        }

        $this->setTemplate($template);
        $this->assign('filterSetting', $setting);

        if ($this->filter instanceof \Amasty\Shopby\Api\Data\FromToFilterInterface) {
            $fromToConfig = $this->filter->getFromToConfig();
            $this->assign('fromToConfig', $fromToConfig);
        }

        $html = parent::render($filter)
            . $this->getTooltipHtml($setting)
            . $this->getShowMoreHtml($setting);
        return $html;
    }

    /**
     * @param \Amasty\ShopbyBase\Model\FilterSetting $setting
     *
     * @return string
     */
    protected function getShowMoreHtml(\Amasty\ShopbyBase\Model\FilterSetting $setting)
    {
        return $this->settingHelper->getShowMoreButtonBlock($setting)->toHtml();
    }

    /**
     * @param FilterInterface $filter
     *
     * @return string
     */
    protected function getCategoryTreeHtml(FilterInterface $filter)
    {
        return $this->getLayout()
            ->createBlock(\Amasty\Shopby\Block\Navigation\FilterRenderer\Category::class)
            ->setFilter($filter)
            ->render();
    }

    /**
     * @param \Amasty\ShopbyBase\Model\FilterSetting $setting
     * @return string
     */
    public function getTooltipHtml(\Amasty\ShopbyBase\Model\FilterSetting $setting)
    {
        if (!$setting->isShowTooltip()) {
            return '';
        }

        return $this->getLayout()->createBlock(\Amasty\Shopby\Block\Navigation\Widget\Tooltip::class)
            ->setFilterSetting($setting)
            ->toHtml();
    }

    /**
     * @param FilterSettingInterface $filterSetting
     * @return string
     */
    protected function getTemplateByFilterSetting(FilterSettingInterface $filterSetting)
    {
        switch ($filterSetting->getDisplayMode()) {
            case DisplayMode::MODE_SLIDER:
                $template = "layer/filter/slider.phtml";
                break;
            case DisplayMode::MODE_DROPDOWN:
                $template = $this->getMultiselectFilter($filterSetting);
                break;
            case DisplayMode::MODE_FROM_TO_ONLY:
                $template = "layer/widget/fromto.phtml";
                break;
            default:
                $template = "layer/filter/default.phtml";
                break;
        }
        return $template;
    }

    /**
     * @param $filterSetting
     * @return string
     */
    private function getMultiselectFilter($filterSetting)
    {
        return $this->baseHelper->isMobile() && $filterSetting->isMultiselect()
            ? "layer/filter/default.phtml"
            : "layer/filter/dropdown.phtml";
    }

    /**
     * @param FilterSettingInterface $filterSetting
     * @return string
     */
    protected function getCustomTemplateForCategoryFilter(FilterSettingInterface $filterSetting)
    {
        switch ($filterSetting->getDisplayMode()) {
            case DisplayMode::MODE_DROPDOWN:
                $template = "layer/filter/category/dropdown.phtml";
                break;
            default:
                if ($filterSetting->isApplyFlyOut()) {
                    $template = 'layer/filter/category/labels_fly_out.phtml';
                } else {
                    $template = 'layer/filter/category/labels_folding.phtml';
                }
                break;
        }
        return $template;
    }

    /**
     * @param \Amasty\Shopby\Model\Layer\Filter\Item $filterItem
     * @return int
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function checkedFilter(\Amasty\Shopby\Model\Layer\Filter\Item $filterItem)
    {
        $checked = $this->helper->isFilterItemSelected($filterItem);

        if (!$checked && $filterItem->getFilter()->getRequestVar() == 'cat') {
            $checked = $filterItem->getValue() == $this->layer->getCurrentCategory()->getId();
        }
        return $checked;
    }

    /**
     * @return string
     */
    public function getClearUrl()
    {
        if (isset($this->_viewVars['filterItems']) && is_array($this->_viewVars['filterItems'])) {
            foreach ($this->_viewVars['filterItems'] as $item) {
                /** @var Item $item */
                if ($this->checkedFilter($item)) {
                    return $item->getRemoveUrl();
                }
            }
        }

        return '';
    }

    /**
     * @return string
     */
    public function getSliderUrlTemplate()
    {
        return $this->urlBuilder->buildUrl($this->filter, 'amshopby_slider_from-amshopby_slider_to');
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
    public function collectFilters()
    {
        return $this->helper->collectFilters();
    }

    /**
     * @return FilterInterface
     */
    public function getFilter()
    {
        return $this->filter;
    }

    public function getRadioAllowed()
    {
        return $this->_scopeConfig->isSetFlag(
            'amshopby/general/keep_single_choice_visible',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
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
    public function isTopNav()
    {
        return $this->getNameInLayout() == self::TOP_NAV_RENDERER_NAME;
    }

    /**
     * @return \Amasty\ShopbyBase\Api\Data\FilterSettingInterface
     */
    public function getFilterSetting()
    {
        $this->filterSetting = $this->settingHelper->getSettingByLayerFilter($this->filter);

        return $this->filterSetting;
    }

    /**
     * @return string
     */
    public function getFromToWidget($type)
    {
        return $this->getLayout()->createBlock(
            \Amasty\Shopby\Block\Navigation\Widget\FromTo::class
        )
            ->assign('filterSetting', $this->getFilterSetting())
            ->assign('fromToConfig', $this->filter->getFromToConfig())
            ->setSliderUrlTemplate($this->getSliderUrlTemplate())
            ->setFilter($this->filter)
            ->setWidgetType($type)
            ->toHtml();
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

    /**
     * @return int
     */
    public function getCurrentCategoryId()
    {
        return $this->helper->getCurrentCategory()->getId();
    }
}

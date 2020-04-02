<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */


namespace Amasty\Shopby\Block\Navigation;

use Amasty\Shopby\Model\Layer\Filter\Item;
use Amasty\Shopby\Helper\Data as ShopbyHelper;

class State extends \Magento\LayeredNavigation\Block\Navigation\State
{
    /**
     * @var string
     */
    protected $_template = 'layer/state.phtml';

    /**
     * @var \Amasty\Shopby\Helper\FilterSetting
     */
    protected $filterSettingHelper;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $managerInterface;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * @var ShopbyHelper
     */
    protected $helper;

    /**
     * @var \Magento\Framework\View\Element\BlockFactory
     */
    private $blockFactory;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Catalog\Model\Layer\Resolver $layerResolver,
        \Amasty\Shopby\Helper\FilterSetting $filterSettingHelper,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        ShopbyHelper $helper,
        \Magento\Framework\View\Element\BlockFactory $blockFactory,
        \Amasty\ShopbyBase\Api\UrlBuilderInterface $urlBuilder,
        array $data = []
    ) {
        $this->filterSettingHelper = $filterSettingHelper;
        $this->managerInterface = $context->getStoreManager();
        $this->priceCurrency = $priceCurrency;
        $this->helper = $helper;
        $this->blockFactory = $blockFactory;
        parent::__construct($context, $layerResolver, $data);
        $this->_urlBuilder = $urlBuilder;
    }

    /**
     * @param \Magento\Catalog\Model\Layer\Filter\FilterInterface $filter
     * @return \Amasty\ShopbyBase\Api\Data\FilterSettingInterface
     */
    public function getFilterSetting(\Magento\Catalog\Model\Layer\Filter\FilterInterface $filter)
    {
        return $this->filterSettingHelper->getSettingByLayerFilter($filter);
    }

    /**
     * @param Item $filter
     * @param bool $showLabels
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getSwatchHtml(\Amasty\Shopby\Model\Layer\Filter\Item $filter, $showLabels = false)
    {
        return $this->getLayout()->createBlock(\Amasty\Shopby\Block\Navigation\State\Swatch::class)
            ->setFilter($filter)
            ->showLabels($showLabels)
            ->toHtml();
    }

    /**
     * @return string
     */
    public function collectFilters()
    {
        return $this->helper->collectFilters();
    }

    /**
     * @return int
     */
    public function getUnfoldedCount()
    {
        return $this->helper->getUnfoldedCount();
    }

    /**
     * @return string
     */
    public function createShowMoreButtonBlock()
    {
        return $this->blockFactory->createBlock(\Amasty\Shopby\Block\Navigation\Widget\HideMoreOptions::class)
            ->setIsState(true)
            ->setUnfoldedOptions($this->getUnfoldedCount())
            ->toHtml();
    }

    /**
     * @param Item $filter
     * @return string
     */
    public function viewLabel($filter)
    {
        $filterSetting = $this->getFilterSetting($filter->getFilter());

        switch ($filterSetting->getDisplayMode()) {
            case \Amasty\Shopby\Model\Source\DisplayMode::MODE_IMAGES:
                $value =  $this->getSwatchHtml($filter);
                break;
            case \Amasty\Shopby\Model\Source\DisplayMode::MODE_TEXT_SWATCH:
                $value =  $this->getSwatchHtml($filter);
                break;
            case \Amasty\Shopby\Model\Source\DisplayMode::MODE_IMAGES_LABELS:
                $value =  $this->getSwatchHtml($filter, true);
                break;
            default:
                $value = $this->viewExtendedLabel($filter);
                break;
        }

        return $value;
    }

    /**
     * @param Item $filter
     * @return string
     */
    protected function viewExtendedLabel($filter)
    {
        if ($filter->getFilter()->getRequestVar() == \Amasty\Shopby\Model\Source\DisplayMode::ATTRUBUTE_PRICE) {
            $currencyRate = (float) $filter->getFilter()->getCurrencyRate();

            if ($currencyRate != 1) {
                $value = $this->generateValueLabel($filter);
            } else {
                $value = $filter->getOptionLabel();
            }
        } else {
            $value = $this->stripTags($filter->getOptionLabel());
        }

        return $value;
    }

    /**
     * @param $filterItem
     * @param $currencyRate
     * @return \Magento\Framework\Phrase
     */
    private function generateValueLabel($filterItem)
    {
        $arguments = $filterItem->getLabel()->getArguments();
        $filter = $filterItem->getFilter();
        $filterSetting = $this->filterSettingHelper->getSettingByLayerFilter($filter);
        $stepSlider = $filterSetting->getSliderStep();

        if (!isset($arguments[1])) {
            $arguments[1] = "";
        }

        $currencySymbol = $this->escapeHtml($filter->getCurrencySymbol());

        $arguments[0] = preg_replace("/[^,.0-9]/", '', $arguments[0]);
        $arguments[1] = preg_replace("/[^,.0-9]/", '', $arguments[1]);

        $posDotInFrom = strpos($arguments[0], '.');
        $posDotInTo = strpos($arguments[1], '.');
        $posCommaInFrom = strpos($arguments[0], ',');
        $posCommaInTo = strpos($arguments[1], ',');

        $arguments[0] = $this->removeSeparator($posDotInFrom, $posCommaInFrom, $arguments[0]);
        $arguments[1] = $this->removeSeparator($posDotInTo, $posCommaInTo, $arguments[1]);

        $arguments[0] = preg_replace("/[']/", '', $arguments[0]);
        $arguments[1] = preg_replace("/[']/", '', $arguments[1]);

        $value = __(
            '%1 - %2',
            $this->generateSpanPrice((float)$arguments[0], $stepSlider, $currencySymbol),
            $this->generateSpanPrice(
                $arguments[1] ? (float)$arguments[1] : $arguments[1],
                $stepSlider,
                $currencySymbol,
                true
            )
        );

        return $value;
    }

    /**
     * @param $posDot
     * @param $posComma
     * @param $value
     * @return string
     */
    private function removeSeparator($posDot, $posComma, $value)
    {
        if ($posDot !== false && $posComma !== false) {
            if ($posDot < $posComma) {
                $value = preg_replace("/[.]/", '', $value);
            } else {
                $value = preg_replace("/[,]/", '', $value);
            }
        }

        return $value;
    }

    /**
     * @param $value
     * @param $currencyRate
     * @param $stepSlider
     * @param $currencySymbol
     * @return string
     */
    private function generateSpanPrice($value, $stepSlider, $currencySymbol, $flagTo = false)
    {
        if (!$value && $flagTo) {
            $resultPrice = __('above');
        } else {
            $resultPrice = $this->priceCurrency->format($value);
        }

        return '<span class="price">' . $resultPrice . '</span>';
    }

    /**
     * @param $value
     * @param $filterItem
     * @return float|string
     */
    public function getFilterValue($value, $filterItem)
    {
        $filter = $filterItem->getFilter();
        if ($filter instanceof \Amasty\Shopby\Model\Layer\Filter\Price && count($value) >= 2) {
            $value[0] = $value[0] ? $value[0] * $filter->getCurrencyRate() : '';
            $value[1] = $value[1] ? $value[1] * $filter->getCurrencyRate() : '';
        } elseif (is_array($value)) {
            $value = $value[0];
        }

        return $value;
    }

    /**
     * @param $resultValue
     * @return string
     */
    public function getDataValue($resultValue)
    {
        $value = null;

        if (isset($resultValue)) {
            $value = $this->escapeHtml(
                $this->stripTags(is_array($resultValue) ? implode('-', $resultValue) : $resultValue, false)
            );
        }

        return $value;
    }

    /**
     * @param $filter
     * @param $value
     * @return array
     */
    public function changeValueForMultiselect($filter, $value)
    {
        if ($filter instanceof \Amasty\Shopby\Model\Layer\Filter\Price) {
            $value = [];
        } else {
            $value = array_filter(array_slice((array)$value, 1));
        }

        return $value;
    }
}

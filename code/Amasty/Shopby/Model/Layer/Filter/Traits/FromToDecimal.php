<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */


namespace Amasty\Shopby\Model\Layer\Filter\Traits;

use \Amasty\Shopby\Model\Source\DisplayMode;
use Amasty\Shopby\Model\Source\PositionLabel;

trait FromToDecimal
{
    use FilterTrait;

    /**
     * set from and to values for decimal filter
     * @param $from
     * @param $to
     *
     * @return $this
     */
    protected function setFromTo($from, $to)
    {
        list($from, $to) = $this->prepareFromTo($from, $to);
        $this->setCurrentValue(['from'=>$from, 'to'=>$to]);
        return $this;
    }

    /**
     * @return null
     */
    public function getCurrentFrom()
    {
        return $this->getCurrentByKey('from');
    }

    /**
     * @return null
     */
    public function getCurrentTo()
    {
        return $this->getCurrentByKey('to');
    }

    /**
     * @param $key
     *
     * @return null
     */
    protected function getCurrentByKey($key)
    {
        $current = null;
        if ($this->hasCurrentValue()) {
            $current = $this->currentValue[$key];
        }
        return $current;
    }

    /**
     * @param $from
     * @param $to
     *
     * @return array
     */
    protected function prepareFromTo($from, $to)
    {
        if ($to && $from > $to) {
            $toTmp = $to;
            $to = $from;
            $from = $toTmp;
        }

        return [$from, $to];
    }

    public function getSignsCount($number, $slider = 1)
    {
        if (($number > 0 && $number < 1) && $slider) {
            $number = $this->trimZeros((string)$number);
            $pos = strpos($number, ".");
            if ($pos !== false) {
                return strlen($number) - $pos;
            }
        }

        return 0;
    }

    public function getFloatNumber($size)
    {
        if (!$size) {
            $size = 3;
        }

        return (float) 1 / (int)str_pad('1', $size, '0', STR_PAD_RIGHT);
    }

    protected function trimZeros($str)
    {
        preg_match("/(\d\.\d*?[1-9]+)/i", $str, $matches);
        return $matches[0];
    }

    /**
     * @param $min
     * @param $sliderMin
     * @return mixed
     */
    protected function getMin($min, $sliderMin)
    {
        if ($sliderMin) {
            $min = ($sliderMin < $min) ? $min : $sliderMin;
        }

        return $min;
    }

    /**
     * @param $min
     * @param $max
     * @param $sliderMax
     * @return mixed
     */
    protected function getMax($min, $max, $sliderMax)
    {
        if ($sliderMax) {
            $max = ($sliderMax > $max) && ($max > $min) ? $max : $sliderMax;
        }

        return $max;
    }

    /**
     * @param $from
     * @param $min
     * @return bool
     */
    private function isIdentically($from, $min)
    {
        return (floor($from) == floor($min));
    }

    private function getExtremeValues(\Amasty\ShopbyBase\Model\FilterSetting $filterSetting, $facets, $currencyRate = 0)
    {
        $from = $this->getCurrentFrom();
        $to = $this->getCurrentTo();

        return ['from' => $from, 'to' => $to];
    }

    private function getConfig($attrType)
    {
        $config = [
            'from' => null,
            'to' => null,
            'min' => null,
            'max' => null,
            'requestVar' => null,
            'step' => null,
            'template' => null,
            'curRate' => 1,
        ];

        $filterSetting = $this->settingHelper->getSettingByLayerFilter($this);

        if ((string)$filterSetting->getDisplayMode() === (string)DisplayMode::MODE_SLIDER ||
            (string)$filterSetting->getDisplayMode() === (string)DisplayMode::MODE_FROM_TO_ONLY ||
            (bool)$filterSetting->getAddFromToWidget()
        ) {
            $facets = $this->getFacetedData();

            if (!isset($facets['data']) || (isset($facets['data']['count']) && $facets['data']['count'] == 0)) {
                return $config;
            }

            $min = $this->getMin(floatval($facets['data']['min']), $filterSetting->getSliderMin());
            $max = $this->getMax($min, floatval($facets['data']['max']), $filterSetting->getSliderMax());

            if ($attrType == DisplayMode::ATTRUBUTE_PRICE) {
                $min *= $this->getCurrencyRate();
                $max *= $this->getCurrencyRate();
            }

            $from = $this->getCurrentFrom() !== null
                ? $this->getCurrentFrom() ? floatval($this->getCurrentFrom()) : ''
                : null;
            $to = $this->getCurrentTo() !== null
                ? $this->getCurrentTo() ? floatval($this->getCurrentTo()) : ''
                : null;
            $template = $this->getTemplateForSlider($filterSetting);

            $config =
                [
                    'from' => $from,
                    'to' => $to,
                    'min' => $from !== null && $min > $from ? round($from) : $min,
                    'max' => $max < $to ? round($to) : $max,
                    'requestVar' => $this->getRequestVar(),
                    'step' => round($filterSetting->getSliderStep(), 4),
                    'template' => $template,
                    'curRate' => $attrType == DisplayMode::ATTRUBUTE_PRICE ? $this->getCurrencyRate() : 1,
                ];
        }

        return $config;
    }

    /**
     * @return string
     */
    public function getCurrencySymbol()
    {
        return $this->currencySymbol;
    }

    /**
     * @param \Amasty\ShopbyBase\Model\FilterSetting $filterSetting
     * @return string
     */
    private function getTemplateForSlider(\Amasty\ShopbyBase\Model\FilterSetting $filterSetting)
    {
        if ($filterSetting->getUnitsLabelUseCurrencySymbol()) {
            /** @var \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency */
            $priceCurrency = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Magento\Framework\Pricing\PriceCurrencyInterface::class);
            $trialValue = '345';

            //label position can be customized by "currency_display_options_forming" event. Trigger it.
            $formattedExample = $priceCurrency->format($trialValue, false, 0);

            $labelPosition = strpos($formattedExample, $trialValue) ==! 0
                ? PositionLabel::POSITION_BEFORE
                : PositionLabel::POSITION_AFTER;
            $labelUnit = $this->currencySymbol;
        } else {
            $labelUnit = $filterSetting->getUnitsLabel();
            $labelPosition = $filterSetting->getPositionLabel();
        }

        if ($labelPosition == PositionLabel::POSITION_BEFORE) {
            $template = $labelUnit . '{from}' . ' - ' . $labelUnit . '{to}';
        } else {
            $template = '{from}' . $labelUnit . ' - {to}' . $labelUnit;
        }

        return $template;
    }

    /**
     * @return int
     */
    public function getItemsCount()
    {
        $itemsCount = $this->isIgnoreRanges() ? 0 : parent::getItemsCount();

        if ($itemsCount == 0) {
            /**
             * show up filter event don't have any option
             */
            $fromToConfig = $this->getFromToConfig();
            if ($fromToConfig && $fromToConfig['min'] != $fromToConfig['max']) {
                return 1;
            }
        }

        return $itemsCount;
    }

    /**
     * @return bool
     */
    private function isIgnoreRanges()
    {
        $filterSetting = $this->settingHelper->getSettingByLayerFilter($this);

        return $filterSetting->getDisplayMode() == DisplayMode::MODE_FROM_TO_ONLY
            || $filterSetting->getDisplayMode() == DisplayMode::MODE_SLIDER;
    }

    /**
     * @return bool
     */
    private function getPriceType()
    {
        return true;
    }

    /**
     * @return bool
     */
    private function isMultiSelectAllowed()
    {
        return true;
    }

    /**
     * @param $displayMode
     * @return bool
     */
    private function isSliderOrFromTo($displayMode)
    {
        return $displayMode == \Amasty\Shopby\Model\Source\DisplayMode::MODE_SLIDER ||
            $displayMode == \Amasty\Shopby\Model\Source\DisplayMode::MODE_FROM_TO_ONLY;
    }
}

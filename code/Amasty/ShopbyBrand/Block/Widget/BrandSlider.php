<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_ShopbyBrand
 */


namespace Amasty\ShopbyBrand\Block\Widget;

use Amasty\ShopbyBase\Api\Data\OptionSettingInterface;
use \Magento\Eav\Model\Entity\Attribute\Option;

class BrandSlider extends BrandListAbstract implements \Magento\Widget\Block\BlockInterface
{
    const HTML_ID = 'amslider_id';

    const DEFAULT_IMG_WIDTH = 130;

    const CONFIG_VALUES_PATH = 'amshopby_brand/slider';

    const PATH_SLIDER_COLOR_HEADER = 'amshopby_brand/slider/slider_header_color';

    const DEFAULT_VALUE_HEADER_COLOR = '#f58c12';

    const PATH_SLIDER_COLOR_TITLE = 'amshopby_brand/slider/slider_title_color';

    const DEFAULT_VALUE_TITLE_COLOR = '#ffffff';

    const PATH_SLIDER_TITLE = 'amshopby_brand/slider/slider_title';

    public function _construct()
    {
        $this->getItems();
        parent::_construct();
    }

    /**
     * @param \Magento\Eav\Model\Entity\Attribute\Option $option
     * @param OptionSettingInterface $setting
     *
     * @return array
     */
    protected function getItemData(Option $option, OptionSettingInterface $setting)
    {
        $result = [];
        if ($setting->getIsFeatured()) {
            $result = [
                'label'    => $setting->getLabel() ?: $option->getLabel(),
                'url'      => $this->getBrandUrl($option),
                'img'      => $setting->getSliderImageUrl(),
                'position' => $setting->getSliderPosition(),
                'alt'      => $setting->getSmallImageAlt() ? : $setting->getLabel()
            ];
        }

        return $result;
    }

    /**
     * @return $this
     */
    protected function applySorting()
    {
        if ($this->getData('sort_by') == 'name') {
            usort($this->items, [$this, '_sortByName']);
        } else {
            usort($this->items, [$this, '_sortByPosition']);
        }

        return $this;
    }

    /**
     * @return array
     */
    public function getSliderOptions()
    {
        $options = [];
        $itemsPerView = max(1, $this->getData('items_number'));
        $options['slidesPerView'] = $itemsPerView;
        $options['loop'] = $this->getData('infinity_loop') ? 'true' : 'false';
        $options['simulateTouch'] = $this->getData('simulate_touch') ? 'true' : 'false';
        if ($this->getData('pagination_show')) {
            $options['pagination'] = '".swiper-pagination"';
            $options['paginationClickable'] = $this->getData('pagination_clickable') ? 'true' : 'false';
        }

        if ($this->getData('autoplay')) {
            $options['autoplay'] = (int)$this->getData('autoplay_delay');
        }

        return $options;
    }

    /**
     * @return string
     */
    public function toHtml()
    {
        if (!count($this->getItems())) {
            return '';
        }

        return parent::toHtml();
    }

    /**
     * @param array $a
     * @param array $b
     *
     * @return int
     */
    protected function _sortByPosition($a, $b)
    {
        return $a['position'] - $b['position'];
    }

    /**
     * Getting slider header color
     *
     * @return string
     */
    public function getHeaderColor()
    {
        $res = $this->_scopeConfig
            ->getValue(self::PATH_SLIDER_COLOR_HEADER, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        return $res ?: self::DEFAULT_VALUE_HEADER_COLOR;
    }

    /**
     * Getting slider title color
     *
     * @return string
     */
    public function getTitleColor()
    {
        $res = $this->_scopeConfig
            ->getValue(self::PATH_SLIDER_COLOR_TITLE, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        return $res ?: self::DEFAULT_VALUE_TITLE_COLOR;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->_scopeConfig
            ->getValue(self::PATH_SLIDER_TITLE, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return string
     */
    protected function getConfigValuesPath()
    {
        return self::CONFIG_VALUES_PATH;
    }

    /**
     * @return bool
     */
    public function isSliderEnabled()
    {
        return count($this->getItems()) > (int)$this->getData('items_number');
    }
}

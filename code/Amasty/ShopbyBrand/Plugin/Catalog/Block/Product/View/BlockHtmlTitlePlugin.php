<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_ShopbyBrand
 */


namespace Amasty\ShopbyBrand\Plugin\Catalog\Block\Product\View;

use Amasty\ShopbyBase\Model\ResourceModel\OptionSetting\CollectionFactory;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Amasty\ShopbyBase\Plugin\Catalog\Block\Product\View\BlockHtmlTitlePluginAbstract;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\BlockFactory;
use Amasty\ShopbyBase\Model\OptionSetting;
use Magento\Store\Model\StoreManagerInterface;
use Amasty\ShopbyBrand\Model\Source\Tooltip;

/**
 * Class BlockHtmlTitlePlugin
 *
 * @package Amasty\ShopbyBrand\Plugin\Catalog\Block\Product\View
 */
class BlockHtmlTitlePlugin extends BlockHtmlTitlePluginAbstract
{
    /**
     * @var \Amasty\ShopbyBase\Helper\Data
     */
    protected $brandHelper;

    public function __construct(
        CollectionFactory $optCollectionFactory,
        Registry $registry,
        BlockFactory $blockFactory,
        StoreManagerInterface $storeManager,
        Configurable $configurableType,
        \Amasty\ShopbyBrand\Helper\Data $brandHelper,
        $data = []
    ) {
        parent::__construct($optCollectionFactory, $registry, $storeManager, $blockFactory, $configurableType, $data);
        $this->brandHelper = $brandHelper;
    }

    /**
     * Add Brand Label to Product Page
     *
     * @param \Magento\Theme\Block\Html\Title $original
     * @param $html
     * @return string
     */
    public function afterToHtml(\Magento\Theme\Block\Html\Title $original, $html)
    {
        if ($this->isShowLogo()) {
            $html = parent::afterToHtml($original, $html);
        }

        return $html;
    }

    /**
     * @return array
     */
    protected function getAttributeCodes()
    {
        return $this->brandHelper->getBrandAttributeCode() ? [$this->brandHelper->getBrandAttributeCode()] : [];
    }

    /**
     * @param OptionSetting $setting
     * @return string
     */
    protected function getOptionSettingUrl(OptionSetting $setting)
    {
        $url = '';
        $option = $setting->getAttributeOption();
        if ($option) {
            $url = $this->brandHelper->getBrandUrl($option);
        }
        
        return $url;
    }

    /**
     * @return bool
     */
    protected function isToolTipEnabled()
    {
        if (isset($this->data['tooltip_enabled'])) {
            $result = $this->data['tooltip_enabled'];
        } else {
            $setting = $this->brandHelper->getModuleConfig('general/tooltip_enabled');
            $result = in_array(Tooltip::PRODUCT_PAGE, explode(',', $setting));
        }

        return $result;
    }

    /**
     * @return bool
     */
    protected function isShowShortDescription()
    {
        if (isset($this->data['show_short_description'])) {
            $result = $this->data['show_short_description'];
        } else {
            $result = (bool)$this->brandHelper->getModuleConfig('product_page/display_description');
        }

        return $result;
    }

    /**
     * @return bool
     */
    protected function isShowLogo()
    {
        return (bool)$this->brandHelper->getModuleConfig('product_page/display_brand_image');
    }

    /**
     * @return int
     */
    protected function getProductPageWidth()
    {
        if (isset($this->data['width'])) {
            $result = $this->data['width'];
        } else {
            $result = $this->brandHelper->getModuleConfig('product_page/width');
        }

        return $result;
    }

    /**
     * @param array $item
     * @return array|string
     */
    protected function getTooltipTemplate(array $item)
    {
        return $this->brandHelper->generateToolTipContent($item);
    }

    /**
     * @return int
     */
    protected function getProductPageHeight()
    {
        if (isset($this->data['height'])) {
            $result = $this->data['height'];
        } else {
            $result = $this->brandHelper->getModuleConfig('product_page/height');
        }

        return $result;
    }
}

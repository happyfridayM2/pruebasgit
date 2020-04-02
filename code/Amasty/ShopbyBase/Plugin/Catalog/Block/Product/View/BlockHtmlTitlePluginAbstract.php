<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_ShopbyBase
 */


namespace Amasty\ShopbyBase\Plugin\Catalog\Block\Product\View;

use Amasty\ShopbyBase\Model\OptionSetting;
use Amasty\ShopbyBase\Model\ResourceModel\OptionSetting\CollectionFactory;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\BlockFactory;
use Amasty\ShopbyBase\Api\Data\OptionSettingInterface;
use Magento\Store\Model\StoreManagerInterface;

abstract class BlockHtmlTitlePluginAbstract
{
    const IMAGE_URL = 'image_url';

    const LINK_URL = 'link_url';

    const TITLE = 'title';

    const SHORT_DESCRIPTION = 'short_description';

    const TOOLTIP_JS = 'tooltip_js';

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var BlockFactory
     */
    private $blockFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Configurable
     */
    protected $configurableType;

    /**
     * @var array
     */
    protected $data;

    /**
     * @var CollectionFactory
     */
    private $optionCollectionFactory;

    public function __construct(
        CollectionFactory $optionCollectionFactory,
        Registry $registry,
        StoreManagerInterface $storeManager,
        BlockFactory $blockFactory,
        Configurable $configurableType,
        $data = []
    ) {
        $this->registry = $registry;
        $this->blockFactory = $blockFactory;
        $this->configurableType = $configurableType;
        $this->storeManager = $storeManager;
        $this->data = $data;
        $this->optionCollectionFactory = $optionCollectionFactory;
    }

    /**
     * @return array
     */
    abstract protected function getAttributeCodes();

    /**
     * Add Brand Label to Product Page
     *
     * @param \Magento\Theme\Block\Html\Title $original
     * @param $html
     *
     * @return string
     */
    public function afterToHtml(
        \Magento\Theme\Block\Html\Title $original,
        $html
    ) {
        $logoHtml = $this->generateLogoHtml();

        $html = str_replace('/h1>', '/h1>' . $logoHtml, $html);

        return $html;
    }

    /**
     * @return string
     */
    public function generateLogoHtml()
    {
        $html = '';
        $optionsData = $this->getOptionsData();
        if (!count($optionsData)) {
            return $html;
        }

        $block = $this->blockFactory->createBlock(\Magento\Framework\View\Element\Template::class)
            ->setData('options_data', $optionsData)
            ->setTemplate('Amasty_ShopbyBase::attribute/icon.phtml');
        $html = $block->toHtml();

        return $html;
    }

    /**
     * @return array
     */
    private function getOptionsData()
    {
        $data = [];

        $attributeValues = $this->getCurrentAttributeValues();
        if (!count($attributeValues)) {
            return $data;
        }

        $optionSettingCollection = $this->getOptionSettingByValues($attributeValues);
        foreach ($optionSettingCollection as $optionSetting) {
            /** @var OptionSetting $optionSetting */
            $data[$optionSetting->getValue()] = $this->getOptionSettingData($optionSetting);
        }

        return $data;
    }

    /**
     * @param array $attributeValues
     *
     * @return \Amasty\ShopbyBase\Model\ResourceModel\OptionSetting\Collection
     */
    private function getOptionSettingByValues($attributeValues)
    {
        $optionSettingCollection = $this->optionCollectionFactory->create()
            ->addTitleToCollection()
            ->addFieldToFilter('main_table.' . OptionSetting::VALUE, ['in' => $attributeValues])
            ->addFieldToFilter(
                [OptionSetting::SLIDER_IMAGE, OptionSetting::IMAGE, OptionSetting::SMALL_IMAGE_ALT],
                [['neq' => ''], ['neq' => ''], ['neq' => '']]
            )
            ->addFieldToFilter(
                'main_table.' . OptionSettingInterface::STORE_ID,
                [$this->storeManager->getStore()->getId(), \Magento\Store\Model\Store::DEFAULT_STORE_ID]
            );

        //default_store options will be rewritten with current_store ones.
        $optionSettingCollection->getSelect()->order(['filter_code ASC', 'main_table.store_id ASC']);

        return $optionSettingCollection;
    }

    /**
     * @return array
     */
    private function getCurrentAttributeValues()
    {
        $attributeValues = [];
        $product = $this->registry->registry('current_product');
        if ($product) {
            $attributeValues = $this->getAttributeValues($product);
        }

        return $attributeValues;
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @return array
     */
    private function getAttributeValues(\Magento\Catalog\Model\Product $product)
    {
        $values = [];
        $attributeCodes = $this->getAttributeCodes();
        if (!count($attributeCodes)) {
            return $values;
        }

        foreach ($attributeCodes as $code) {
            $data = $product->getData($code);
            if (!$data && $product->getTypeId() === Configurable::TYPE_CODE) {
                /** @var \Magento\Catalog\Model\Product[] $childProducts */
                $childProducts = $this->configurableType->getUsedProducts($product);
                foreach ($childProducts as $childProduct) {
                    $childAttrValue = $childProduct->getData($code);
                    if ($childAttrValue) {
                        if (is_string($childAttrValue)) {
                            $childAttrValue = explode(',', $childAttrValue);
                        }
                        // phpcs:ignore
                        $values = array_merge($values, $childAttrValue);
                    }
                }
            } elseif ($data) {
                if (is_string($data)) {
                    $data = explode(',', $data);
                }
                // phpcs:ignore
                $values = array_merge($values, $data);
            }
        }

        return $values;
    }

    /**
     * @param OptionSetting $setting
     * @return array
     */
    private function getOptionSettingData(OptionSetting $setting)
    {
        $label = $setting->getAttributeOption()->getLabel();
        $title = $label ?: $setting->getTitle();
        $data = [
            self::IMAGE_URL => $this->getProductPageLogoUrl($setting),
            self::LINK_URL => $this->getOptionSettingUrl($setting),
            self::TITLE => $title,
            OptionSetting::SMALL_IMAGE_ALT => $setting->getSmallImageAlt()
        ];

        if ($this->isShowShortDescription()) {
            $data[self::SHORT_DESCRIPTION ] = $setting->getShortDescription();
        }

        if ($this->isToolTipEnabled()) {
            $data[self::TOOLTIP_JS] = $this->getTooltipTemplate([
                'title' => $title,
                'label' => $setting->getLabel(),
                'img' => $setting->getSliderImageUrl(),
                'image' => $setting->getImageUrl(),
                'description' => $setting->getDescription(true),
                'short_description' => $setting->getShortDescription(true),
            ]);
        }

        return $data;
    }

    /**
     * @param OptionSetting $setting
     * @return string
     */
    protected function getOptionSettingUrl(OptionSetting $setting)
    {
        return $setting->getUrlPath();
    }

    /**
     * @param OptionSetting $setting
     *
     * @return bool|string|null
     */
    private function getProductPageLogoUrl(OptionSetting $setting)
    {
        $url = $setting->getSliderImageUrl();
        $width = $this->getProductPageWidth();
        $height = $this->getProductPageHeight();
        /** @var \Amasty\ShopbyBase\Model\Resizer $resizer */
        $resizer = $this->getResizer();
        if ($width && $height && $resizer && $url) {
            $url = $resizer->getImageUrl($url, $width, $height);
        }

        return $url;
    }

    /**
     * @return int
     */
    protected function getProductPageWidth()
    {
        return 0;
    }

    /**
     * @return int
     */
    protected function getProductPageHeight()
    {
        return 0;
    }

    /**
     * @return bool
     */
    protected function isShowShortDescription()
    {
        return false;
    }

    /**
     * @return bool
     */
    protected function isShowLogo()
    {
        return false;
    }

    /**
     * @return bool
     */
    protected function isToolTipEnabled()
    {
        return false;
    }

    /**
     * @return string
     */
    protected function getTooltipTemplate(array $item)
    {
        return '';
    }

    /**
     * @return \Amasty\ShopbyBase\Model\Resizer
     */
    protected function getResizer()
    {
        if (isset($this->data['resizer'])) {
            return $this->data['resizer'];
        }

        return null;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param array $data
     */
    public function setData($data)
    {
        $this->data = $data;
    }
}

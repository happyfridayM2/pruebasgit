<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_ShopbyBrand
 */


namespace Amasty\ShopbyBrand\Block\Widget;

use Amasty\ShopbyBase\Api\Data\OptionSettingInterface;
use Amasty\ShopbyBrand\Model\Source\Tooltip;
use Magento\Catalog\Model\Product\Attribute\Repository;
use Magento\Framework\View\Element\Template as Template;
use Magento\Framework\View\Element\Template\Context;
use \Magento\Eav\Model\Entity\Attribute\Option;
use Amasty\ShopbyBrand\Helper\Data as DataHelper;
use Amasty\ShopbyBase\Model\ResourceModel\OptionSetting\CollectionFactory as OptionSettingCollectionFactory;
use Magento\Framework\App\Request\DataPersistorInterface;

class BrandList extends BrandListAbstract implements \Magento\Widget\Block\BlockInterface
{
    const CONFIG_VALUES_PATH = 'amshopby_brand/brands_landing';

    /**
     * @var bool
     */
    protected $isDisplayZero;

    /**
     * @var DataHelper
     */
    protected $brandHelper;

    /**
     * used for BrandsPopup
     *
     * @var DataPersistorInterface
     */
    protected $dataPersistor;

    public function __construct(
        Context $context,
        Repository $repository,
        \Amasty\ShopbyBase\Model\OptionSettingFactory $optionSettingFactory,
        OptionSettingCollectionFactory $optionSettingCollectionFactory,
        \Magento\CatalogSearch\Model\Layer\Category\ItemCollectionProvider $collectionProvider,
        \Magento\Catalog\Model\Product\Url $productUrl,
        \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository,
        DataHelper $dataHelper,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Amasty\ShopbyBase\Api\UrlBuilderInterface $amUrlBuilder,
        \Amasty\ShopbyBrand\Helper\Data $brandHelper,
        \Amasty\ShopbyBrand\Model\ProductCount $productCount,
        DataPersistorInterface $dataPersistor,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $repository,
            $optionSettingFactory,
            $optionSettingCollectionFactory,
            $collectionProvider,
            $productUrl,
            $categoryRepository,
            $dataHelper,
            $messageManager,
            $amUrlBuilder,
            $productCount,
            $data
        );

        $this->brandHelper = $brandHelper;
        $this->dataPersistor = $dataPersistor;
    }

    /**
     * @return array
     */
    public function getIndex()
    {
        $items = $this->getItems();
        if (!$items) {
            return [];
        }

        $letters = $this->sortByLetters($items);
        $index = $this->breakByColumns($letters);

        return $index;
    }

    /**
     * @param array $items
     *
     * @return array
     */
    private function sortByLetters($items)
    {
        $this->sortItems($items);
        $letters = $this->items2letters($items);

        return $letters;
    }

    /**
     * @param array $letters
     *
     * @return array
     */
    private function breakByColumns($letters)
    {
        $columnCount = abs((int)$this->getData('columns'));
        if (!$columnCount) {
            $columnCount = 1;
        }

        $row = 0; // current row
        $num = 0; // current number of items in row
        $index = [];
        foreach ($letters as $letter => $items) {
            $index[$row][$letter] = $items['items'];
            $num++;
            if ($num >= $columnCount) {
                $num = 0;
                $row++;
            }
        }

        return $index;
    }

    /**
     * @param \Magento\Eav\Model\Entity\Attribute\Option $option
     * @param OptionSettingInterface $setting
     * @return array
     */
    protected function getItemData(Option $option, OptionSettingInterface $setting)
    {
        $count = $this->_getOptionProductCount($setting->getValue());
        if ($this->isDisplayZero() || $count) {
            $result = [
                'brandId' => $option->getData('value'),
                'label' => $setting->getLabel() ?: $option->getLabel(),
                'url' => $this->getBrandUrl($option),
                'img' => $setting->getSliderImageUrl(),
                'image' => $setting->getImageUrl(),
                'description' => $setting->getDescription(true),
                'short_description' => $setting->getShortDescription(),
                'cnt' => $count,
                'alt' => $setting->getSmallImageAlt() ?: $setting->getLabel()
            ];
        }

        return $result ?? [];
    }

    /**
     * @return bool
     */
    protected function isDisplayZero()
    {
        if ($this->isDisplayZero === null) {
            $this->isDisplayZero = (bool)$this->helper->isDisplayZero();
        }

        return $this->isDisplayZero;
    }

    /**
     * Get brand product count
     *
     * @param int $optionId
     * @return int
     */
    protected function _getOptionProductCount($optionId)
    {
        if ($this->getData('show_count') || !$this->isDisplayZero()) {
            return parent::_getOptionProductCount($optionId);
        }

        return 0;
    }

    /**
     * @param array $items
     */
    protected function sortItems(array &$items)
    {
        usort($items, [$this, '_sortByName']);
    }

    /**
     * @param array $items
     * @return array
     */
    protected function items2letters($items)
    {
        $letters = [];
        foreach ($items as $item) {
            $letter = $this->getLetter($item['label']);
            if (!isset($letters[$letter]['items'])) {
                $letters[$letter]['items'] = [];
            }

            $letters[$letter]['items'][] = $item;
            if (!isset($letters[$letter]['count'])) {
                $letters[$letter]['count'] = 0;
            }

            $letters[$letter]['count']++;
        }

        return $letters;
    }

    /**
     * @param $item
     * @return false|mixed|string|string[]|null
     */
    public function getLetter($label)
    {
        if (function_exists('mb_strtoupper')) {
            $letter = mb_strtoupper(mb_substr($label, 0, 1, 'UTF-8'));
        } else {
            $letter = strtoupper(substr($label, 0, 1));
        }

        if (is_numeric($letter)) {
            $letter = '#';
        }

        return $letter;
    }

    /**
     * @return array
     */
    public function getAllLetters()
    {
        $brandLetters = [];
        /** @codingStandardsIgnoreStart */
        foreach ($this->getIndex() as $letters) {
            $brandLetters = array_merge($brandLetters, array_keys($letters));
        }
        /** @codingStandardsIgnoreEnd */

        return $brandLetters;
    }

    /**
     * @return string
     */
    public function getSearchHtml()
    {
        $html = '';
        if (!$this->getData('show_search') || !$this->getItems()) {
            return $html;
        }

        $searchCollection = [];
        foreach ($this->getItems() as $item) {
            $searchCollection[$item['url']] = $item['label'];
        }

        /** @var Template $block */
        $block = $this->getSearchBrandBlock();
        if ($block) {
            $searchCollection = json_encode($searchCollection);
            $block->setBrands($searchCollection);
            $html = $block->toHtml();
        }

        return $html;
    }

    /**
     * @return bool|\Magento\Framework\View\Element\BlockInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function getSearchBrandBlock()
    {
        $block = $this->getLayout()->getBlock('ambrands.search');
        if (!$block) {
            $block = $this->getLayout()->createBlock(Template::class, 'ambrands.search')
                ->setTemplate('Amasty_ShopbyBrand::brand_search.phtml');
        }

        return $block;
    }

    /**
     * @return bool
     */
    public function isTooltipEnabled()
    {
        $setting = $this->brandHelper->getModuleConfig('general/tooltip_enabled');

        return in_array(Tooltip::ALL_BRAND_PAGE, explode(',', $setting));
    }

    /**
     * @param array $item
     * @return string
     */
    public function getTooltipAttribute(array $item)
    {
        $result = '';
        if ($this->isTooltipEnabled()) {
            $result = $this->brandHelper->generateToolTipContent($item);
        }

        return $result;
    }

    /**
     * @return string
     */
    protected function getConfigValuesPath()
    {
        return self::CONFIG_VALUES_PATH;
    }

    /**
     * @return int
     */
    public function getImageWidth()
    {
        return abs($this->getData('image_width')) ?: 100;
    }

    /**
     * @return int
     */
    public function getImageHeight()
    {
        return abs($this->getData('image_height')) ?: 50;
    }
}

<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_ShopbyBrand
 */


namespace Amasty\ShopbyBrand\Block\Widget;

use Amasty\ShopbyBase\Model\ResourceModel\OptionSetting\CollectionFactory as OptionSettingCollectionFactory;
use Amasty\ShopbyBrand\Helper\Data;
use Magento\Catalog\Model\Product\Attribute\Repository;
use Magento\Framework\View\Element\Template\Context;
use Amasty\ShopbyBase\Helper\OptionSetting as OptionSettingHelper;
use Amasty\ShopbyBase\Api\Data\OptionSettingInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Eav\Model\Entity\Attribute\Option;
use Amasty\ShopbyBrand\Helper\Data as DataHelper;
use \Magento\Framework\Exception\StateException;

/**
 * Class BrandListAbstract
 *
 * @package Amasty\ShopbyBrand\Block\Widget
 */
abstract class BrandListAbstract extends \Magento\Framework\View\Element\Template
{
    const PATH_BRAND_ATTRIBUTE_CODE = 'amshopby_brand/general/attribute_code';

    /**
     * @var  array|null
     */
    protected $items;

    /**
     * @var  Repository
     */
    protected $repository;

    /**
     * @var \Magento\CatalogSearch\Model\Layer\Category\ItemCollectionProvider
     */
    protected $collectionProvider;

    /**
     * @var \Magento\Catalog\Model\Product\Url
     */
    protected $productUrl;

    /**
     * @var \Amasty\ShopbyBrand\Model\ProductCount
     */
    protected $productCount;

    /**
     * @var \Magento\Catalog\Api\CategoryRepositoryInterface
     */
    protected $categoryRepository;

    /**
     * @var DataHelper
     */
    protected $helper;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @var OptionSettingCollectionFactory
     */
    private $optionSettingCollectionFactory;

    /**
     * @var \Amasty\ShopbyBase\Model\OptionSettingFactory
     */
    private $optionSettingFactory;

    /**
     * @var array
     */
    private $settingByValue = [];

    /**
     * @var \Amasty\ShopbyBase\Api\UrlBuilderInterface
     */
    private $amUrlBuilder;

    public function __construct(
        Context $context,
        Repository $repository,
        \Amasty\ShopbyBase\Model\OptionSettingFactory $optionSettingFactory,
        OptionSettingCollectionFactory $optionSettingCollectionFactory,
        \Magento\CatalogSearch\Model\Layer\Category\ItemCollectionProvider $collectionProvider,
        \Magento\Catalog\Model\Product\Url $productUrl,
        \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository,
        DataHelper $helper,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Amasty\ShopbyBase\Api\UrlBuilderInterface $amUrlBuilder,
        \Amasty\ShopbyBrand\Model\ProductCount $productCount,
        array $data = []
    ) {
        $this->repository = $repository;
        $this->collectionProvider = $collectionProvider;
        $this->productUrl = $productUrl;
        $this->categoryRepository = $categoryRepository;
        $this->helper = $helper;
        $this->messageManager = $messageManager;
        $this->optionSettingCollectionFactory = $optionSettingCollectionFactory;
        $this->optionSettingFactory = $optionSettingFactory;
        $this->amUrlBuilder = $amUrlBuilder;
        $this->productCount = $productCount;
        parent::__construct($context, $data);
    }

    /**
     * @return array
     */
    public function getItems()
    {
        if ($this->items === null) {
            $this->items = [];
            $attributeCode = $this->helper->getBrandAttributeCode();
            if (!$attributeCode) {
                return $this->items;
            }

            $options = $this->repository->get($attributeCode)->getOptions();
            array_shift($options);

            foreach ($options as $option) {
                $setting = $this->getBrandOptionSettingByValue($option->getValue());
                $data = $this->getItemData($option, $setting);
                if ($data) {
                    $this->items[] = $data;
                }
            }

        }

        return $this->items;
    }

    /**
     * @param int $value
     * @return OptionSettingInterface
     */
    private function getBrandOptionSettingByValue($value)
    {
        if (empty($this->settingByValue)) {
            $filterCode = \Amasty\ShopbyBase\Helper\FilterSetting::ATTR_PREFIX .
                $this->helper->getBrandAttributeCode();

            $stores = [0,  $this->_storeManager->getStore()->getId()];
            $collection = $this->optionSettingCollectionFactory->create()
                ->addFieldToFilter('store_id', $stores)
                ->addFieldToFilter('filter_code', $filterCode)
                ->addOrder('store_id', 'ASC'); //current store values will rewrite defaults
            foreach ($collection as $item) {
                $this->settingByValue[$item->getValue()] = $item;
            }
        }
        
        return isset($this->settingByValue[$value])
            ? $this->settingByValue[$value] : $this->optionSettingFactory->create() ;
    }
    
    /**
     * @param \Magento\Eav\Model\Entity\Attribute\Option $option
     * @param \Amasty\ShopbyBase\Api\Data\OptionSettingInterface $setting
     * @return array
     */
    abstract protected function getItemData(Option $option, OptionSettingInterface $setting);

    /**
     * @param \Magento\Eav\Model\Entity\Attribute\Option $option
     * @return string
     */
    public function getBrandUrl(Option $option)
    {
        return $this->amUrlBuilder->getUrl('ambrand/index/index', ['id' => $option->getValue()]);
    }

    /**
     * @param array $a
     * @param array $b
     * @return int
     */
    protected function _sortByName($a, $b)
    {
        $a['label'] = trim($a['label']);
        $b['label'] = trim($b['label']);

        if ($a == '') {
            return 1;
        }
        if ($b == '') {
            return -1;
        }

        $x = substr($a['label'], 0, 1);
        $y = substr($b['label'], 0, 1);
        if (is_numeric($x) && !is_numeric($y)) {
            return 1;
        }
        if (!is_numeric($x) && is_numeric($y)) {
            return -1;
        }

        if (function_exists('mb_strtoupper')) {
            $res = strcmp(mb_strtoupper($a['label']), mb_strtoupper($b['label']));
        } else {
            $res = strcmp(strtoupper($a['label']), strtoupper($b['label']));
        }
        return $res;
    }

    /**
     * Apply options from config
     * @return $this
     */
    protected function _beforeToHtml()
    {
        $configValues = $this->_scopeConfig->getValue(
            $this->getConfigValuesPath(),
            ScopeInterface::SCOPE_STORE
        );
        foreach ($configValues as $option => $value) {
            if ($this->getData($option) === null) {
                $this->setData($option, $value);
            }
        }

        $this->applySorting();

        return parent::_beforeToHtml();
    }

    /**
     * Apply additional sorting before render html
     *
     * @return $this
     */
    protected function applySorting()
    {
        return $this;
    }

    /**
     * @return string
     */
    abstract protected function getConfigValuesPath();

    /**
     * Get brand product count
     *
     * @param int $optionId
     * @return int
     */
    protected function _getOptionProductCount($optionId)
    {
        return $this->productCount->get($optionId);
    }
}

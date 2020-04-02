<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_ShopbyBase
 */


namespace Amasty\ShopbyBase\Helper;

use Magento\Catalog\Model\Layer\Filter\FilterInterface;
use Magento\Framework\App\Helper\Context;
use Amasty\ShopbyBase\Api\Data\FilterSettingInterface;
use Magento\Store\Model\ScopeInterface;
use Amasty\ShopbyBase\Model\ResourceModel\FilterSetting\CollectionExtendedFactory;

/**
 * Class FilterSetting
 */
class FilterSetting extends \Magento\Framework\App\Helper\AbstractHelper
{
    const ATTR_PREFIX = 'attr_';

    /**
     * @var  \Amasty\ShopbyBase\Model\FilterSettingFactory
     */
    protected $settingFactory;

    /**
     * @var \Amasty\ShopbyBase\Model\FilterSettingRepository
     */
    private $settingRepository;

    /**
     * @var CollectionExtendedFactory
     */
    private $collectionExtendedFactory;

    public function __construct(
        Context $context,
        CollectionExtendedFactory $collectionExtendedFactory,
        \Amasty\ShopbyBase\Model\FilterSettingFactory $settingFactory,
        \Amasty\ShopbyBase\Model\FilterSettingRepository $settingRepository
    ) {
        parent::__construct($context);
        $this->settingFactory = $settingFactory;
        $this->settingRepository = $settingRepository;
        $this->collectionExtendedFactory = $collectionExtendedFactory;
    }

    /**
     * @param FilterInterface $layerFilter
     * @return \Amasty\ShopbyBase\Api\Data\FilterSettingInterface
     */
    public function getSettingByLayerFilter(FilterInterface $layerFilter)
    {
        $filterCode = $this->getFilterCode($layerFilter);
        $setting = $this->getFilterSettingByCode($filterCode);
        if ($setting === null) {
            $setting = $this->settingFactory->create(
                ['data' => [FilterSettingInterface::FILTER_CODE => $filterCode]]
            );
        }

        $setting->setAttributeModel($layerFilter->getData('attribute_model'));

        return $setting;
    }

    /**
     * @param \Magento\Eav\Model\Entity\Attribute $attributeModel
     *
     * @return \Amasty\ShopbyBase\Api\Data\FilterSettingInterface
     */
    public function getSettingByAttribute($attributeModel)
    {
        return $this->collectionExtendedFactory->get()->getItemByAttribute($attributeModel);
    }

    /**
     * @param string $attributeCode
     *
     * @return \Amasty\ShopbyBase\Api\Data\FilterSettingInterface
     */
    public function getSettingByAttributeCode($attributeCode)
    {
        $filterCode = self::ATTR_PREFIX . $attributeCode;
        $setting = $this->getFilterSettingByCode($filterCode);

        return $setting;
    }

    /**
     * @param FilterInterface $layerFilter
     * @return null|string
     */
    protected function getFilterCode(FilterInterface $layerFilter)
    {
        $attribute = $layerFilter->getData('attribute_model');
        if (!$attribute) {
            $categorySetting = $layerFilter->getSetting();

            return is_object($categorySetting) ? $categorySetting->getFilterCode() : null;
        }

        return is_object($attribute) ? self::ATTR_PREFIX . $attribute->getAttributeCode() : null;
    }

    /**
     * @param string $filterName
     * @param string $configName
     * @return string
     */
    public function getConfig($filterName, $configName)
    {
        return $this->scopeConfig->getValue(
            'amshopby/' . $filterName . '_filter/' . $configName,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return array
     */
    public function getCustomDataForCategoryFilter()
    {
        $data = [];
        foreach ($this->getKeyValueForCategoryFilterConfig() as $key => $value) {
            $data[$key] = $this->scopeConfig->getValue($value, ScopeInterface::SCOPE_WEBSITES);
        }

        return $data;
    }

    /**
     * @return array
     */
    public function getKeyValueForCategoryFilterConfig()
    {
        return [
            'category_tree_depth' => 'amshopby/category_filter/category_tree_depth',
            'subcategories_view' => 'amshopby/category_filter/subcategories_view',
            'subcategories_expand' => 'amshopby/category_filter/subcategories_expand',
            'render_all_categories_tree' => 'amshopby/category_filter/render_all_categories_tree',
            'render_categories_level' => 'amshopby/category_filter/render_categories_level',
        ];
    }

    /**
     * @param string|null $code
     * @return \Amasty\ShopbyBase\Model\FilterSetting|null
     */
    protected function getFilterSettingByCode($code)
    {
        return $this->collectionExtendedFactory->get()->getItemByCode($code);
    }
}

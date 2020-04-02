<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */


namespace Amasty\Shopby\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Catalog\Model\Layer\Filter\FilterInterface;
use Amasty\ShopbyBase\Model\ResourceModel\FilterSetting\CollectionExtendedFactory;
use Magento\Store\Model\ScopeInterface;

/**
 * Class FilterSetting
 * @package Amasty\Shopby\Helper
 */
class FilterSetting extends \Amasty\ShopbyBase\Helper\FilterSetting
{
    /**
     * @var \Magento\Framework\View\Element\BlockFactory
     */
    private $blockFactory;

    public function __construct(
        Context $context,
        CollectionExtendedFactory $collectionExtendedFactory,
        \Amasty\ShopbyBase\Model\FilterSettingFactory $settingFactory,
        \Amasty\ShopbyBase\Model\FilterSettingRepository $settingRepository,
        \Magento\Framework\View\Element\BlockFactory $blockFactory
    ) {
        parent::__construct($context, $collectionExtendedFactory, $settingFactory, $settingRepository);
        $this->blockFactory = $blockFactory;
    }

    /**
     * @param FilterInterface $layerFilter
     * @return \Amasty\ShopbyBase\Api\Data\FilterSettingInterface
     */
    public function getSettingByLayerFilter(FilterInterface $layerFilter)
    {
        $filterCode = $this->getFilterCode($layerFilter);
        $setting =  $this->getFilterSettingByCode($filterCode);

        $setting->setAttributeModel($layerFilter->getData('attribute_model'));

        return $setting;
    }

    /**
     * @param $attributeCode
     *
     * @return \Amasty\ShopbyBase\Api\Data\FilterSettingInterface
     */
    public function getSettingByAttributeCode($attributeCode)
    {
        $filterCode = self::ATTR_PREFIX . $attributeCode;
        $setting =  $this->getFilterSettingByCode($filterCode);

        return $setting;
    }

    /**
     * @param FilterInterface $layerFilter
     * @return string|null
     */
    public function getFilterCode(FilterInterface $layerFilter)
    {
        $attribute = $layerFilter->getData('attribute_model');
        $filterCode = is_object($attribute) ? self::ATTR_PREFIX . $attribute->getAttributeCode() : null;

        if (!$filterCode) {
            if ($layerFilter instanceof \Amasty\Shopby\Model\Layer\Filter\Category) {
                $filterCode = self::ATTR_PREFIX . \Amasty\Shopby\Helper\Category::ATTRIBUTE_CODE;
            } elseif ($layerFilter instanceof \Amasty\Shopby\Model\Layer\Filter\Stock) {
                $filterCode = 'stock';
            } elseif ($layerFilter instanceof \Amasty\Shopby\Model\Layer\Filter\Rating) {
                $filterCode = 'rating';
            } elseif ($layerFilter instanceof \Amasty\Shopby\Model\Layer\Filter\IsNew) {
                $filterCode = 'am_is_new';
            } elseif ($layerFilter instanceof \Amasty\Shopby\Model\Layer\Filter\OnSale) {
                $filterCode = 'am_on_sale';
            }
        }

        return $filterCode;
    }

    /**
     * @return string
     */
    public function getShowMoreButtonBlock($setting)
    {
        return $this->blockFactory->createBlock(\Amasty\Shopby\Block\Navigation\Widget\HideMoreOptions::class)
            ->setFilterSetting($setting);
    }

    /**
     * @param string $path
     * @return bool
     */
    public function isSetConfig($path)
    {
        return $this->scopeConfig->isSetFlag(
            $path,
            ScopeInterface::SCOPE_STORE
        );
    }
}

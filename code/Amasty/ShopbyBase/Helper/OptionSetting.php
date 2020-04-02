<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_ShopbyBase
 */


namespace Amasty\ShopbyBase\Helper;

use Amasty\ShopbyBase\Api\Data\OptionSettingInterface;
use Amasty\ShopbyBase\Model\OptionSetting as OptionSettingModel;
use Magento\Catalog\Model\Product\Attribute\Repository;
use Magento\Eav\Api\Data\AttributeOptionInterface;
use Magento\Framework\App\Helper\Context;
use Amasty\ShopbyBase\Api\Data\OptionSettingRepositoryInterface;

/**
 * Class OptionSetting
 */
class OptionSetting extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var  Repository
     */
    private $repository;

    /**
     * @var \Amasty\ShopbyBase\Model\ResourceModel\OptionSetting
     */
    private $optionSettingResource;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var array
     */
    private $allAttributeOptions = [];

    /**
     * @var OptionSettingRepositoryInterface
     */
    private $optionSettingRepository;

    public function __construct(
        Context $context,
        \Amasty\ShopbyBase\Model\ResourceModel\OptionSetting $optionSettingResource,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        OptionSettingRepositoryInterface $optionSettingRepository,
        Repository $repository
    ) {
        parent::__construct($context);
        $this->optionSettingResource = $optionSettingResource;
        $this->storeManager = $storeManager;
        $this->repository = $repository;
        $this->optionSettingRepository = $optionSettingRepository;
    }

    /**
     * @param string $value
     * @param string $filterCode
     * @param int $storeId
     * @return OptionSettingInterface
     */
    public function getSettingByValue($value, $filterCode, $storeId)
    {
        /** @var OptionSettingModel $setting */
        $setting = $this->optionSettingRepository->getByParams($filterCode, $value, $storeId);

        if (!$setting->getId()) {
            $setting->setFilterCode($filterCode);
            $attribute = $this->getAttribute(substr($filterCode, 5), $storeId);
            $setting = $this->applyDataFromOption($attribute, $value, $setting);
        }

        return $setting;
    }

    /**
     * @param string $attributeCode
     * @param int $storeId
     *
     * @return \Magento\Catalog\Api\Data\ProductAttributeInterface|\Magento\Eav\Api\Data\AttributeInterface
     */
    public function getAttribute($attributeCode, $storeId)
    {
        $attribute = $this->repository->get($attributeCode);
        $attribute->setStoreId($storeId);

        return $attribute;
    }

    /**
     * @param $attribute
     * @param $value
     * @param OptionSettingInterface $setting
     *
     * @return OptionSettingInterface
     */
    public function applyDataFromOption($attribute, $value, OptionSettingInterface $setting)
    {
        foreach ($attribute->getOptions() as $option) {
            if ($option->getValue() == $value) {
                $this->initiateSettingByOption($setting, $option);
                break;
            }
        }

        return $setting;
    }

    /**
     * @param OptionSettingInterface $setting
     * @param AttributeOptionInterface $option
     * @return $this
     */
    protected function initiateSettingByOption(
        OptionSettingInterface $setting,
        AttributeOptionInterface $option
    ) {
        $setting->setValue($option->getValue());
        $setting->setTitle($option->getLabel());
        $setting->setMetaTitle($option->getLabel());
        return $this;
    }

    /**
     * @return array
     */
    public function getAllFeaturedOptionsArray()
    {
        if (empty($this->allAttributeOptions)) {
            $this->allAttributeOptions = $this->optionSettingResource
                ->getAllFeaturedOptionsArray($this->getCurrentStoreId());
        }

        return $this->allAttributeOptions;
    }

    /**
     * @return int
     */
    public function getCurrentStoreId()
    {
        return $this->storeManager->getStore()->getId();
    }
}

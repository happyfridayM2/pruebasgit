<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_ShopbyBase
 */


namespace Amasty\ShopbyBase\Model;

use Amasty\ShopbyBase\Api\Data\OptionSettingRepositoryInterface;
use Amasty\ShopbyBase\Api\Data\OptionSettingInterface;
use Amasty\ShopbyBase\Model\ResourceModel\OptionSetting as OptionSettingResource;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Option;

/**
 * OptionSettingRepository
 */
class OptionSettingRepository implements OptionSettingRepositoryInterface
{
    /**
     * @var OptionSettingResource
     */
    private $resource;

    /**
     * @var OptionSettingFactory
     */
    private $factory;

    /**
     * @var OptionSettingResource\CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var Option\CollectionFactory
     */
    private $optionCollectionFactory;

    public function __construct(
        OptionSettingResource $resource,
        OptionSettingFactory $factory,
        ResourceModel\OptionSetting\CollectionFactory $collectionFactory,
        Option\CollectionFactory $optionCollectionFactory
    ) {
        $this->resource = $resource;
        $this->factory = $factory;
        $this->collectionFactory = $collectionFactory;
        $this->optionCollectionFactory = $optionCollectionFactory;
    }

    /**
     * @param int $id
     * @return OptionSettingInterface
     * @throws NoSuchEntityException
     */
    public function get($id)
    {
        $entity = $this->factory->create();
        $this->resource->load($entity, $id);
        if (!$entity->getId()) {
            throw new NoSuchEntityException(__('Requested option setting doesn\'t exist'));
        }

        return $entity;
    }

    /**
     * @param string $filterCode
     * @param int $optionId
     * @param int $storeId
     * @return OptionSettingInterface
     */
    public function getByParams($filterCode, $optionId, $storeId)
    {
        $collection = $this->collectionFactory->create()->addLoadParams($filterCode, $optionId, $storeId);
        $eavValue = $collection->getValueFromMagentoEav($optionId, $storeId);

        /** @var OptionSettingInterface $model */
        $model = $collection->getFirstItem();
        if ($storeId !== 0) {
            $defaultModel = $collection->getLastItem();
            foreach ($model->getData() as $key => $value) {
                $defaultValue = $defaultModel->getData($key);
                $isDefault = $defaultValue == $value ? true : false;

                if (in_array($key, ['meta_title', 'title'])) {
                    $isDefault = !$value || $eavValue == $value ? true : false;
                    if ($isDefault) {
                        $model->setData($key, $defaultModel->getData($key) ?: $eavValue);
                    }
                }

                $model->setData($key . '_use_default', $isDefault);
            }
        } else {
            foreach (['meta_title', 'title'] as $key) {
                $model->setData($key . '_use_default', false);
                $value = $model->getData($key);
                if (!$value || $model->getData($key) == $eavValue) {
                    $model->setData($key . '_use_default', true);
                    $model->setData($key, $eavValue);
                }
            }
        }

        return $model;
    }

    /**
     * @param OptionSettingInterface $optionSetting
     * @return $this
     */
    public function save(OptionSettingInterface $optionSetting)
    {
        $this->resource->save($optionSetting);
        return $this;
    }

    /**
     * @param int $storeId
     * @return array
     */
    public function getAllFeaturedOptionsArray($storeId)
    {
        return $this->resource->getAllFeaturedOptionsArray($storeId);
    }
}

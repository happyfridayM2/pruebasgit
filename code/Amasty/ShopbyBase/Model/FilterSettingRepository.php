<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_ShopbyBase
 */


namespace Amasty\ShopbyBase\Model;

use Amasty\ShopbyBase\Api\Data\FilterSettingRepositoryInterface;
use Amasty\ShopbyBase\Api\Data\FilterSettingInterface;
use Amasty\ShopbyBase\Model\ResourceModel\FilterSetting as FilterSettingResource;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class FilterSettingRepository
 * @package Amasty\ShopbyBase\Model
 */
class FilterSettingRepository implements FilterSettingRepositoryInterface
{
    /**
     * @var FilterSettingResource
     */
    private $resource;

    /**
     * @var FilterSettingFactory
     */
    private $factory;

    public function __construct(
        FilterSettingResource $resource,
        FilterSettingFactory $factory
    ) {
        $this->resource = $resource;
        $this->factory = $factory;
    }

    /**
     * @param int $id
     * @param null $idFieldName
     * @return FilterSettingInterface
     * @throws NoSuchEntityException
     */
    public function get($id, $idFieldName = null)
    {
        $entity = $this->factory->create();
        $this->resource->load($entity, $id, $idFieldName);
        if (!$entity->getId()) {
            throw new NoSuchEntityException(__('Requested filter setting doesn\'t exist'));
        }
        return $entity;
    }

    /**
     * @param FilterSettingInterface $FilterSetting
     * @return $this
     */
    public function save(FilterSettingInterface $FilterSetting)
    {
        $this->resource->save($FilterSetting);
        return $this;
    }
}

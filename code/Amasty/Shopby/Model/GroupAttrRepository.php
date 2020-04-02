<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */


namespace Amasty\Shopby\Model;

use Amasty\Shopby\Api\Data\GroupAttrInterface;
use Amasty\Shopby\Model\GroupAttrFactory;
use Amasty\Shopby\Api\Data\GroupAttrRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class GroupAttrRepository
 * @package Amasty\Shopby\Model
 */
class GroupAttrRepository implements GroupAttrRepositoryInterface
{
    /**
     * @var ResourceModel\GroupAttr
     */
    private $resource;

    /**
     * @var GroupAttrInterface
     */
    private $factory;

    /**
     * AbstractGiftCardEntityRepository constructor.
     * @param ResourceModel\GroupAttr $resource
     * @param GroupAttrFactory $factory
     */
    public function __construct(
        ResourceModel\GroupAttr $resource,
        GroupAttrFactory $factory
    ) {
        $this->resource = $resource;
        $this->factory = $factory;
    }

    /**
     * @param int $id
     * @return GroupAttrInterface
     * @throws NoSuchEntityException
     */
    public function get($id)
    {
        $entity = $this->factory->create();
        $this->resource->load($entity, $id);
        if (!$entity->getId()) {
            throw new NoSuchEntityException(__('Requested attribute group doesn\'t exist'));
        }
        return $entity;
    }

    /**
     * @param GroupAttrInterface $entity
     * @return $this
     */
    public function save(GroupAttrInterface $entity)
    {
        $this->resource->save($entity);
        return $this;
    }

    /**
     * @param GroupAttrInterface $entity
     * @return $this
     */
    public function delete(GroupAttrInterface $entity)
    {
        $this->resource->delete($entity);
        return $this;
    }
}

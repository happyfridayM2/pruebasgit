<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */


namespace Amasty\Shopby\Api\Data;

use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Interface GroupAttrRepositoryInterface
 * @package Amasty\Shopby\Api\Data
 */
interface GroupAttrRepositoryInterface
{
    /**
     * @param int $id
     * @return GroupAttrInterface
     * @throws NoSuchEntityException
     */
    public function get($id);

    /**
     * @param GroupAttrInterface $entity
     * @return $this
     */
    public function save(GroupAttrInterface $entity);

    /**
     * @param GroupAttrInterface $entity
     * @return $this
     */
    public function delete(GroupAttrInterface $entity);
}

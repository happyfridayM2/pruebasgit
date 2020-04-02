<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Conf
 */


namespace Amasty\Conf\Api;

/**
 * Interface GroupAttrRepositoryInterface
 * @api
 */
interface GroupAttrRepositoryInterface
{
    /**
     * @param \Amasty\Conf\Api\Data\GroupAttrInterface $group
     * @return \Amasty\Conf\Api\Data\GroupAttrInterface
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function save(\Amasty\Conf\Api\Data\GroupAttrInterface $group);

    /**
     * @param int $groupId
     * @return \Amasty\Conf\Api\Data\GroupAttrInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function get($groupId);

    /**
     * @param \Amasty\Conf\Api\Data\GroupAttrInterface $group
     * @return bool
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function delete(\Amasty\Conf\Api\Data\GroupAttrInterface $group);

    /**
     * @param int $groupId
     * @return bool
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function deleteById($groupId);
}

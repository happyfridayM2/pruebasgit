<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Conf
 */


namespace Amasty\Conf\Api;

/**
 * Interface GroupAttrOptionRepositoryInterface
 * @api
 */
interface GroupAttrOptionRepositoryInterface
{
    /**
     * @param \Amasty\Conf\Api\Data\GroupAttrOptionInterface $group
     * @return \Amasty\Conf\Api\Data\GroupAttrOptionInterface
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function save(\Amasty\Conf\Api\Data\GroupAttrOptionInterface $group);

    /**
     * @param int $optionId
     * @return \Amasty\Conf\Api\Data\GroupAttrOptionInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getByOption($optionId);

    /**
     * @param \Amasty\Conf\Api\Data\GroupAttrOptionInterface $group
     * @return bool
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function delete(\Amasty\Conf\Api\Data\GroupAttrOptionInterface $group);

    /**
     * @param int $groupId
     * @return bool
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function deleteById($groupId);
}

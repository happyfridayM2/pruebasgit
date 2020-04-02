<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_PageSpeedOptimizer
 */


namespace Amasty\PageSpeedOptimizer\Api;

interface ImageSettingRepositoryInterface
{
    /**
     * @param int $imageSettingId
     *
     * @return \Amasty\PageSpeedOptimizer\Api\Data\ImageSettingInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($imageSettingId);

    /**
     * @param \Amasty\PageSpeedOptimizer\Api\Data\ImageSettingInterface $imageSetting
     *
     * @return \Amasty\PageSpeedOptimizer\Api\Data\ImageSettingInterface
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function save(\Amasty\PageSpeedOptimizer\Api\Data\ImageSettingInterface $imageSetting);

    /**
     * @param \Amasty\PageSpeedOptimizer\Api\Data\ImageSettingInterface $imageSetting
     *
     * @return bool true on success
     */
    public function delete(\Amasty\PageSpeedOptimizer\Api\Data\ImageSettingInterface $imageSetting);

    /**
     * @param int $imageSettingId
     *
     * @return bool true on success
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function deleteById($imageSettingId);

    /**
     * @return \Amasty\PageSpeedOptimizer\Api\Data\ImageSettingInterface
     */
    public function getEmptyImageSettingModel();

    /**
     * @return \Amasty\PageSpeedOptimizer\Model\Image\ResourceModel\Collection
     */
    public function getImageSettingCollection();
}

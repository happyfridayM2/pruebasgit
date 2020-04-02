<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_ShopbyBase
 */


namespace Amasty\ShopbyBase\Api\Data;

use Magento\Framework\Exception\NoSuchEntityException;

interface OptionSettingRepositoryInterface
{
    /**
     * @param int $id
     * @return OptionSettingInterface
     * @throws NoSuchEntityException
     */
    public function get($id);

    /**
     * @param string $filterCode
     * @param int $optionId
     * @param int $storeId
     * @return OptionSettingInterface
     */
    public function getByParams($filterCode, $optionId, $storeId);

    /**
     * @param OptionSettingInterface $optionSetting
     * @return OptionSettingRepositoryInterface
     */
    public function save(OptionSettingInterface $optionSetting);

    /**
     * @param int $storeId
     * @return array
     */
    public function getAllFeaturedOptionsArray($storeId);
}

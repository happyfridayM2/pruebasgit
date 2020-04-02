<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_ShopbyBase
 */


namespace Amasty\ShopbyBase\Api\Data;

use Magento\Framework\Exception\NoSuchEntityException;

interface FilterSettingRepositoryInterface
{
    /**
     * @param int $id
     * @param null $idFieldName
     * @return FilterSettingInterface
     * @throws NoSuchEntityException
     */
    public function get($id, $idFieldName = null);

    /**
     * @param FilterSettingInterface $filterSetting
     * @return FilterSettingRepositoryInterface
     */
    public function save(FilterSettingInterface $filterSetting);
}

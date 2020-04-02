<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_ShopbyBase
 */


namespace Amasty\ShopbyBase\Api;

interface GroupAttributeCollectionProvider
{
    /**
     * @return \Amasty\Shopby\Model\ResourceModel\GroupAttr\Collection
     */
    public function getCollection();
}

<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_ShopbyBase
 */


namespace Amasty\ShopbyBase\Model\FilterSetting\AttributeConfig;

interface AttributeListProvider
{
    /**
     * Getting list of attribute codes, which can be configured with Amasty Attribute Settings
     * @return array
     */
    public function getAttributeList();
}

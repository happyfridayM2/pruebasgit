<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_ShopbyBase
 */


namespace Amasty\ShopbyBase\Api;

interface GroupAttributeDataFactoryProvider
{
    /**
     * @param array $data
     * @return mixed
     */
    public function create(array $data = []);
}

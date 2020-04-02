<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_ShopbyBase
 */


namespace Amasty\ShopbyBase\Api\Group\Attribute;

interface DataFactoryProviderInterface
{
    /**
     * @param array $data
     * @return mixed
     */
    public function create(array $data = []);
}

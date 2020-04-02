<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_ShopbyBase
 */


namespace Amasty\ShopbyBase\Api;

interface UrlModifierInterface
{
    /**
     * @param string $url
     * @return string
     */
    public function modifyUrl($url);
}

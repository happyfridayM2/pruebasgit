<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_ShopbyBase
 */


namespace Amasty\ShopbyBase\Model\Integration;

use Magento\Framework\Exception\LocalizedException;

/**
 * Class DummyObject
 */
class DummyObject
{
    /**
     * @param string $method
     * @param array $args
     * @return null
     * @throws LocalizedException
     */
    public function __call($method, $args)
    {
        if (substr($method, 0, 3) === 'get') {
            return null;
        }

        throw new LocalizedException(
            __(
                'Requested Improved Navigation submodule is disabled. Only read methods is allowed.'
            )
        );
    }
}

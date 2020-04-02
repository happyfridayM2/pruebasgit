<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_ShopbySeo
 */


namespace Amasty\ShopbySeo\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;

/**
 * Class Config
 */
class Config extends AbstractHelper
{
    const MODULE_PATH = 'amasty_shopby_seo/';

    /**
     * @param $path
     * @param int $storeId
     *
     * @return mixed
     */
    public function getModuleConfig($path, $storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::MODULE_PATH . $path,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @return bool
     */
    public function isSeoUrlEnabled()
    {
        return (bool)$this->getModuleConfig('url/mode');
    }

    /**
     * @return string
     */
    public function getOptionSeparator()
    {
        return $this->getModuleConfig('url/option_separator');
    }
}

<?php
/**
 * @author Edgar J. Gómez
 * @copyright Copyright (c) 2017 Edgargomez (http://www.edgargomez.es)
 * @package Edgargomez_CookieMsg
 */
namespace Edgargomez\CookieMsg\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    public function getConfig($config_path)
    {
        return $this->scopeConfig->getValue($config_path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
}

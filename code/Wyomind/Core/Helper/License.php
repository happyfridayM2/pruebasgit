<?php

/**
 * Copyright © 2018 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\Core\Helper;

class License extends \Magento\Framework\App\Helper\AbstractHelper
{

    /**
     * Soap url for the license server
     */
    const SOAP_URL = "https://www.wyomind.com/service/license/soap";

    /**
     * Soap uri for the license server
     */
    const SOAP_URI = "https://www.wyomind.com/";

    /**
     * Webservice url for the license server
     */


    /**
     * @var array
     */
    protected $_messages = [
        "ws_error" => "The Wyomind's license server encountered an error. Please contact us.",
        "ws_success" => "%s",
        "ws_failure" => "%s",
        "pending" => "<span style='background-color:orange; color:white; padding:2px 5px'>Your license is not yet registered</span> please run  <i>bin/magento wyomind:license:activate %s &lt;your activation key&gt;</i>",
        "upgrade" => "<span  style='background-color:'orange; color:white; padding:2px 5px'>Extension upgrade from v%s to v%s</span> your license must be updated, please run <i>bin/magento wyomind:license:activate %s %s</i>",
        "invalidated" => "<span  style='background-color:red; color:white; padding:2px 5px'>Your license is invalidated</span> please run <i>bin/magento wyomind:license:activate %s %s</i>",
    ];
    public $modulesList = null;

    public function __construct(
        \Magento\Framework\Module\ModuleListFactory $moduleListFactory)
    {

        $this->modulesList = $moduleListFactory->create();
    }

    /**
     * Print array
     * @param string $format
     * @param array $arr
     * @return string
     */
    public function sprintfArray(
        $format,
        $arr
    )
    {

        return call_user_func_array("sprintf", array_merge((array)$this->_messages[$format], $arr));
    }

    public function getModulesList()
    {
        $list = $this->modulesList->getAll();
        $list = array_filter($list, function ($key) {
            return strpos($key, "Wyomind_") === 0 && $key !== "Wyomind_Core";
        }, ARRAY_FILTER_USE_KEY);
        return $list;
    }

    public function getCoreVersion()
    {
        return $this->modulesList->getOne("Wyomind_Core")['setup_version'];
    }

}

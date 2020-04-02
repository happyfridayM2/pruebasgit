<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Copyright © 2019 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\OrdersExportTool\Helper;

/**
 * Class Config
 * @package Wyomind\OrdersExportTool\Helper
 */
class Config extends \Magento\Framework\App\Helper\AbstractHelper
{


    /**
     *
     */
    const SETTINGS_LOG = "ordersexporttool/advanced/enable_log";


    /**
     * @var null|\Wyomind\Core\Helper\Data
     */
    protected $_coreHelper = null;

    /**
     * Config constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Wyomind\Core\Helper\Data $coreHelper
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Wyomind\Core\Helper\Data $coreHelper
    ) {
        parent::__construct($context);
        $this->_coreHelper = $coreHelper;
    }


    /**
     * @return string
     */
    public function getSettingsLog()
    {
        return $this->_coreHelper->getDefaultConfig($this::SETTINGS_LOG);
    }
    

}

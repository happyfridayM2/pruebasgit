<?php
/**
 * Copyright © 2019 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\OrdersExportTool\Block\Adminhtml;

/**
 * Prepare the functions admin page
 */
class Functions extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * Block constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_controller = 'adminhtml_functions';
        $this->_blockGroup = 'Wyomind_OrdersExportTool';
        $this->_headerText = __('Manage custom functions');

        parent::_construct();
    }
}
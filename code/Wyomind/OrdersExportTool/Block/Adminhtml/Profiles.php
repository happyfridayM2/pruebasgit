<?php
/**
 * Copyright © 2019 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\OrdersExportTool\Block\Adminhtml;

/**
 * Prepare the Profiles admin page
 */
class Profiles extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * Block constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_controller = 'adminhtml_profiles';
        $this->_blockGroup = 'Wyomind_OrdersExportTool';
        $this->_headerText = __('Manage export profiles');
        
        $this->_addButtonLabel = __('Create a new profile');

        parent::_construct();
    }
}
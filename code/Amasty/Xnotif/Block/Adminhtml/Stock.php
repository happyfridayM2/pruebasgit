<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Xnotif
 */


namespace Amasty\Xnotif\Block\Adminhtml;

/**
 * Class Stock
 */
class Stock extends \Magento\Backend\Block\Widget\Grid\Container
{
    public function _construct()
    {
        parent::_construct();
        $this->_controller = 'adminhtml_stock';
        $this->_blockGroup = 'Amasty_Xnotif';
        $this->removeButton('add');
    }
}

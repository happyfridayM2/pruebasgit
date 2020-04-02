<?php
namespace Magebees\Navigationmenu\Block\Adminhtml;

class Menucreatorgroup extends \Magento\Backend\Block\Widget\Grid\Container
{
    protected function _construct()
    {
        $this->_controller = 'adminhtml_menucreatorgroup';
        $this->_blockGroup = 'Magebees_Navigationmenu';
        $this->_headerText = 'Menu Group';
        $this->_addButtonLabel = __('Add Group');
        parent::_construct();
    }
}

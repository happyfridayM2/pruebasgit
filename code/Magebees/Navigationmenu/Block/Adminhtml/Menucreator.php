<?php
namespace Magebees\Navigationmenu\Block\Adminhtml;

class Menucreator extends \Magento\Backend\Block\Template
{
    protected function _construct()
    {
        $this->_controller = 'adminhtml_menucreator';
        $this->_blockGroup = 'Magebees_Navigationmenu';
        $this->_headerText = 'Navigation Menu Pro Management';
        parent::_construct();
        $this->setTemplate('grid.phtml');
    }
}

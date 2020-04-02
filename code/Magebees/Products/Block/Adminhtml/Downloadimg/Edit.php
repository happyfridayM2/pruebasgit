<?php
namespace Magebees\Products\Block\Adminhtml\Downloadimg;
class Edit extends \Magento\Backend\Block\Widget\Form\Container {
    protected function _construct()	{
		$this->_objectId = 'id';
        $this->_blockGroup = 'Magebees_Products';
        $this->_controller = 'adminhtml_downloadimg';
		
        parent::_construct();
		$this->buttonList->remove('back');
		$this->buttonList->remove('save');
		$this->buttonList->remove('reset');
    }
}
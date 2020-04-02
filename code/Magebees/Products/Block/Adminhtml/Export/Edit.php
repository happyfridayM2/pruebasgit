<?php
namespace Magebees\Products\Block\Adminhtml\Export;
class Edit extends \Magento\Backend\Block\Widget\Form\Container
{
    protected function _construct()
    {
		$this->_objectId = 'id';
        $this->_blockGroup = 'Magebees_Products';
        $this->_controller = 'adminhtml_export';
        parent::_construct();
		$this->buttonList->remove('back');
		$this->buttonList->remove('save');
		$this->buttonList->remove('reset');
		$this->addButton(
            'importbutton',
            [
                'label' => __('Export Products'),
                'onclick' => 'runProfile(false)',
                'class' => 'action-default scalable save primary importbutton',
                'level' => -1,
				'id' => 'export_product',
            ]
        );
		$this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('block_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'categories_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'categories_content');
                }
            }					
					
		";       
    }
}
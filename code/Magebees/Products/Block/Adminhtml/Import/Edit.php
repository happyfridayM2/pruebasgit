<?php
namespace Magebees\Products\Block\Adminhtml\Import;
class Edit extends \Magento\Backend\Block\Widget\Form\Container 
{
    protected function _construct()	{
		$this->_objectId = 'id';
        $this->_blockGroup = 'Magebees_Products';
        $this->_controller = 'adminhtml_import';

        parent::_construct();
		$this->buttonList->remove('back');
		$this->buttonList->remove('save');
		$this->buttonList->remove('reset');
		$this->_formScripts[] = "";
		
		$this->addButton(
            'import_product',
            [
                'label' => __('Upload File'),
                'class' => 'scalable primary save',
                'level' => -1,
            ]
        );
		$this->addButton(
            'validatebutton',
            [
                'label' => __('Validate & Import File Data'),
                'onclick' => 'validateData()',
                'class' => 'primary validate',
                'level' => -1,
            ]
        );
		$this->addButton(
            'importbutton',
            [
                'label' => __('Import Products'),
                'onclick' => 'runImport()',
                'class' => 'action-default scalable save primary importbutton',
                'level' => -1,
            ]
        );
    }
}
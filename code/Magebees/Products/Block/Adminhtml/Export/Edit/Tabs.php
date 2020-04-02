<?php
namespace Magebees\Products\Block\Adminhtml\Export\Edit;
class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    protected function _construct()
    {
		parent::_construct();
        $this->setId('export_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Export Products'));
    }
	protected function _prepareLayout(){
		
		$this->addTab(
            'export_section',
            [
                'label' => __('Export Information'),
                'title' => __('Export Information'),
                'content' => $this->getLayout()->createBlock(
                    'Magebees\Products\Block\Adminhtml\Export\Edit\Tab\Export'
                )->toHtml(),
                'active' => true
            ]
        );
		$this->addTab(
            'export_file',
            [
                'label' => __('Exported Files'),
                'title' => __('Exported Files'),
                'content' => $this->getLayout()->createBlock(
                    'Magebees\Products\Block\Adminhtml\Export\Edit\Tab\Grid'
                )->toHtml(),
                'active' => false
            ]
        );
		$this->addTab('categories_section',
				[
					'label' => __('Categories'),
					'title' => __('Categories'),
					'url'       => $this->getUrl('products/export/categories', array('_current' => true)),
					'active' => false,
					'class'     => 'ajax',
				]
			);		
		return parent::_prepareLayout();
	}
}
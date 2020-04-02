<?php
namespace Magebees\Products\Block\Adminhtml\Import\Edit;
class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    protected function _construct()
    {
		parent::_construct();
        $this->setId('import_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Import Products'));
    }
	protected function _prepareLayout(){		
		$this->addTab(
            'import_section',
            [
                'label' => __('Upload File'),
                'title' => __('Upload File'),
                'content' => $this->getLayout()->createBlock(
                    'Magebees\Products\Block\Adminhtml\Import\Edit\Tab\Import'
                )->toHtml(),
                'active' => true
            ]
        );
		$this->addTab(
            'runprofule_section',
            [
                'label' => __('Run Profile'),
                'title' => __('Run Profile'),
                'content' => $this->getLayout()->createBlock(
                    'Magebees\Products\Block\Adminhtml\Import\Edit\Tab\Runprofile'
                )->toHtml(),
                'active' => false
            ]
        );
		$this->addTab(
            'validationlog',
            [
                'label' => __('Validation Log'),
                'title' => __('Validation Log'),
                'content' => $this->getLayout()->createBlock(
                    'Magebees\Products\Block\Adminhtml\Import\Edit\Tab\Validationlog'
                )->toHtml(),
                'active' => false
            ]
        );
		$this->addTab(
            'importlog',
            [
                'label' => __('Import Log'),
                'title' => __('Import Log'),
                'content' => $this->getLayout()->createBlock(
                    'Magebees\Products\Block\Adminhtml\Import\Edit\Tab\Importlog'
                )->toHtml(),
                'active' => false
            ]
        );
        $this->addTab(
            'samplecsv',
            [
                'label' => __('User Guide and Sample CSV files'),
                'title' => __('User Guide and Sample CSV files'),
                'content' => $this->getLayout()->createBlock(
                    'Magebees\Products\Block\Adminhtml\Import\Edit\Tab\Samplecsv'
                )->toHtml(),
                'active' => false
            ]
        );
		return parent::_prepareLayout();
	}
}
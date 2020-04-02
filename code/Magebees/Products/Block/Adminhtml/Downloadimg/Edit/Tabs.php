<?php
namespace Magebees\Products\Block\Adminhtml\Downloadimg\Edit;
class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    protected function _construct()
    {
		parent::_construct();
        $this->setId('downloadimg_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Download Images From Live URL'));
    }
	protected function _prepareLayout()
    {
		$this->addTab(
            'howtouse_section',
            [
                'label' => __('How To Use'),
                'title' => __('How To Use'),
                'content' => $this->getLayout()->createBlock(
                    'Magebees\Products\Block\Adminhtml\Downloadimg\Edit\Tab\Howtouse'
                )->toHtml(),
                'active' => true
            ]
        );
		$this->addTab(
            'downloadimg_section',
            [
                'label' => __('Download Products Images'),
                'title' => __('Download Products Images'),
                'content' => $this->getLayout()->createBlock(
                    'Magebees\Products\Block\Adminhtml\Downloadimg\Edit\Tab\Downloadimg'
                )->toHtml(),
                'active' => false
            ]
        );
		return parent::_prepareLayout();
	}
}
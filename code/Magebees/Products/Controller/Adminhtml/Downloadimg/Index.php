<?php
namespace Magebees\Products\Controller\Adminhtml\Downloadimg;

class Index extends \Magento\Backend\App\Action
{
    public function execute()
    {
        $this->_view->loadLayout();
        $this->_setActiveMenu('Magebees_Products::downloadimg');
        $this->_addBreadcrumb(__('Import Products'), __('Download Images From Live URL'));
        $this->_view->renderLayout();
    }
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magebees_Products::downloadimg');
    }
}

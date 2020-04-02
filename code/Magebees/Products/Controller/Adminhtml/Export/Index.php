<?php
namespace Magebees\Products\Controller\Adminhtml\Export;

class Index extends \Magento\Backend\App\Action
{
    public function execute()
    {
        $this->_view->loadLayout();
        $this->_setActiveMenu('Magebees_Products::export');
        $this->_addBreadcrumb(__('Export Products'), __('Export Products'));
        $this->_view->renderLayout();
    }
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magebees_Products::export');
    }
}
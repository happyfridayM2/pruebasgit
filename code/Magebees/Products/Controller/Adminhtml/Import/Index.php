<?php
namespace Magebees\Products\Controller\Adminhtml\Import;

class Index extends \Magento\Backend\App\Action
{
    public function execute()
    {
        $this->_view->loadLayout();
        $this->_setActiveMenu('Magebees_Products::import');
        $this->_addBreadcrumb(__('Import Products'), __('Import Products'));
        $this->_view->renderLayout();
    }
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magebees_Products::import');
    }
}
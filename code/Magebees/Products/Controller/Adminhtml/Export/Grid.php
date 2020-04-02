<?php
namespace Magebees\Products\Controller\Adminhtml\Export;

class Grid extends \Magento\Backend\App\Action
{
    public function execute()
    {
        $this->getResponse()->setBody($this->_view->getLayout()->createBlock('Magebees\Products\Block\Adminhtml\Export\Edit\Tab\Grid')->toHtml());
    }   
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magebees_Products::export');
    }
}
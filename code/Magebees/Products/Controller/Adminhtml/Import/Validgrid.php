<?php
namespace Magebees\Products\Controller\Adminhtml\Import;

class Validgrid extends \Magento\Backend\App\Action
{
    public function execute()
    {
        $this->getResponse()->setBody($this->_view->getLayout()->createBlock('Magebees\Products\Block\Adminhtml\Import\Edit\Tab\Validationlog')->toHtml());
    }
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magebees_Products::import');
    }
}

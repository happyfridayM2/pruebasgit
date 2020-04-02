<?php
namespace Magebees\Products\Controller\Adminhtml\Import;

class Importgrid extends \Magento\Backend\App\Action
{
    public function execute()
    {
        $this->getResponse()->setBody($this->_view->getLayout()->createBlock('Magebees\Products\Block\Adminhtml\Import\Edit\Tab\Importlog')->toHtml());
    }
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magebees_Products::import');
    }
}
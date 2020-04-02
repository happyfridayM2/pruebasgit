<?php
namespace  Magebees\Navigationmenu\Controller\Adminhtml\Menucreatorgroup;

class Grid extends \Magento\Backend\App\Action
{

    public function execute()
    {
        $this->getResponse()->setBody($this->_view->getLayout()->createBlock('Magebees\Navigationmenu\Block\Adminhtml\Menucreatorgroup\Grid')->toHtml());
    }
    protected function _isAllowed()
    {
        return true;
    }
}

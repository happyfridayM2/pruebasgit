<?php

namespace Magebees\Navigationmenu\Controller\Adminhtml\Menucreator;

class NewAction extends \Magento\Backend\App\Action
{
   
    public function execute()
    {
        $this->_forward('edit');
    }
    protected function _isAllowed()
    {
        return true;
    }
}

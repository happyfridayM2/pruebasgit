<?php

namespace Magebees\Navigationmenu\Controller\Adminhtml\Menucreator;

class ManageItem extends \Magento\Backend\App\Action
{
    protected $resultPageFactory;
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
    
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Magebees_Navigationmenu::manageitems');
        $resultPage->getConfig()->getTitle()->prepend(__('Manage Menu Items'));
        return $resultPage;
    }
    protected function _isAllowed()
    {
        return true;
    }
}

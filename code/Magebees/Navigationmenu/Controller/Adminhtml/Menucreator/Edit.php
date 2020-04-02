<?php

namespace Magebees\Navigationmenu\Controller\Adminhtml\Menucreator;

class Edit extends \Magento\Backend\App\Action
{
    protected $resultPageFactory;
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
		\Magebees\Navigationmenu\Model\Menucreator $menucreator,
		\Magento\Framework\Registry $registry,
		\Magento\Backend\Model\Session $session
    ) {
    
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
		$this->menucreator = $menucreator;
		$this->registry = $registry;
		$this->session = $session;
    }
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        $registryObject = $this->_objectManager->create('Magento\Framework\Registry');
        
        // 2. Initial checking
        if ($id) {
            $this->menucreator->load($id);
            if (!$this->menucreator->getId()) {
                $this->messageManager->addError(__('Menu Item Information Not Available.'));
                $this->_redirect('*/*/');
                return;
            }
        }
        // 3. Set entered data if was error when we do save
        
        $data = $this->session->getFormData(true);
        if (!empty($data)) {
            $$this->menucreator->setData($data);
        }
        $this->registry->register('menucreator', $this->menucreator);
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Magebees_Navigationmenu::managegroup');
        $resultPage->getConfig()->getTitle()->prepend(__('Navigation Menu Group Management'));
        return $resultPage;
    }
    protected function _isAllowed()
    {
        return true;
    }
}


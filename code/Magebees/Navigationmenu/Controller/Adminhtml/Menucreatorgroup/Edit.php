<?php

namespace Magebees\Navigationmenu\Controller\Adminhtml\Menucreatorgroup;

class Edit extends \Magento\Backend\App\Action
{
    protected $resultPageFactory;
	protected $_coreSession;
	protected $_scopeConfig;
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
		\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
		\Magento\Framework\Session\SessionManagerInterface $coreSession,
		\Magebees\Navigationmenu\Model\Menucreatorgroup $menucreatorgroup,
		\Magento\Framework\Registry $registry,
		\Magento\Backend\Model\Session $session
    ) {
    
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
		$this->_scopeConfig = $scopeConfig;
		$this->_coreSession = $coreSession;
		$this->menucreatorgroup = $menucreatorgroup;
		$this->registry = $registry;
		$this->session = $session;
		$this->_coreSession->start();
		$publishhtml = $this->_coreSession->getPublishHtml();
		
		$developer_mode_enable_disable = $this->_scopeConfig->getValue('navigationmenu/optimize_performance/developer_mode_enable_disable', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
					if(!$developer_mode_enable_disable){
						if($publishhtml){
						$configurl = $this->getUrl("adminhtml/system_config/edit/section/navigationmenu");
						$message = __('You need to PUBLISH Static HTML Menu again, so that your changes reflect in frontend, Please check it   <a href="%1">PUBLISH Static HTML Menu</a>',$configurl);
                    	$this->messageManager->addWarning($message);	
						}
					}
    }
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        // 2. Initial checking
        if ($id) {
            $this->menucreatorgroup->load($id);
            if (!$this->menucreatorgroup->getId()) {
                $this->messageManager->addError(__('Menu Group Information Not Available.'));
                $this->_redirect('*/*/');
                return;
            }
        }
        // 3. Set entered data if was error when we do save
        
        $data = $this->session->getFormData(true);
        if (!empty($data)) {
            $this->menucreatorgroup->setData($data);
        }
        $this->registry->register('menucreatorgroup', $this->menucreatorgroup);
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

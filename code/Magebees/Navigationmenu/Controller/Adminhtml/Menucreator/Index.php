<?php

namespace Magebees\Navigationmenu\Controller\Adminhtml\Menucreator;

class Index extends \Magento\Backend\App\Action
{
    protected $resultPageFactory;
	protected $_scopeConfig;
	protected $_coreSession;
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
		\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
		\Magento\Framework\Session\SessionManagerInterface $coreSession
    ) {
    $this->resultPageFactory = $resultPageFactory;
		$this->_scopeConfig = $scopeConfig;
		$this->_coreSession = $coreSession;
        parent::__construct($context);
        $this->_coreSession->start();
		$publishhtml = $this->_coreSession->getPublishHtml();
		$developer_mode_enable_disable = $this->_scopeConfig->getValue('navigationmenu/optimize_performance/developer_mode_enable_disable', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
					if(!$developer_mode_enable_disable){
						if($publishhtml){
						$configurl = $this->getUrl("adminhtml/system_config/edit/section/navigationmenu");
						$message = __('You need to PUBLISH Static HTML Menu again, so that your changes reflect in frontend, Please check it <a href="%1">PUBLISH Static HTML Menu</a>',$configurl);
                    	$this->messageManager->addWarning($message);	
						}
					}
    }
    public function execute()
    {
         
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Magebees_Navigationmenu::items');
        $resultPage->getConfig()->getTitle()->prepend(__('Manage Menu Items'));
		return $resultPage;
    }
    protected function _isAllowed()
    {
        return true;
    }
}

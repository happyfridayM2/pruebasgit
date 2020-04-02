<?php
namespace Magebees\Navigationmenu\Controller\Adminhtml\Menucreator;

class DeleteItems extends \Magento\Backend\App\Action
{
    protected $_scopeConfig;
	protected $_coreSession;
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
    	\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
		\Magento\Framework\Session\SessionManagerInterface $coreSession,
		\Magebees\Navigationmenu\Helper\Data $helper
    ) {
    
        parent::__construct($context);
    	 $this->_scopeConfig = $scopeConfig;
		$this->_coreSession = $coreSession;
		$this->helper = $helper;

    }
    public function execute()
    {
        if ($this->getRequest()->getParam('id') > 0) {
            $model= $this->_objectManager->create('Magebees\Navigationmenu\Model\Menucreator');
            $currentmenu_id = $this->getRequest()->getParam('id');
            $menu_delete_option = $this->getRequest()->getParam('deleteoption');
            $child_menu_item_list = [];
            $child_menu_item = $this->helper->getChildMenuItem($currentmenu_id);
            $child_menu_item_list[$currentmenu_id] = $child_menu_item;
            $delete_menu = [];
            foreach ($child_menu_item as $key => $menuitem) {
                if (!is_array($menuitem)) {
                    array_push($delete_menu, $menuitem);
                }
            }
                 
            if ($menu_delete_option == "deleteparent") {
                try {
                    foreach ($delete_menu as $menuid) {
                        if ($currentmenu_id == $menuid) {
                            $menu= $this->_objectManager->create('Magebees\Navigationmenu\Model\Menucreator')->load($menuid);
                            $menu->delete();
                        } else {
                            $model= $this->_objectManager->create('Magebees\Navigationmenu\Model\Menucreator')->load($menuid);
                            if ($model->getParentId() == $currentmenu_id) {
                                $model->setParentId("0");
                                $model->setPosition("0");
                            }
                            $model->save();
                        }
                    }
                        $this->messageManager->addSuccess(__('Menu item was deleted successfully!'));
				$developer_mode_enable_disable = $this->_scopeConfig->getValue('navigationmenu/optimize_performance/developer_mode_enable_disable', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
					if(!$developer_mode_enable_disable){
						$this->_coreSession->start();
						$publishhtml = $this->_coreSession->setPublishHtml(true);
						
					}
                } catch (\Exception $e) {
                    $this->messageManager->addError($e->getMessage());
                    $this->_redirect('*/*/edit', ['id' => $this->getRequest()->getParam('id')]);
                }
                        $this->_redirect('*/*/');
            } elseif ($menu_delete_option== "deleteparentchild") {
                try {
                    $count=0;
                    $count=count($delete_menu);
                    $i=0;
                    foreach ($delete_menu as $menuid) {
                        $i++;
                        $menu= $this->_objectManager->create('Magebees\Navigationmenu\Model\Menucreator')->load($menuid);
                        $menu->delete();
                    }
                    $this->messageManager->addSuccess(
                        __('A total of   '.$count .'  record(s) were successfully deleted.', count($delete_menu))
                    );
					$configurl = $this->getUrl("adminhtml/system_config/edit/section/navigationmenu");
					$link = '<a href='.$configurl.'> PUBLISH Static HTML Menu</a>';
					$this->messageManager->addWarning(__('PUBLISH Static HTML Menu again so that your changes reflect in frontend .'.$link));
                } catch (\Exception $e) {
                    $this->messageManager->addError($e->getMessage());
                    $this->_redirect('*/*/edit', ['id' => $this->getRequest()->getParam('id')]);
                }
            }
        }
                $this->_redirect('*/*/');
    }
    protected function _isAllowed()
    {
        return true;
    }
}

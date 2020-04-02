<?php

namespace Magebees\Navigationmenu\Controller\Adminhtml\Menucreator;

class UpdateMenu extends \Magento\Backend\App\Action
{
    protected $resultPageFactory;
	protected $_scopeConfig;
	protected $_coreSession;
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
		\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
		\Magento\Framework\Session\SessionManagerInterface $coreSession,
		\Magebees\Navigationmenu\Model\Menucreator $menucreator
    ) {
    
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
		 $this->_scopeConfig = $scopeConfig;
		$this->_coreSession = $coreSession;
		$this->menucreator = $menucreator;

    }
    public function execute()
    {
        $menu_order_item_details = [];
        $_update_menu = $this->getRequest()->getPost();
        $all_menuitems =    $_update_menu['menuitemorder'];
    
        if ($all_menuitems != '') {
            $menus=explode("&", $all_menuitems);
    
            foreach ($menus as $item_key => $item_value) {
                if (!empty($item_value)) {
                    $menu_item_order = explode("=", $item_value);
                    if (strpos($menu_item_order[0], 'group[id]') !== false) {
                        $group_id = $menu_item_order[1];
                    }
                    if (strpos($menu_item_order[0], 'menuItem') !== false) {
                        $menu_id = str_replace("menuItem[", "", $menu_item_order[0]);
                        $menu_id = str_replace("]", "", $menu_id);
                        if ($menu_item_order[1] == 'null') {
                            $parent_id = '0';
                        } else {
                            $parent_id = $menu_item_order[1];
                        }
                        $menu_order_item_details[$menu_id] =  [
                                                'group_id' => $group_id,
                                                'parent_id' => $parent_id
                        ];
                        ;
                    }
                }
            }
            $temp=[];
            $finalsort=[];
            foreach ($menu_order_item_details as $key => $value) {
                if ($key != '') {
                    $temp[$key]=$value['parent_id'];
                    $finalsort[$key]=['group_id' => $value['group_id'],'parent'=>$value['parent_id'],'sortorder'=> $this->get_Sortorder($value['parent_id'], $temp)];
                }
            }
            
            try {
                foreach ($finalsort as $menu_id => $values) :
                    if ($menu_id != "0") {
                        $model = $this->menucreator->load($menu_id);
                            $model->setGroupId($values['group_id']);
                            $model->setParentId($values['parent']);
                            $model->setPosition($values['sortorder']);
                            $model->save();
                    }
                endforeach;
                
				
				$developer_mode_enable_disable = $this->_scopeConfig->getValue('navigationmenu/optimize_performance/developer_mode_enable_disable', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
					if(!$developer_mode_enable_disable){
						$this->_coreSession->start();
						$publishhtml = $this->_coreSession->setPublishHtml(true);
					}
				
            //$this->_redirect('*/*/');
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            }
            $all_menuitems = '';
        }
    }
    public function get_Sortorder($match, $temp)
    {
        $count = 0;

        foreach ($temp as $key => $value) {
            if ($value == $match) {
                $count++;
            }
        }

        return $count;
    }
    protected function _isAllowed()
    {
        return true;
    }
}

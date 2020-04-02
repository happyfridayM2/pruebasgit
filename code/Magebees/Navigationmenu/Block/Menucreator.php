<?php
namespace Magebees\Navigationmenu\Block;
class Menucreator extends \Magento\Framework\View\Element\Template
{
    protected $group_option = '';
    protected $_config;
    protected $_optimizeconfig;
    
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
		\Magebees\Navigationmenu\Model\Menucreatorgroup $menucreatorgroup,
		\Magento\Customer\Model\Session $customerSession,
		\Magebees\Navigationmenu\Model\Menustaticitem $menustaticitem,
		\Magento\Store\Model\StoreManagerInterface $storeManager,
        array $data = []
    ) {
     	$this->menucreatorgroup = $menucreatorgroup;
		$this->menustaticitem = $menustaticitem;
		$this->customerSession = $customerSession;
		$this->storeManager = $storeManager;
        parent::__construct($context, $data);
    }
    public function getConfig()
    {
           $this->_config = $this->_scopeConfig->getValue('navigationmenu/general', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
           return $this->_config;
    }
    public function getOptimizeConfig()
    {
            $this->_optimizeconfig = $this->_scopeConfig->getValue('navigationmenu/optimize_performance', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            return $this->_optimizeconfig;
    }
      //for posh theme
    public function getIdFromUniqueCode($unique_code){
        $group_id = $this->menucreatorgroup->load($unique_code,'unique_code')->getId();
        return $group_id;
    }
    //for posh theme  
    public function _prepareLayout()
    {
        return parent::_prepareLayout();
    }
    
    public function getMenutype($group_id)
    {
		$group_details = $this->menucreatorgroup->load($group_id);
        $group_menutype = trim($group_details->getMenutype());
        return $group_menutype;
    }
    public function getMenuStatus($group_id)
    {
		$group_details = $this->menucreatorgroup->load($group_id);
        $group_status = trim($group_details->getStatus());
        return $group_status;
    }
	public function getCurrentCustomerDetails(){
			$customer_detail = array();
		
			if($this->customerSession->isLoggedIn()){
        	$customer_detail['customerGroupId'] = $this->customerSession->getCustomer()->getGroupId();
			$customer_detail['customerStatus'] = 'login';
		}else{
			$customer_detail['customerGroupId'] = 0;
			$customer_detail['customerStatus'] = 'guest';
		}
		return $customer_detail;
	}
	public function getCurrentStoreDetails(){
		$store_detail = array();
		$store_detail['storeId'] = $this->storeManager->getStore()->getStoreId();
        $store_detail['storeCode'] =$this->storeManager->getStore()->getCode();
        $store_detail['website_id'] = $this->storeManager->getWebsite()->getId();
		return $store_detail;
	}
    public function isJSON($string)
    {
        return is_string($string) && is_array(json_decode($string, true)) && (json_last_error() == JSON_ERROR_NONE) ? true : false;
    }
	public function generateStaticHtml($groupId,$website_id,$store_id,$storeCode,$customerStatus,$customerGroupId)
	{
        $group_option = '';
        $group_details = $this->menucreatorgroup->load($groupId);
        if ($group_details->getStatus() == "1") {
            $group_position = $group_details->getPosition();
            $group_menutype = $group_details->getMenutype();
            if (($group_menutype == 'mega-menu')) {
                $group_option = $group_menutype." ".$group_position;
            } else {
                $group_option = $group_menutype;
            }
            $group_level = $group_details->getLevel();
            $direction = $group_details->getDirection();
        
            if ($direction=='rtl') {
                $direction_css = 'rtl';
            } elseif ($direction=='ltr') {
                $direction_css = 'ltr';
            }
        
            if ($group_menutype == 'list-item') {
                if ($direction_css!='') {
                    $menufront = "<div id='cwsMenu-".$groupId."' class='".$direction_css."'>";
                } else {
                    $menufront = "<div id='cwsMenu-".$groupId."'>";
                }
            } elseif ($group_menutype == 'mega-menu') {
                if ($direction_css!='') {
                    $menufront = "<div id='cwsMenu-".$groupId."' class='cwsMenuOuter ".$group_position." ".$direction_css."'>";
                } else {
                    $menufront = "<div id='cwsMenu-".$groupId."' class='cwsMenuOuter'>";
                }
            } else {
                if ($direction_css!='') {
                    $menufront = "<div id='cwsMenu-".$groupId."' class='cwsMenuOuter ".$direction_css."'>";
                } else {
                    $menufront = "<div id='cwsMenu-".$groupId."' class='cwsMenuOuter'>";
                }
            }
        
            if ($group_details->getShowhidetitle() == "2") {
                $menufront .= '<h3 class="menuTitle">'.$group_details->getTitle().'</h3>';
            }
            if ($group_menutype != 'list-item') {
                $menufront .="<ul class='cwsMenu ".$group_option."'>";
            } else {
                $menufront .="<ul>";
            }
             $menufront .= $this->menustaticitem->getStaticMenuContent($groupId,$website_id,$store_id,$storeCode,$customerStatus,$customerGroupId);
			
            $menufront .= "</ul>";
            $menufront .= "</div>";
            return $menufront;
        } else {
            return;
        }
    }
}

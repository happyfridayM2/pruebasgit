<?php

namespace Magebees\Navigationmenu\Controller\Adminhtml\Menudata;

class GenerateStaticMenu extends \Magento\Backend\App\Action
{
    protected $_coreSession;
	protected $_scopeConfig;
	public function __construct(
        \Magento\Backend\App\Action\Context $context,
		\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
		\Magento\Framework\Session\SessionManagerInterface $coreSession,
		\Magento\Framework\Filesystem\Driver\File $_file,
		\Magebees\Navigationmenu\Helper\Data $helper,
		\Magento\Framework\Json\Helper\Data $jsonhelper,
		\Magebees\Navigationmenu\Model\Menucreatorgroup $menucreatorgroup,
		\Magebees\Navigationmenu\Model\Menustaticitem $menustaticitem
	) {
        parent::__construct($context);
		$this->_scopeConfig = $scopeConfig;
		$this->_file = $_file;
		$this->_coreSession = $coreSession;
		$this->helper = $helper;
		$this->jsonhelper = $jsonhelper;
		$this->menucreatorgroup = $menucreatorgroup;
		$this->menustaticitem = $menustaticitem;
    }
    public function execute()
    {
		
		$params = $this->getRequest()->getParams();
        $file_list = json_decode($this->getRequest()->getParam('file_list'));
		
        try {
			if(isset($file_list[0])){
			$menuGroupId = $file_list[0][0]; 
			$store_id = $file_list[0][1];
			$storeCode = $file_list[0][2];
			$website_id = $file_list[0][3];
			$customerGroupId = $file_list[0][4];
			$customerStatus = $file_list[0][5]; 
			$myFile = $file_list[0][6];
			
			$dir_path_array = explode("/",$myFile);
			array_pop($dir_path_array);
			$myFile_path =  implode("/",$dir_path_array);
				
				$dir_permission = 0755;
				$file_permission = 0664;
			
				if(!$this->_file->isExists($myFile_path))
				{
				$this->_file->createDirectory($myFile_path,$dir_permission);
				}
				
					
				if(!$this->_file->isWritable($myFile_path))
					{
					$this->_file->changePermissionsRecursively($myFile_path,$dir_permission,$file_permission);
					}
				
				
				
				
	
			$menu_html_content = $this->generateStaticHtml($menuGroupId,$website_id,$store_id,$storeCode,$customerStatus,$customerGroupId);
			if (file_exists($myFile)) {
				unlink($myFile); // delete file	
			}
			$fh = fopen($myFile, 'w'); // or die("error");
			$search = [
				'/\>[^\S ]+/s',  // strip whitespaces after tags, except space
				'/[^\S ]+\</s',  // strip whitespaces before tags, except space
				'/(\s)+/s'       // shorten multiple whitespace sequences
				];
			$replace = [ '>', '<','\\1'];
			$menu_compressedhtml = preg_replace($search, $replace, $menu_html_content);
			fwrite($fh, $menu_compressedhtml);
			fclose($fh);
				unset($file_list[0]);
			}
			
			
			$info = array();
			if(count($file_list) > 0)
			{
			$info['next'] = true;
			}else{
			$info['next'] = false;
				$this->_coreSession->start();
				$publishhtml = $this->_coreSession->getPublishHtml();
				$developer_mode_enable_disable = $this->_scopeConfig->getValue('navigationmenu/optimize_performance/developer_mode_enable_disable', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
					if(!$developer_mode_enable_disable){	
						
						$message = __('PUBLISH Static HTML Menu Successfully.');
                    	$this->messageManager->addSuccess($message);	
						
					}
				$this->_coreSession->start();
				$publishhtml = $this->_coreSession->unsPublishHtml();
			}
			$this->getResponse()->representJson($this->jsonhelper->jsonEncode($info));
            return;
			} catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
       		$info = array(); 
			$info['error_message'] = $e->getMessage();
			
			$this->getResponse()->representJson($this->jsonhelper->jsonEncode($info));
            return;
		
		}
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
    protected function _isAllowed()
    {
        return true;
    }
}


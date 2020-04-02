<?php
namespace Magebees\Navigationmenu\Controller\Adminhtml\Menucreatorgroup;

class MassDelete extends \Magento\Backend\App\Action
{
   
     public function __construct(
        \Magento\Backend\App\Action\Context $context,
    	\Magebees\Navigationmenu\Model\Menucreatorgroup $menucreatorgroup,
		\Magebees\Navigationmenu\Helper\Data $helper
    ) {
    
        parent::__construct($context);
    	$this->menucreatorgroup = $menucreatorgroup;
		$this->helper = $helper;

    }
 
    public function execute()
    {
       
        $groupIds = $this->getRequest()->getParam('navigationmenu');
        
        if (!is_array($groupIds) || empty($groupIds)) {
            $this->messageManager->addError(__('Please select items.'));
        } else {
            try {
                $count=0;
                 $count=count($groupIds);
                foreach ($groupIds as $groupId) {
                    $group= $this->menucreatorgroup->load($groupId);
					$path_css = $this->helper->getDynamicCSSDirectoryPath();
					$menu_type = $this->menucreatorgroup->getMenutype();
					$path_css .= $menu_type."-".$groupId.".css";
					if(is_file($path_css)) {
						$result = unlink($path_css); // delete Old Less file
					}
                    $group->delete();
                }
                $this->messageManager->addSuccess(
                    __('A total of   '.$count .'  record(s) have been deleted.', count($groupIds))
                );
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            }
        }
         $this->_redirect('*/*/');
    }
    
    protected function _isAllowed()
    {
        return true;
    }
}

<?php
namespace Magebees\Navigationmenu\Controller\Adminhtml\Menucreatorgroup;

class Delete extends \Magento\Backend\App\Action
{
   
   protected $_scopeConfig;
	protected $_coreSession;
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
        $groupId = $this->getRequest()->getParam('id');
        try {
                $group = $this->menucreatorgroup->load($groupId);
				$path_css = $this->helper->getDynamicCSSDirectoryPath();
				$menu_type = $this->menucreatorgroup->getMenutype();
				$path_css .= $menu_type."-".$groupId.".css";
				if(is_file($path_css)) {
					$result = unlink($path_css); // delete Old Less file
				}
                $group->delete();
                $this->messageManager->addSuccess(
                    __('Group was deleted successfully!')
                );
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        }
        $this->_redirect('*/*/');
    }
    protected function _isAllowed()
    {
        return true;
    }
}

<?php
namespace Magebees\Navigationmenu\Controller\Adminhtml\Menucreatorgroup;

class MassLevel extends \Magento\Backend\App\Action
{
   public function __construct(
        \Magento\Backend\App\Action\Context $context,
    	\Magebees\Navigationmenu\Model\Menucreatorgroup $menucreatorgroup
    ) {
    
        parent::__construct($context);
    	$this->menucreatorgroup = $menucreatorgroup;

    }
    
    public function execute()
    {
        
        $delivered_level = (int)$this->getRequest()->getPost('level')-1;
        $groupIds = $this->getRequest()->getParam('navigationmenu');
        
        if (!is_array($groupIds) || empty($groupIds)) {
            $this->messageManager->addError(__('Please select items.'));
        } else {
            try {
                $count=0;
                 $count=count($groupIds);
                foreach ($groupIds as $groupId) {
                    $group = $this->menucreatorgroup->load($groupId)->setLevel($delivered_level)->save();
                }
                $this->messageManager->addSuccess(
                    __('A total of   '.$count .'  group(s) menu level were successfully updated.', count($groupIds))
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

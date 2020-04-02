<?php

namespace Magebees\Navigationmenu\Controller\Adminhtml\Menudata;

use Magento\Framework\Controller\ResultFactory;

class AddSubParent extends \Magento\Backend\App\Action
{
    protected $resultPageFactory;
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
		\Magebees\Navigationmenu\Model\Menucreator $menucreator
    ) {
    
        parent::__construct($context);
		$this->menucreator = $menucreator;
        $this->resultPageFactory = $resultPageFactory;
    }
    public function execute()
    {
        $params = $this->getRequest()->getParams();
        $groupId = $this->getRequest()->getParam('group_id');
        $current_menu_id = $this->getRequest()->getParam('current_menu');
        $Parent_item = $this->menucreator->getAddSubParentItems($groupId, $current_menu_id);
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setData($Parent_item);
        return $resultJson;
    }
    protected function _isAllowed()
    {
		 return true;
    }
}

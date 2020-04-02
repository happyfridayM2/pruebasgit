<?php

namespace Magebees\Navigationmenu\Controller\Adminhtml\Menucreator;

class EditForm extends \Magento\Backend\App\Action
{
    protected $resultPageFactory;
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
		\Magebees\Navigationmenu\Model\Menucreator $menucreator
    ) {
    
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
		$this->menucreator = $menucreator;
    }
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        $model = $this->menucreator->load($id);
        $this->getResponse()->setBody(json_encode($model->getData()));
    }
    protected function _isAllowed()
    {
        return true;
    }
}

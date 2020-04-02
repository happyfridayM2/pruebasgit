<?php
namespace Magebees\Products\Controller\Adminhtml\Import;

class Validate extends \Magento\Backend\App\Action
{
    protected $coreRegistry = null;
    protected $resultPageFactory;
    
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Registry $registry
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->coreRegistry = $registry;
        parent::__construct($context);
    }
    public function execute()
    {
        $next_step=true;
        $importfiletype = $this->getRequest()->getParam('importfiletype');
        if ($importfiletype) {
            $model = $this->_objectManager->create('\Magebees\Products\Model\Validator');
        } else {
            $next_step=false;
        }
        if ($this->getRequest()->getParam('clearOldData')=='true') {
            $collection = $this->_objectManager->create('\Magebees\Products\Model\ResourceModel\Profiler')->truncate();
        }
        $url = $model->setProfilerData();
        $this->getResponse()->representJson($this->_objectManager->get('Magento\Framework\Json\Helper\Data')->jsonEncode(['url'=>$url]));
    }
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magebees_Products::import');
    }
}
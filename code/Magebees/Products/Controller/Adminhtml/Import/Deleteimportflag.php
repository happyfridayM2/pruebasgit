<?php
namespace Magebees\Products\Controller\Adminhtml\Import;

class Deleteimportflag extends \Magento\Backend\App\Action
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
        $filesystem = $this->_objectManager->get('Magento\Framework\Filesystem');
        $reader = $filesystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::VAR_DIR);
        $flagDir = $reader->getAbsolutePath("import/").'cws_product_import_flag_do_not_delete-'.$timestamp.'.flag';
        if (file_exists($flagDir)) {
            unlink($flagDir);
        }
    }   
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magebees_Products::import');
    }
}
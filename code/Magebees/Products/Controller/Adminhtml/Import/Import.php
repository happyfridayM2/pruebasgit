<?php
namespace Magebees\Products\Controller\Adminhtml\Import;

use Magento\Framework\App\Filesystem\DirectoryList;

class Import extends \Magento\Backend\App\Action
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
        $files = $this->getRequest()->getFiles()->toarray();
        if (isset($files['filename']['name']) && $files['filename']['name'] != '') {
            try {
                $mediaDirectory = $this->_objectManager->get('Magento\Framework\Filesystem')->getDirectoryRead(DirectoryList::VAR_DIR);
                if (!file_exists($mediaDirectory->getAbsolutePath().'import')) {
                    mkdir($mediaDirectory->getAbsolutePath().'import', 0777, true);
                }
                $uploader = $this->_objectManager->create('Magento\MediaStorage\Model\File\Uploader', ['fileId' => 'filename']);
                $allowed_ext_array = ['csv'];
                $uploader->setAllowedExtensions($allowed_ext_array);
                $uploader->setAllowRenameFiles(true);
                $uploader->setFilesDispersion(false);
                $result = $uploader->save($mediaDirectory->getAbsolutePath('import/'));
                $path = $mediaDirectory->getAbsolutePath('import');
            } catch (\Exception $e) {
                $this->messageManager->addError(__($e->getMessage()));
                return;
            }
        }
        $result = "<div class='message message-success success mb-success'><div data-ui-id='messages-message-success'>File upload successfully - <b>".$result['file']."</b><br/>Please wait you will be redirect to next tab...</div></div>";
            $this->getResponse()->representJson($this->_objectManager->get('Magento\Framework\Json\Helper\Data')->jsonEncode($result));
    }   
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magebees_Products::import');
    }
}
<?php
namespace Magebees\Products\Controller\Adminhtml\Export;

class MassDelete extends \Magento\Backend\App\Action
{
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Filesystem\Driver\File $file
    ) {
        $this->_file = $file;
        parent::__construct($context);
    }
    public function execute()
    {
        $exportIds = $this->getRequest()->getParam('export_id');
        if (!is_array($exportIds) || empty($exportIds)) {
            $this->messageManager->addError(__('Please select Item(s).'));
        } else {
            try {
                $filesystem = $this->_objectManager->get('Magento\Framework\Filesystem');
                $reader = $filesystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::VAR_DIR);
                
                foreach ($exportIds as $exportId) {
                    $model = $this->_objectManager->get('Magebees\Products\Model\Exportfile')->load($exportId);
                    $filepath = $reader->getAbsolutePath("export/".$model->getData("file_name"));
                    if ($this->_file->isExists($filepath)) {
                        $this->_file->deleteFile($filepath);
                    }
                    $model->delete();
                }
                $this->messageManager->addSuccess(__('A total of %1 record(s) have been deleted.', count($exportIds)));
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            }
        }
         $this->_redirect('*/*/');
    }
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magebees_Products::export');
    }
}
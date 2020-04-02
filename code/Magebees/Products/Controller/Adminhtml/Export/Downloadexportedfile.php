<?php
namespace Magebees\Products\Controller\Adminhtml\Export;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Downloadexportedfile extends \Magento\Backend\App\Action
{
    protected $resultPageFactory;
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    public function execute()
    {
        $id=$this->getRequest()->getParam('id');
        $exportedfile = $this->_objectManager->create('\Magebees\Products\Model\Exportfile');
        $fname = $exportedfile->load($id);
        $filename = $fname->getFileName();

        $filesystem = $this->_objectManager->get('Magento\Framework\Filesystem');
        $reader = $filesystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::VAR_DIR);
        $filepath = $reader->getAbsolutePath("export/".$filename);
        if (! is_file($filepath) || ! is_readable($filepath)) {
            $result = "";
            $result .= "File not exists";
            $this->getResponse()->representJson($this->_objectManager->get('Magento\Framework\Json\Helper\Data')->jsonEncode($result));
            return;
        }
        $this->getResponse()
                ->setHttpResponseCode(200)
                ->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true)
                ->setHeader('Pragma', 'public', true)
                ->setHeader('Content-type', 'application/force-download')
                ->setHeader('Content-Length', filesize($filepath))
                ->setHeader('Content-Disposition', 'attachment' . '; filename=' . basename($filepath));
        $this->getResponse()->clearBody();
        $this->getResponse()->sendHeaders();
        readfile($filepath);
    }
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magebees_Products::export');
    }
}
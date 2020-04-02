<?php
namespace Magebees\Products\Controller\Adminhtml\Order;
use Magento\Framework\App\Filesystem\DirectoryList;
class Exportimportcsv extends \Magento\Backend\App\Action 
{
	protected $resultPageFactory;
	protected $fileFactory;

	public function __construct(
		\Magento\Backend\App\Action\Context $context,
		\Magento\Framework\App\Response\Http\FileFactory $fileFactory,    
		\Magento\Framework\View\Result\PageFactory $resultPageFactory
	) {
		$this->resultPageFactory = $resultPageFactory;
		$this->fileFactory       = $fileFactory;
		parent::__construct($context);
	}
	public function execute() {
		$fileName = 'Import-Log.csv';
		$content = $this->_view->getLayout()->createBlock(
						\Magebees\Products\Block\Adminhtml\Import\Edit\Tab\Importlog::class
				)->getCsvFile();

		return $this->fileFactory->create($fileName, $content, DirectoryList::VAR_DIR);
	}
}
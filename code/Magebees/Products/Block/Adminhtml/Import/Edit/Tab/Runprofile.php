<?php
namespace Magebees\Products\Block\Adminhtml\Import\Edit\Tab;
class Runprofile extends \Magento\Backend\Block\Widget\Form\Generic
{
	public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magebees\Products\Helper\Data $helper,
        array $data = array()
    ) {
		$this->setTemplate('Magebees_Products::runprofile.phtml');
		$this->helper = $helper;
        parent::__construct($context, $registry, $formFactory, $data);
	}	
	public function getImportedCSVFiles(){
        $files = array();
		$filesystem = $this->helper->getObjectManager()->get('Magento\Framework\Filesystem');
		$reader = $filesystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::VAR_DIR);
		$path = $reader->getAbsolutePath("import");
		if(is_dir($path)){
			$dir = dir($path);
			while (false !== ($entry = $dir->read())) {
				if($entry != '.' && $entry != '..'){
					$file_parts = pathinfo($entry);
					if(isset($file_parts['extension']) && $file_parts['extension'] == 'csv')
					{
						$files[] = $file_parts['basename']; 
					}
				}
			}
			sort($files);
			$dir->close();
			return $files;
		}else {
			 return;
		}
    }
    protected function _isAllowedAction($resourceId){
        return $this->_authorization->isAllowed($resourceId);
    }
}
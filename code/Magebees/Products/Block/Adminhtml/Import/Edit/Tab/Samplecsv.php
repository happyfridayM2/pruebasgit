<?php
namespace Magebees\Products\Block\Adminhtml\Import\Edit\Tab;
class Samplecsv extends \Magento\Backend\Block\Widget\Form\Generic
{
	public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        array $data = array()
    ) {
		$this->setTemplate('Magebees_Products::samplecsv.phtml');
        parent::__construct($context, $registry, $formFactory, $data);
	}	
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }
}
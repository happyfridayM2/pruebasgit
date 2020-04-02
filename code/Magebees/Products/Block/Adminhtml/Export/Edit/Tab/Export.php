<?php
namespace Magebees\Products\Block\Adminhtml\Export\Edit\Tab;
class Export extends \Magento\Backend\Block\Widget\Form\Generic
{	
	public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magebees\Products\Helper\Data $helper,

        array $data = array()
    ) {
		$this->setTemplate('Magebees_Products::export.phtml');
        parent::__construct($context, $registry, $formFactory, $data);
        $this->helper = $helper;	
	}	
	public function getAttributeSet()
    {
        $coll = $this->helper->getObjectManager()->create('\Magento\Catalog\Model\Product\AttributeSet\Options');
		return $coll->toOptionArray();
	}
	public function getStoreData()
    {
		$model_store = $this->helper->getObjectManager()->create('Magento\Store\Model\System\Store');
		$store_info	= $model_store->getStoreValuesForForm(false, true);
		return $store_info;
	}
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }

    public function getAttriubteList()
    {
        $attributeInfo = $this->helper->getObjectManager()->create('\Magento\Catalog\Model\ResourceModel\Eav\Attribute');
        $attributeCollection = $attributeInfo->getCollection()->addFieldToFilter('entity_type_id', array('eq' => 4));
        $attributeCodes = array();
        foreach($attributeCollection as $attributes)
        {
            if($attributes->getAttributeCode() == "category_ids"){
                $attributeCodes['categories'] = "categories";
            }else{
                $attributeCodes[$attributes->getAttributeCode()] = $attributes->getAttributeCode();
            }
        }

        return $attributeCodes;
    }	
}
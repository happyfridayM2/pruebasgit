<?php
namespace Magebees\Products\Controller\Adminhtml\Export;

class Exportrecordcount extends \Magento\Backend\App\Action
{
    protected $coreRegistry = null;
    protected $resultPageFactory;
    
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Stdlib\DateTime\DateTime $date
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->coreRegistry = $registry;
        $this->date = $date;
        parent::__construct($context);
    }
    public function execute()
    {
        $data = [];
        $can_proceed = false;
        $count = 0;
        $store_export_id = $this->getRequest()->getParam('store_id');
        $attr_id = $this->getRequest()->getParam('attr_id');
        $type_id = $this->getRequest()->getParam('type_id');
        $export_for = $this->getRequest()->getParam('export_for');
        $status_id = $this->getRequest()->getParam('status_dropdown');
        $visibility_id = $this->getRequest()->getParam('visibility_dropdown');
        $fromId = $this->getRequest()->getParam('fromId');
        $toId = $this->getRequest()->getParam('toId');
        $cat_ids = $this->getRequest()->getParam('categoriesSelect');

        if ($attr_id != '*') {
            $_product_collection = $this->_objectManager->create('\Magento\Catalog\Model\Product')->getCollection()->addAttributeToSelect("*")->addFieldToFilter('attribute_set_id', $attr_id);
        } else {
            $_product_collection = $this->_objectManager->create('\Magento\Catalog\Model\Product')->getCollection()->addAttributeToSelect("*");
        }
        if ($store_export_id!='*') {
            $_product_collection->addStoreFilter($store_export_id);
        }
        if ($type_id != '*') {
            $_product_collection->addFieldToFilter('type_id', $type_id);
        }
        if ($status_id != '*') {
            $_product_collection->addAttributeToFilter('status', ['in' => $status_id]);
        }
        if ($visibility_id != '*') {
            $_product_collection->addAttributeToFilter('visibility', $visibility_id);
        }
        if ($fromId != '' && $toId != '') {
            $_product_collection->addAttributeToFilter('entity_id', ['from' => $fromId,'to' => $toId]);
        }
        if (!empty($cat_ids)) {
            $cat_ids = array_unique($cat_ids);
            if (count(array_filter($cat_ids)) > 0) {
                $_product_collection->addCategoriesFilter(['in' => [$cat_ids]]);
            }
        }  
        $count = $_product_collection->getSize();
        if ($count>0) {
            $can_proceed=true;
            $current_time = str_replace(" ", "-", $this->date->gmtDate());
            $current_time = str_replace(":", "-", $current_time);
            $data['timestamp'] = $current_time;
        }
        if ($count>200) {
            $data['splitExport']=true;
        } else {
            $data['splitExport']=false;
        }
        $data['export_can_proceed']=$can_proceed;
        $data['totalOrder']=$count;
        $this->getResponse()->representJson($this->_objectManager->get('Magento\Framework\Json\Helper\Data')->jsonEncode($data));
    }
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magebees_Products::export');
    }
}
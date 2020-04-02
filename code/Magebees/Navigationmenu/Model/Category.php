<?php
namespace Magebees\Navigationmenu\Model;

class Category extends \Magento\Framework\Model\AbstractModel
{
	public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
		\Magento\Catalog\Model\Category $category,
array $data = []
    ) {
        $this->_storeManager = $storeManager;
		$this->_category = $category;
		parent::_construct();
    }
	
    public function checkCategoryAvailable($cat_id,$store_id=null,$rootId=null)
    {
        
		if($store_id)
		{
			$current_storeid = $store_id;
		}else{
			  $current_storeid = $this->_storeManager->getStore()->getStoreId();
		}
		$this->_category->setStoreId($current_storeid);
        $this->_category->load($cat_id);
        $allow_cat = '0';
    
    /* Check Category Is Available Or not is_active*/
        if (($this->_category->getIsActive() == "1") && ($this->_category->getIncludeInMenu()=="1")) {
		if($rootId)
		{
		$rootCategoryId = $rootId;
		}else{

		$rootCategoryId = $this->_storeManager->getStore()->getRootCategoryId();
		}
            
		$rootCategoryId =$this->_storeManager->getStore()->getRootCategoryId();
            /*Check Root Category Is available in the Category Path Or not*/
            $pos = strpos($this->_category->getPath(), "/".$rootCategoryId."/");
            if ($pos != '') {
                $allow_cat = '1';
            }
            /* Here If the Current Category is the Root Category then Also it will allow*/
            if ($rootCategoryId == $cat_id) {
                $allow_cat = '1';
            }
        }
        return $allow_cat;
    }
    public function getChildCategoryCount($cat_id,$store_id=null,$rootId=null)
    {
        
	if($store_id)
	{
		$current_storeid = $store_id;
	}else{
		$current_storeid = $this->_storeManager->getStore()->getStoreId();
	}
	if($rootId)
	{
		$rootCategoryId = $rootId;
	}else{
		$rootCategoryId = $this->_storeManager->getStore()->getRootCategoryId();
	}

        $rootpath = $this->_category->setStoreId($current_storeid)->load($rootCategoryId)->getPath();
        $childCats = $this->_category->setStoreId($current_storeid)
                    ->getCollection()
                    ->addAttributeToSelect('*')
                    ->addAttributeToFilter('include_in_menu', ['eq' => 1])
                    ->addAttributeToFilter('is_active', ['eq' => 1])
                    ->addAttributeToFilter('parent_id', ['eq' => $cat_id])
                    ->addAttributeToFilter('path', ["like"=>$rootpath."/"."%"]);
        return count($childCats->getData());
    }
}

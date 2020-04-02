<?php
namespace Magebees\Navigationmenu\Model;
class Product extends \Magento\Framework\Model\AbstractModel
{
	
	public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
		\Magento\Catalog\Model\Product $product,
array $data = []
    ) {
        $this->_storeManager = $storeManager;
		$this->_product = $product;
		parent::_construct();
    }
    public function checkProductavailable($product_id,$store_id=null)
    {
    if($store_id)
	{
		$current_storeid = $store_id;
	}else{
	      $current_storeid = $this->_storeManager->getStore()->getStoreId();
	}       
	$_current_store = $this->_storeManager->getStore($store_id);

 		$this->_product->setStoreId($current_storeid);
        $this->_product->load($product_id);
        $pro_webiste = $this->_product->getWebsiteIds();
        $website_id = $_current_store->getWebsite()->getId();
        $allow_pro = '0';
    /* Check Product is Enable Or Disable
    Check the Product Visibility is not Visible Individually
	*/
    
        if (($this->_product->getStatus() == "1") && ($this->_product->getVisibility() != "1")) {
            foreach ($pro_webiste as $key => $value) :
                if ($value == $website_id) {
                    $allow_pro = '1';
                }
            endforeach;
        }
        return $allow_pro;
    }
}

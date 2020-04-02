<?php
namespace Magebees\Products\Model\Convert\Adapter;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\Product;

 class GroupedProduct{
 	protected $resource;
	protected $filesystem;
	protected $productManager;
	const SKU_QTY_DELIMITER = '=';
	protected $LinksData;
	protected $simple_error=array();
	protected $linkReData=array();
	protected $helper;
	protected $eavAttribute;
	
    public function __construct(
    	\Magento\Framework\App\ResourceConnection $resource,
		\Magento\Catalog\Model\ProductFactory $ProductFactory,
		Filesystem $filesystem,
		\Magento\Catalog\Model\Product $Product,
		\Magento\GroupedImportExport\Model\Import\Product\Type\Grouped\Links $LinksData,
		\Magebees\Products\Helper\Data $helper,
		\Magento\Eav\Model\ResourceModel\Entity\Attribute $eavAttribute
    ) {
    	 $this->resource = $resource;
		 $this->productManager = $ProductFactory;
		 $this->filesystem = $filesystem;
		 $this->Product = $Product;
		 $this->LinksData = $LinksData;
		 $this->helper = $helper;
		 $this->eavAttribute = $eavAttribute;
    }
	public function GroupedProductData($ProcuctData, $ProductAttributeData, $ProductImageGallery, $ProductStockdata, $ProductSupperAttribute)
	{
		$ProcuctData['type'] = "grouped";
		$updateOnly = false;
		$storeObj = $this->helper->getObjectManager()->create('\Magento\Store\Model\StoreManagerInterface')->getStore($ProcuctData['store']);
		$storeId = $storeObj->getId();
		$xyz=0;
		if($productIdupdate = $this->Product->loadByAttribute('sku', $ProcuctData['sku'])) 
		{
			$xyz=1;
			$productIdupdate = $this->productManager->create()->setStoreId($storeId)->load($productIdupdate->getId());
			$SetProductData = $productIdupdate;
		} else {
			$SetProductData = $this->productManager->create();
		}
		if ($updateOnly == false) {
			$imagePath = $this->helper->getMediaImportDirPath();
			if(empty($ProductAttributeData['url_key'])) {
			unset($ProductAttributeData['url_key']);
			} else {
				$surl_key=strtolower($ProductAttributeData['url_key']);
				$new_urlKey=str_replace(' ', '-', $surl_key);
				$urlrewrite = $this->helper->checkUrlKeyExists($ProcuctData['store_id'], $new_urlKey);
				if ($urlrewrite->getId()) {
					for ($iUrlKey = 0; $iUrlKey <= 10; $iUrlKey++) {
						$keyToken = $iUrlKey + 1;
						$freshUrlKey = $new_urlKey . '-' . $keyToken;
						$urlrewriteCheck = $this->helper->checkUrlKeyExists($ProcuctData['store_id'], $freshUrlKey);
						if (!$urlrewriteCheck->getId()) {
							break;
						}
					}
					$ProductAttributeData['url_key'] = $freshUrlKey;
					$ProductAttributeData['url_path'] = $freshUrlKey;
				}
			}
			if(empty($ProductAttributeData['url_path'])) { unset($ProductAttributeData['url_path']); }
			
			$SetProductData->setSku($ProcuctData['sku']);
			$SetProductData->setStoreId($storeId);
			if ($storeId != 0) {
	            $websiteIds = $SetProductData->getWebsiteIds();
	            if (!is_array($websiteIds)) {
	                $websiteIds = array();
	            }
	            if (!in_array($storeObj->getWebsiteId(), $websiteIds)) {
	                $websiteIds[] = $storeObj->getWebsiteId();
	            }
	            $SetProductData->setWebsiteIds($websiteIds);
	        }
	        if (isset($ProcuctData['websites'])) {
	            $websiteIds = $SetProductData->getWebsiteIds();
	            if (!is_array($websiteIds)) {
	                $websiteIds = array();
	            }
	            $websiteCodes = $ProcuctData['websites'];
	            foreach ($websiteCodes as $websiteCode) {
	                try {
	                    $website = $this->helper->getObjectManager()->create('\Magento\Store\Model\StoreManagerInterface')->getWebsite(trim($websiteCode));
	                    if (!in_array($website->getId(), $websiteIds)) {
	                        $websiteIds[] = $website->getId();
	                    }
	                } catch (\Exception $e) {
					}
	            }
	            $SetProductData->setWebsiteIds($websiteIds);
	            unset($websiteIds);
	        }

			if(isset($ProcuctData['name'])) { $SetProductData->setName($ProcuctData['name']); }
			if(isset($ProcuctData['attribute_set'])) { $SetProductData->setAttributeSetId($ProcuctData['attribute_set']); }
			if(isset($ProcuctData['type'])) { $SetProductData->setTypeId($ProcuctData['type']); }
			if(isset($ProcuctData['category_ids'])) { $SetProductData->setCategoryIds($ProcuctData['category_ids']); }
			if(isset($ProcuctData['status'])) { $SetProductData->setStatus($ProcuctData['status']); }
			if(isset($ProcuctData['weight'])) { $SetProductData->setWeight($ProcuctData['weight']); }
			if(isset($ProcuctData['price'])) { $SetProductData->setPrice($ProcuctData['price']); }
			if(isset($ProcuctData['visibility'])) { $SetProductData->setVisibility($ProcuctData['visibility']); }
			if(isset($ProcuctData['tax_class_id'])) { $SetProductData->setTaxClassId($ProcuctData['tax_class_id']); }
			if(isset($ProcuctData['special_price'])) { $SetProductData->setSpecialPrice($ProcuctData['special_price']); }
			if(isset($ProcuctData['description'])) { $SetProductData->setDescription($ProcuctData['description']); }
			if(isset($ProcuctData['short_description'])) { $SetProductData->setShortDescription($ProcuctData['short_description']); }
			if(isset($ProductAttributeData['special_from_date'])) { $SetProductData->setSpecialFromDate($ProductAttributeData['special_from_date']); }
			if(isset($ProductAttributeData['news_from_date'])) { $SetProductData->setNewsFromDate($ProductAttributeData['news_from_date']); }
			if(isset($ProductAttributeData['special_to_date'])) { $SetProductData->setSpecialToDate($ProductAttributeData['special_to_date']); }
			if(isset($ProductAttributeData['news_to_date'])) { $SetProductData->setNewsToDate($ProductAttributeData['news_to_date']); }
			$SetProductData->addData($ProductAttributeData);
			if( trim($ProductImageGallery['gallery']) != "" || 
				trim($ProductImageGallery['image']) != "" && 
				trim($ProductImageGallery['small_image']) != "" && 
				trim($ProductImageGallery['thumbnail']) != "" ) 
			{
				$_productImages = array(
				'media_gallery' => ($ProductImageGallery['gallery']!="") ? $ProductImageGallery['gallery'] : 'no_selection',
				'image'       	=> ($ProductImageGallery['image']!="") ? $ProductImageGallery['image'] : 'no_selection',
				'small_image'  	=> ($ProductImageGallery['small_image']!="") ? $ProductImageGallery['small_image'] : 'no_selection',
				'thumbnail'    	=> ($ProductImageGallery['thumbnail']!="") ? $ProductImageGallery['thumbnail'] : 'no_selection',
				'swatch_image' 	=> ($ProductImageGallery['swatch_image']!="") ? $ProductImageGallery['swatch_image'] : 'no_selection'
				);
				$imageArray = array();
				foreach($_productImages as $columnName => $imageName) {
					$imageArray = $this->helper->assignProductImage($imageName, $columnName, $imageArray);
				}
				foreach ($imageArray as $ImageFile => $imageColumns) {
					if($ImageFile != "no_selection") {
						$SetProductData->addImageToMediaGallery($imagePath . $ImageFile, $imageColumns, false, false);
					} else {
						foreach( $imageColumns as $mediaAttribute ) {
							$SetProductData->setData($mediaAttribute, 'no_selection');
						}
					}
				}
			}
			if(!isset($ProductStockdata['qty']) || $ProductStockdata['qty'] == "" || $ProductStockdata['qty'] == null){
				unset($ProductStockdata['qty']);
			}
			$SetProductData->setStockData($ProductStockdata);
			if(isset($ProductSupperAttribute['cws_tier_price'])) { 
				if($ProductSupperAttribute['cws_tier_price']!=""){ $SetProductData->setTierPrice($ProductSupperAttribute['cws_tier_price']); }
			}
			try{
				$SetProductData->save();
			} catch (\Exception $e) {
				array_push($this->simple_error,array('txt'=>$e->getMessage(),'product_sku'=>$ProcuctData['sku']));
				return $this->simple_error;
			}

			if (isset($ProductSupperAttribute['grouped_product_sku']) && $ProductSupperAttribute['grouped_product_sku'] != "") {
					$attributes = $this->LinksData->getAttributes();
					$linksData = [
	                'product_ids' => [],
	                'attr_product_ids' => [],
	                'position' => [],
	                'qty' => [],
	                'relation' => []
	            ];
				$associatedSkusQty = $ProductSupperAttribute['grouped_product_sku'];
				$associatedSkusAndQtyPairs = explode(",", $associatedSkusQty);
				$position = 0;
				 foreach ($associatedSkusAndQtyPairs as $associatedSkuAndQty) {
					++$position;
					$associatedSkuAndQty = explode(self::SKU_QTY_DELIMITER, $associatedSkuAndQty);
					$associatedSku = isset($associatedSkuAndQty[0]) ? trim($associatedSkuAndQty[0]) : null;
					
					$linkedProductId = $this->Product->getIdBySku( $associatedSku );
					$productId = $SetProductData->getId();
					$linksData['product_ids'][$productId] = true;
					$linksData['relation'][] = ['parent_id' => $productId, 'child_id' => $linkedProductId];
					$qty = empty($associatedSkuAndQty[1]) ? 0 : trim($associatedSkuAndQty[1]);
					$linksData['attr_product_ids'][$productId] = true;
					$linksData['position']["{$productId} {$linkedProductId}"] = [
						'product_link_attribute_id' => $attributes['position']['id'],
						'value' => $position
					];
					if ($qty) {
						$linksData['attr_product_ids'][$productId] = true;
						$linksData['qty']["{$productId} {$linkedProductId}"] = [
							'product_link_attribute_id' => $attributes['qty']['id'],
							'value' => $qty
						];
					}
				 }
				$this->LinksData->saveLinksData($linksData);
				try{
				} catch (\Exception $e) {
					array_push($this->simple_error,array('txt'=>$e->getMessage(),'product_sku'=>$ProcuctData['sku']));
				}
				
			}
			$ReProductData = array();
			$UpProductData = array();
			$csSellProductData = array();
			if($ProductSupperAttribute['related_product_sku']!=""){ 
				$ReProductData = $this->helper->AssignReCsUpProduct($ProductSupperAttribute['related_product_sku'],$ProcuctData['sku'],'related');
			}
			if($ProductSupperAttribute['upsell_product_sku']!=""){
				$UpProductData = $this->helper->AssignReCsUpProduct($ProductSupperAttribute['upsell_product_sku'],$ProcuctData['sku'],'upsell');
			}
			if($ProductSupperAttribute['crosssell_product_sku']!=""){
				$csSellProductData = $this->helper->AssignReCsUpProduct($ProductSupperAttribute['crosssell_product_sku'],$ProcuctData['sku'],'crosssell');
			}
			if(!empty($ReProductData) || !empty($UpProductData) || !empty($csSellProductData)) {
				$allProductLinks = array_merge($ReProductData, $UpProductData, $csSellProductData);
				$SetProductData->setProductLinks($allProductLinks)->save();
			}
			if($SetProductData->getStoreId()!=0)
			{
				$attributeId1 = $this->eavAttribute->getIdByCode('catalog_product', 'image');
				$attributeId2 = $this->eavAttribute->getIdByCode('catalog_product', 'small_image');
				$attributeId3 = $this->eavAttribute->getIdByCode('catalog_product', 'thumbnail');
				$attribute_array=array($attributeId1,$attributeId2,$attributeId3);
				
				$connection = $this->resource->getConnection();
				$catTable = $this->resource->getTableName('catalog_product_entity_varchar');
				foreach($attribute_array as $a){	
					$sql = "SELECT * FROM ".$catTable." WHERE entity_id=".$SetProductData->getId()." AND attribute_id=".$a; 	
					$result = $connection->fetchAll($sql);
					$count=0;
					if($result!=''){
						foreach($result as $r){
				 			if($r['store_id']!=0){
				 				if($r['value']!='no_selection'){
				 					if($count==0){	
				 						$sql = "UPDATE ".$catTable." SET `store_id`='0' WHERE value_id=".$r['value_id'];
				 					}else{
						 				$sql = "DELETE FROM ".$catTable." WHERE value_id=".$r['value_id'];
						 			}
				 				}else{
				 					$sql = "DELETE FROM ".$catTable." WHERE value_id=".$r['value_id'];
				 				}	
				 				$connection->query($sql);
			 	 			}else{	
			 	 				$count=$count+1;
			 	 			}
						}
					}
				}
			}
	  	}
	    return $this->simple_error;
	}
}
<?php
namespace Magebees\Products\Model\Convert\Adapter;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\Product;
use Magento\Eav\Model\ResourceModel\Entity\Attribute;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
 
 class ConfigurableProduct{
 	protected $resource;
	protected $filesystem;		
	protected $productManager;
	protected $simple_error = array();
	protected $linkReData = array();
	protected $eavAttribute;
	protected $ProductRepositoryInterface;
	protected $ProductMetadataInterface;
	
    public function __construct(
    	\Magento\Framework\App\ResourceConnection $resource,
		\Magento\Catalog\Model\ProductFactory $ProductFactory,
		Filesystem $filesystem,
		\Magento\Catalog\Model\Product $Product,
		\Magento\Eav\Model\ResourceModel\Entity\Attribute $Attribute,
		\Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable $ConfigurableProduct,
		\Magento\Catalog\Model\ResourceModel\Product\Collection $ProductCollection,
		\Magebees\Products\Helper\Data $helper,
		\Magento\Catalog\Api\ProductRepositoryInterface $ProductRepositoryInterface,
		\Magento\Framework\App\ProductMetadataInterface $ProductMetadataInterface,
		\Magento\Eav\Model\ResourceModel\Entity\Attribute $eavAttribute
		
    ) {
    	 $this->resource = $resource;
		 $this->productManager = $ProductFactory;
		 $this->filesystem = $filesystem;
		 $this->Product = $Product;
		 $this->Attribute = $Attribute;
		 $this->ConfigurableProduct = $ConfigurableProduct;
		 $this->ProductCollection = $ProductCollection;
		 $this->helper = $helper;
		 $this->ProductRepositoryInterface = $ProductRepositoryInterface;
		 $this->ProductMetadataInterface = $ProductMetadataInterface;
		 $this->eavAttribute = $eavAttribute;
    }
	public function ConfigurableProductData($ProcuctData,$ProductAttributeData,$ProductImageGallery,$ProductStockdata,$ProductSupperAttribute,$ProductCustomOption)
	{
		$ProcuctData['type'] = "configurable";
		$updateOnly = false;
		$storeObj = $this->helper->getObjectManager()->create('\Magento\Store\Model\StoreManagerInterface')->getStore($ProcuctData['store']);
		$storeId = $storeObj->getId();
		$xyz=0;
		if($productIdupdate = $this->Product->loadByAttribute('sku', $ProcuctData['sku'])) 
		{
			$xyz=1;
			$productIdupdate = $this->productManager->create()->setStoreId($storeId)->load($productIdupdate->getId());
			$SetProductData = $productIdupdate;
		}else {
			$SetProductData = $this->productManager->create();
			$new = true;
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
			if(empty($ProductAttributeData['url_path'])) {unset($ProductAttributeData['url_path']);}
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
					'media_gallery'     => ($ProductImageGallery['gallery']!="") ? $ProductImageGallery['gallery'] : 'no_selection',
					'image'       		=> ($ProductImageGallery['image']!="") ? $ProductImageGallery['image'] : 'no_selection',
					'small_image'       => ($ProductImageGallery['small_image']!="") ? $ProductImageGallery['small_image'] : 'no_selection',
					'thumbnail'       	=> ($ProductImageGallery['thumbnail']!="") ? $ProductImageGallery['thumbnail'] : 'no_selection',
					'swatch_image'      => ($ProductImageGallery['swatch_image']!="") ? $ProductImageGallery['swatch_image'] : 'no_selection'
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
			try {
				if($ProductSupperAttribute['child_products_sku'] != ""){
					$ProductSupperAttribute['child_products_sku'];
					if($ProcuctData['type'] == "configurable"){

						$SetProductData->setCanSaveConfigurableAttributes(true);
						$SetProductData->setCanSaveCustomOptions(true);		 
						$cProductTypeInstance = $SetProductData->getTypeInstance();

						$attribute_ids = $this->getConfigAttributesId($ProductAttributeData['used_attribute']);

						$cProductTypeInstance->setUsedProductAttributeIds($attribute_ids,$SetProductData);
						$attributes_array = $cProductTypeInstance->getConfigurableAttributesAsArray($SetProductData);
						foreach($attributes_array as $key => $attribute_array) 
						{
							$attributes_array[$key]['use_default'] = 1;
							$attributes_array[$key]['position'] = 0;
							if (isset($attribute_array['frontend_label']))
							{
								$attributes_array[$key]['label'] = $attribute_array['frontend_label'];
							}
							else {
								$attributes_array[$key]['label'] = $attribute_array['attribute_code'];
							}
						}
						$SetProductData->setConfigurableAttributesData($attributes_array);
						if(!isset($ProductStockdata['qty']) || $ProductStockdata['qty'] == "" || $ProductStockdata['qty'] == null){
							unset($ProductStockdata['qty']);
						}
						$SetProductData->setStockData($ProductStockdata);
						$SetProductData->save();
						$ConfigurableId = $ProcuctData['sku'];
						$this->SimpleAssociatedWithConfigureable($ProductSupperAttribute['child_products_sku'],$ConfigurableId);
					}
				}
				if(!empty($ProductCustomOption))
				{
					$SetProductData->save();
					$productOpt = $this->ProductRepositoryInterface->getById($SetProductData->getId());
					if($productOpt->getOptions() != ""){
	       				foreach ($productOpt->getOptions() as $opt){
	                   		$opt->delete();
	               		}
	       				$productOpt->setHasOptions(0)->save();
	  				}
					$version = $this->ProductMetadataInterface->getVersion();
					if($version < '2.1.0'){
						$SetProductData->save();
						$productId = $SetProductData->getId();
							foreach ($ProductCustomOption as $arrayOption) {
								$SetProductData->setHasOptions(1);
								$SetProductData->getResource()->save($SetProductData);
								$option = $this->helper->getObjectManager()->create('\Magento\Catalog\Model\Product\Option')
										->setProductId($productId)
										->setStoreId($SetProductData->getStoreId())
										->addData($arrayOption);
								$option->save();
								$SetProductData->addOption($option);
							}
					}else{
						$SetProductData->save();
						$SetProductData->setHasOptions(true);
						$SetProductData->setCanSaveCustomOptions(true);
						$productId = $SetProductData->getId();
							foreach ($ProductCustomOption as $arrayOption) {
								$option = $this->helper->getObjectManager()->create('\Magento\Catalog\Model\Product\Option')
										->setProductId($productId)
										->setStoreId($SetProductData->getStoreId())
										->addData($arrayOption);
								$SetProductData->addOption($option);
							}
					}
				}
				else{
					$productOption = $this->getProductOptions($SetProductData);	
				}
			} catch (\Exception $e) {
				array_push($this->simple_error,array('txt'=>$e->getMessage(),'product_sku'=>$ProcuctData['sku']));
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
			try{
				if(!empty($ProductCustomOption)){
					$SetProductData->save();
				}else{
					$SetProductData->setHasOptions(true);
	        		$SetProductData->setCanSaveCustomOptions(true);
					$SetProductData->save();
				}
			} catch (\Exception $e) {
				array_push($this->simple_error,array('txt'=>$e->getMessage(),'product_sku'=>$ProcuctData['sku']));
			}
			if($SetProductData->getStoreId()!=0){
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
	public function SetDataTosimpleProducts($ProductsFieldArray){
	    $Atdata = explode(',', $ProductsFieldArray);
		foreach($Atdata as $data){
		if(!empty($data) && $data !="")
			$pdata = explode('=', $data);
			if(isset($pdata[1])) {
				$AttributeCol = $this->Product->getResource()->getAttribute($pdata[1]);
				$OptionId = $AttributeCol->getSource()->getOptionId($pdata[2]);
				$ProductId = $this->Product->getResource()->getIdBySku($pdata[0]);
				$product = $this->Product->load($ProductId);
				$product->setData($pdata[1] , $OptionId);
				$product->getResource()->saveAttribute($product, $pdata[1]);
			}
		}
	}
	public function SimpleAssociatedWithConfigureable($childProduct, $configurableProduct){
				if($childProduct!="") 
	{
			$cpId = $this->Product->getResource()->getIdBySku($configurableProduct);
			$Products_sku = explode(',',$childProduct);
			$ProductId = array();
			foreach($Products_sku as $sku){
				if($sku){
					if(!$this->Product->getResource()->getIdBySku($sku)) {
							array_push($this->simple_error,array('txt'=>'The Following sku: "' . $sku. '" does NOT exist and cannot be used to create a configurable product. However you do have it listed as a sku in your "associated" column','product_sku'=>$sku));
					} else {
						$ProductId[] = $this->Product->getResource()->getIdBySku($sku);
						$ProductId = array_unique($ProductId);
					}
				}
			}
			$productModel = $this->Product->load($cpId);
			$this->ConfigurableProduct->saveProducts($productModel,$ProductId);
		} else {
			array_push($this->simple_error,array('txt'=>"The column is empty and must contain the sku's in a comma delimited from that you want to associate to a configurable product",'product_sku'=>$sku));
		}
	}
	public function getConfigAttributesId($AttributesCode){
		if($AttributesCode!="") {
			$Codes = explode(',', $AttributesCode);
			$AttributeId = array();
			foreach($Codes as $Code){ 
				if($this->Attribute->getIdByCode('catalog_product',trim($Code))) {
					$AttributeId[] = $this->Attribute->getIdByCode('catalog_product',trim($Code));
				} else {
					array_push($this->simple_error,array('txt'=>'The column contains the following attribute "'.$Code.'" and it does NOT exist in the install',''));
				}
			}	
			return $AttributeId;
		} else {
			array_push($this->simple_error,array('txt'=>'The column is empty and should contain the attribute names you want to use to create a configurable product e.g "color,size"',''));
		}
	}
	public function getProductOptions($productObj)
    {
        $customOptions = $this->helper->getObjectManager()->get('Magento\Catalog\Model\Product\Option')->getProductOptionCollection($productObj);
        $options = [];
        if($customOptions->count() > 0) {
            foreach($customOptions as $customOption) {
                $optionsValue = [];
                $customOptionValues = $this->helper->getObjectManager()->get('Magento\Catalog\Model\Product\Option\Value')->getValuesCollection($customOption);
                if($customOptionValues->count() > 0) {
                    foreach($customOptionValues as $customOptionValue) {
                        $optionsValue[] = array(
                            'record_id' => $customOptionValue->getRecordId(),
                            'title' => $customOptionValue->getTitle(),
                            'price' => $customOptionValue->getPrice(),
                            'price_type' => $customOptionValue->getPriceType(),
                            'sort_order' => $customOptionValue->getSortOrder(),
                            'sku' => $customOptionValue->getSku(),
                            'is_delete' => 0,
                        );                
                    }
                }
                $max_characters=$customOption['max_characters']; 
                $file_extension=$customOption['file_extension'];
                $image_size_x=$customOption['image_size_x'];
                $image_size_y=$customOption['image_size_y'];
                $sku = $customOption->getSku();
                $title = $customOption->getTitle();
                $type = $customOption->getType();
                $price = $customOption->getPrice();
                $price_type = $customOption->getPriceType();
                $record_id = $customOption->getRecordId();
                $options[] = array(
                    'sort_order' => $customOption->getSortOrder(),
                    'title' => $title,
                    'price_type' => $price_type,
                    'price' => $price,
                    'type' => $type,
                    'sku' => $sku,
                    'max_characters' => $max_characters,
                    'file_extension' => $file_extension,
                    'image_size_x' => $image_size_x,
                    'image_size_y' => $image_size_y,
                    'values' => $optionsValue,
                    'is_require' => 1,
                );
            }
        }
        return $options;        
    }
    public function addProductCustomOptions($product, $productOption) 
    {
        $product->setHasOptions(true);
        $product->setCanSaveCustomOptions(true);
        if(empty($productOption)){
	        foreach ($productOption as $arrayOption) {
	            $option = $this->helper->getObjectManager()->create('\Magento\Catalog\Model\Product\Option')
	                    ->setProductId($product->getId())
	                    ->setStoreId($product->getStoreId())
	                    ->addData($arrayOption);
	            $option->save();
	            $product->addOption($option);
	        }
	    }
        $product->setHasOptions(true);
        $product->save();
    }
}
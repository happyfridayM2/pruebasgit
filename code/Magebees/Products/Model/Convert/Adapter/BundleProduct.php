<?php
namespace Magebees\Products\Model\Convert\Adapter;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\Product;
 
class BundleProduct{
	protected $resource;
	protected $filesystem;		
	protected $productManager;
	protected $simple_error = array();
	protected $linkReData = array();
	protected $eavAttribute;
	
    public function __construct(
    	\Magento\Framework\App\ResourceConnection $resource,
		\Magento\Catalog\Model\ProductFactory $ProductFactory,
		Filesystem $filesystem,
		\Magento\Framework\Registry $registry,
		\Magento\Catalog\Model\Product $Product,
		\Magebees\Products\Helper\Data $helper,
		\Magento\Eav\Model\ResourceModel\Entity\Attribute $eavAttribute
    ) {
    	 $this->resource = $resource;	
		 $this->productManager = $ProductFactory;
		 $this->filesystem = $filesystem;
		 $this->registry = $registry;
		 $this->Product = $Product;
		 $this->helper = $helper;
		 $this->eavAttribute = $eavAttribute;
    }
    
	public function BundleProductData($ProcuctData,$ProductAttributeData,$ProductImageGallery,$ProductStockdata,$ProductSupperAttribute,$ProductCustomOption)
	{
		$ProcuctData['type'] = "bundle";
		
		$updateOnly = false;
		$storeObj = $this->helper->getObjectManager()->create('\Magento\Store\Model\StoreManagerInterface')->getStore($ProcuctData['store']);
		$storeId = $storeObj->getId();
		$xyz=0;
		if($productIdupdate = $this->Product->loadByAttribute('sku', $ProcuctData['sku'])) 
		{
			$xyz=1;	
			$productIdupdate = $this->productManager->create()->setStoreId($storeId)->load($productIdupdate->getId());
			$SetProductData = $productIdupdate;
			$new = false;
		} else {
			$SetProductData = $this->productManager->create();
			$new = true;
		}
		if ($updateOnly == false) 
		{
			$imagePath = $this->helper->getMediaImportDirPath();
			if($this->Product->loadByAttribute('sku', $ProcuctData['sku'])) {
				$xyz=1;
				$productIdupdate = $this->Product->loadByAttribute('sku', $ProcuctData['sku']);
				$productIdupdate = $this->productManager->create()->setStoreId($storeId)->load($productIdupdate->getId());
				$SetProductData = $productIdupdate;
			} else {
				$SetProductData = $this->productManager->create();
			}
			
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
			if(empty($ProductAttributeData['url_path'])) { unset($ProductAttributeData['url_path']);}
			
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
				if(!isset($ProductStockdata['qty']) || $ProductStockdata['qty'] == "" || $ProductStockdata['qty'] == null){
					unset($ProductStockdata['qty']);
				}
				$SetProductData->setStockData($ProductStockdata);
				if(!empty($ProductCustomOption) && $ProductAttributeData['price_type'] != "dynamic"){
					$SetProductData->save();
					$productRepositoryopt = $this->helper->getObjectManager()->create('Magento\Catalog\Api\ProductRepositoryInterface');
					$productOpt = $productRepositoryopt->getById($SetProductData->getId());
					if($productOpt->getOptions() != ""){
	       				foreach ($productOpt->getOptions() as $opt){
	                   		$opt->delete();
	               		}
	       				$productOpt->setHasOptions(0)->save();
	  				}
					$productMetadata = $this->helper->getObjectManager()->get('Magento\Framework\App\ProductMetadataInterface');
					$version = $productMetadata->getVersion();
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
			if (isset( $ProductSupperAttribute['bundle_product_options'] ) && $ProductSupperAttribute['bundle_product_options'] != "" ) {
				try {
					if(isset($ProductAttributeData['price_type'])){
						$SetProductData->setPriceType($this->getPriceType($ProductAttributeData['price_type']));
					}else{
						$SetProductData->setPriceType(1);
					}
					if(isset($ProductAttributeData['price_view'])){
						$SetProductData->setPriceView($this->getPriceView($ProductAttributeData['price_view']));
					}else{
						$SetProductData->setPriceView(0);
					}
					if(isset($ProductAttributeData['shipment_type'])){
						$SetProductData->setShipmentType($this->getShipmentType($ProductAttributeData['shipment_type']));
					}else{
						$SetProductData->setShipmentType(1);
					}
					if(isset($ProductAttributeData['weight']) && $ProductAttributeData['weight']!=''){$SetProductData->setWeightType(1);}
				} catch (\Exception $e) {
					array_push($this->simple_error,array('txt'=>$e->getMessage(),'product_sku'=>$ProcuctData['sku']));
				}
				$productRepository = $this->helper->getObjectManager()->create('\Magento\Catalog\Api\ProductRepositoryInterface');
				if($xyz==0 && empty($ProductCustomOption)){
					$SetProductData->save();	
				}
				$product = $productRepository->get($ProcuctData['sku'], true);
				$option_str = $ProductAttributeData['bundle_product_options'];
				$single_bundle_option = explode("|",$option_str);
				$optionRawData = array();
				$selectionRawData = array();
				for($z=0;$z<count($single_bundle_option);$z++)
				{
					$single_bundle_option_data = explode(":",$single_bundle_option[$z]);
					$single_bundle_option_title_value = explode(",",$single_bundle_option_data[0]);
						$optionRawData[$z] = array(
						  'title' => $single_bundle_option_title_value[0],
						  'type' => $single_bundle_option_title_value[1],
						  'required' => $single_bundle_option_title_value[2],
						  'position' => $single_bundle_option_title_value[3],
						  'delete' => '',
						);
					$single_bundle_option_selection_value = explode("!",$single_bundle_option_data[1]);
					foreach($single_bundle_option_selection_value as $singleBundleOptionData){	
						$d = explode(',',$singleBundleOptionData);
						$product_id = $this->helper->getObjectManager()->get('Magento\Catalog\Model\Product')->getIdBySku($d[0]);
						if($product_id){
							$ptype = "";
							$pprice = "";
							if(isset($d[5])){$ptype = $d[5];}else{$ptype = '';}
							if(isset($d[4])){$pprice = $d[4];}else{$pprice = '';}
							$selectionRawData[$z][] = array(
							  'sku' => $d[0],
							  'qty' => $d[1],
							  'position' => $d[3],
							  'is_default' => '',
							  'price_type' => $ptype,
							  'price' => $pprice,
							  'can_change_quantity' => $d[2],
							  'delete' => ''
							);	
						}else{
							$message='Product does not exist. SKU :'.$d[0];
							array_push($this->simple_error,array('txt'=>$message,'product_sku'=>$ProcuctData['sku']));
							return $this->simple_error;
						}
					}
					$optionRawData[$z]['product_links'] = $selectionRawData[$z];
				}
				$options = array();
				foreach ($optionRawData as $key => $optionData) {
					if (!(bool)$optionData['delete']) {
						$option = $this->helper->getObjectManager()->create('Magento\Bundle\Api\Data\OptionInterfaceFactory')->create(['data' => $optionData]);

						$option->setSku($product->getSku());
						$option->setOptionId(null);
							$links = array();
							foreach ($optionData['product_links'] as $linkData) {
								if (!(bool)$linkData['delete']) {
									$link = $this->helper->getObjectManager()->create('Magento\Bundle\Api\Data\LinkInterfaceFactory')->create(['data' => $linkData]);
									$linkProduct = $productRepository->get($linkData['sku']);
									$link->setSku($linkProduct->getSku());
									$links[] = $link;
								}
							}
							$option->setProductLinks($links);
							$options[] = $option;
					}
				}
				try {
					$extension = $product->getExtensionAttributes();
					$extension->setBundleProductOptions($options);
					$productRepository->save($product, true);
				} catch (\Exception $e) {
					array_push($this->simple_error,array('txt'=>$e->getMessage(),'product_sku'=>$ProcuctData['sku']));
				}
			}
			if(isset($ProductSupperAttribute['cws_tier_price'])) {
				if($ProductSupperAttribute['cws_tier_price']!=""){ 
					$SetProductData->setTierPrice($ProductSupperAttribute['cws_tier_price']); 
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
	
	public function getPriceView($txt){
		if(strtolower($txt) == 'price range'){
			return '0';
		}else{
			return '1';
		}
	} 

	public function getPriceType($txt){
		if(strtolower($txt) == 'fixed'){
			return '1';
		}else{
			return '0';
		}
	} 
	public function getShipmentType($txt){
		if(strtolower($txt) == 'separately'){
			return '1';
		}else{
			return '0';
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
        if(empty($productOption))
        {	
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
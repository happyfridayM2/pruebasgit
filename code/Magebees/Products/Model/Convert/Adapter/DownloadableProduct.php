<?php
namespace Magebees\Products\Model\Convert\Adapter;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\Product;
use Magento\Downloadable\Model\Product\Type;
use Magento\Framework\App\ResourceConnection;

class DownloadableProduct{
	protected $resource;
	protected $filesystem;		
	protected $productManager;
	protected $linkReData = array();
	protected $simple_error = array();
	protected $helper;
	protected $eavAttribute;
	
    public function __construct(
		ResourceConnection $resource,
		\Magento\Catalog\Model\ProductFactory $ProductFactory,
		Filesystem $filesystem,
		\Magento\Catalog\Model\Product $Product,
		\Magento\Downloadable\Model\Product\Type $DownloadableProductType,
		\Magebees\Products\Helper\Data $helper,
		\Magento\Eav\Model\ResourceModel\Entity\Attribute $eavAttribute
    ) {
		 $this->resource = $resource;
		 $this->productManager = $ProductFactory;
		 $this->filesystem = $filesystem;
		 $this->Product = $Product;
		 $this->DownloadableProductType = $DownloadableProductType;
		 $this->helper = $helper;
		 $this->eavAttribute = $eavAttribute;
    }	
	protected function getConnection($data){
		$this->connection = $this->resource->getConnection($data);
		return $this->connection;
	}
	public function DownloadableProductData($ProcuctData,$ProductAttributeData,$ProductImageGallery,$ProductStockdata,$ProductSupperAttribute,$ProductCustomOption)
	{	
		$ProcuctData['type'] = "downloadable";
		$updateOnly = false;
		$xyz=0;
		if($productIdupdate = $this->Product->loadByAttribute('sku', $ProcuctData['sku'])) {
			$xyz=1;	
			$SetProductData = $productIdupdate;
			$new = false;
		} else {
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
			if(empty($ProductAttributeData['url_path'])) { unset($ProductAttributeData['url_path']); }
			
			$SetProductData->setSku($ProcuctData['sku']);
			$storeObj = $this->helper->getObjectManager()->create('\Magento\Store\Model\StoreManagerInterface')->getStore($ProcuctData['store']);
			$storeId = $storeObj->getId();	
			$SetProductData->setStoreId($storeId);
			if(isset($ProcuctData['name'])) { $SetProductData->setName($ProcuctData['name']); }
			if(isset($ProcuctData['websites'])) { $SetProductData->setWebsiteIds($ProcuctData['websites']); }
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
				'image'			=> ($ProductImageGallery['image']!="") ? $ProductImageGallery['image'] : 'no_selection',
				'small_image'   => ($ProductImageGallery['small_image']!="") ? $ProductImageGallery['small_image'] : 'no_selection',
				'thumbnail'     => ($ProductImageGallery['thumbnail']!="") ? $ProductImageGallery['thumbnail'] : 'no_selection',
				'swatch_image'  => ($ProductImageGallery['swatch_image']!="") ? $ProductImageGallery['swatch_image'] : 'no_selection'
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
			if(!empty($ProductCustomOption)){
				try{
					$SetProductData->save(); 
				}catch(\Exception $e){
					array_push($this->simple_error,array('txt'=>$e->getMessage(),'product_sku'=>$ProcuctData['sku']));
			    	return $this->simple_error;
				}
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
					$SetProductData->setHasOptions(true);
				}
			}
			else{	
				$productOption = $this->getProductOptions($SetProductData);
			}
			try{					
				$SetProductData->save();
			}
			catch(\Exception $e){
				array_push($this->simple_error,array('txt'=>$e->getMessage(),'product_sku'=>$ProcuctData['sku']));
				return $this->simple_error;
			}
			if (isset( $ProductSupperAttribute['downloadable_product_options'] ) && $ProductSupperAttribute['downloadable_product_options'] != "") {
				if(isset($ProductAttributeData['link_title']) && $ProductAttributeData['link_title'] != ""){
					$SetProductData->setLinksTitle($ProductAttributeData['link_title']);
				}else{
					$SetProductData->setLinksTitle("Download");
				}
				if(isset($ProductAttributeData['sample_title']) && $ProductAttributeData['sample_title'] != ""){
					$SetProductData->setSamplesTitle($ProductAttributeData['sample_title']);
				}else{
					$SetProductData->setSamplesTitle("Samples");
				}
				$main_option_array = array();
				$main_temp_array=array();
				$filearrayforimport = array();
				$filenameforsamplearrayforimport = array();
				$SetProductData->setLinksPurchasedSeparately($ProductSupperAttribute['link_can_purchase_separately']);		
				if($xyz!=1)
				{	
					try{
						$SetProductData->save();
					}catch(\Exception $e){
						array_push($this->simple_error,array('txt'=>$e->getMessage(),'product_sku'=>$ProcuctData['sku']));
					}
				}
				if($ProductSupperAttribute['downloadable_product_samples'] != ""){
					$downloadable_product_samples = explode('|',$ProductSupperAttribute['downloadable_product_samples']);
					foreach (array_filter($downloadable_product_samples) as $sample) {
						$sampledata=explode(";",$sample);	
						$sampledata=explode(",",$sampledata[0]);
						if(isset($sampledata[3])){
							$SampleSortOrder = $sampledata[3];
						}else{
							$SampleSortOrder = 0;
						};
						$basic_field=array(
							'product_id' => $SetProductData->getId(),
							'sort_order' => $SampleSortOrder,
							'sample_type' => $sampledata[1],
							'title' => $sampledata[0],
							'store_id' => 0,
							'website_id' => $SetProductData->getStore()->getWebsiteId(),
						);
						$sampleimagename=ltrim($sampledata[2], '/');
						$samplefile = array();
						$_samplefilePath=$sampleimagename;
						$samplefile[] = array(
							'file' => $_samplefilePath,
							'name' => $sampleimagename,
							'status' => 'new'
						);
						if($sampledata[1]=='file'){								
							$basic_field['sample_file']=json_encode($samplefile);
							$basic_field['sample_url']='';
						}else{
							$basic_field['sample_file']='';
							$basic_field['sample_url']=$sampleimagename;
						}
						$linkModel = $this->helper->getObjectManager()->create('Magento\Downloadable\Model\Sample')->setData($basic_field);
						$filePath = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath('import');
						if($sampledata[1]=='file'){
							if (!file_exists($filePath.'/'.$sampleimagename)){
								$d_sku=$SetProductData->getSku();	
								$SetProductData->delete();
								$message= 'File Does Not Exist. SKU: '.$d_sku;
								array_push($this->simple_error,array('txt'=>$message,'product_sku'=>$ProcuctData['sku'],'error_level'=>1));
								return $this->simple_error;
							}
							$filePath1 = "/import";
							$basePath =  $this->helper->getObjectManager()->create('Magento\Downloadable\Model\Sample')->getBasePath();
							$sampleFileName = $this->helper->getObjectManager()->create('Magento\Downloadable\Helper\File')->moveFileFromTmp($filePath1,$basePath,$samplefile);
							try{
								$linkModel->setSampleFile($sampleFileName)->save();	
							}catch(\Exception $e){	
								array_push($this->simple_error,array('txt'=>$e->getMessage(),'product_sku'=>$ProcuctData['sku'],'error_level'=>1));
							}
						}else{
							try{
								$linkModel->save();	
							}catch(\Exception $e){
							array_push($this->simple_error,array('txt'=>$e->getMessage(),'product_sku'=>$ProcuctData['sku'],'error_level'=>1));
							}
						}
					}
				}
				$downloadable_product_main_data = explode('|',$ProductSupperAttribute['downloadable_product_options']); 
				foreach (array_filter($downloadable_product_main_data) as $single) {
					$single_row=explode(";",$single);
					$linkdata=$single_row[0];	
					$linkdata=explode(",",$linkdata);		
					$sampledata=$single_row[1];		
					$sampledata=explode(",",$sampledata);
					$website_id=array($SetProductData->getStore()->getWebsiteId());
					$basic_field=array(
						'product_id' => $SetProductData->getId(),
						'sort_order' => 0,
						'number_of_downloads' => $linkdata[2],
						'is_shareable' => $linkdata[3],
						'link_type' => $linkdata[4],
						'sample_type' => $sampledata[0],
						'use_default_title' => false,
						'title' => $linkdata[0],
						'default_price' => 0,
						'price' => $linkdata[1],
						'store_id' => 0,
						'website_id' => $website_id,
					);		
					$linkimagename=ltrim($linkdata[5], '/');	
					$sampleimagename=ltrim($sampledata[1], '/');
					$linkfile = array();
					$samplefile = array();
					$_highfilePath =$linkimagename;
					$_samplefilePath=$sampleimagename;				
					$samplefile[] = array(
						'file' => $_samplefilePath,
						'name' => $sampleimagename,
						'status' => "new"
					);
					$linkfile[] = array(
						'file' => $_highfilePath,
						'name' => $linkimagename,                
						'status' => "new"
					);			
					if($linkdata[4]=='file'){								
						$basic_field['link_file']=json_encode($linkfile);
						$basic_field['link_url']='';									
					}else{
						$basic_field['link_file']='';
						$basic_field['link_url']=$linkimagename;
					}			
					if($sampledata[0]=='file'){								
						$basic_field['sample_file']=json_encode($samplefile);
						$basic_field['sample_url']='';									
					}else{
						$basic_field['sample_file']='';
						$basic_field['sample_url']=$sampleimagename;
					}		
					$linkModel = $this->helper->getObjectManager()->create('Magento\Downloadable\Model\Link')->setData($basic_field);
					$filePath = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath('import');				
					if($linkdata[4]=='file' || $linkdata[4]==''){
						if (!file_exists($filePath.'/'.$linkimagename)){
							$d_sku=$SetProductData->getSku();	
							$SetProductData->delete();
							$message='File Does Not Exist. SKU: '.$d_sku;
							array_push($this->simple_error,array('txt'=>$message,'product_sku'=>$ProcuctData['sku'],'error_level'=>1));
							return $this->simple_error;
						}
						$filePath1 = "/import";
						$basePath =  $this->helper->getObjectManager()->create('Magento\Downloadable\Model\Link')->getBasePath();
						$linkFileName = $this->helper->getObjectManager()->create('Magento\Downloadable\Helper\File')->moveFileFromTmp($filePath1,$basePath,$linkfile);			
						try{
							$linkModel->setLinkFile($linkFileName)->save();	
						}catch(\Exception $e){		    	
							array_push($this->simple_error,array('txt'=>$e->getMessage(),'product_sku'=>$ProcuctData['sku'],'error_level'=>1));
						}
					}else{
							try{
								$linkModel->save();
							}catch(\Exception $e){
								array_push($this->simple_error,array('txt'=>$e->getMessage(),'product_sku'=>$ProcuctData['sku'],'error_level'=>1));
							}
					}
					if($sampledata[0]=='file'){									
						if (!file_exists($filePath.'/'.$sampleimagename)) {
							$d_sku=$SetProductData->getSku();	
							$SetProductData->delete();
							$message='Sample File Does Not Exist. SKU: '.$d_sku;
							array_push($this->simple_error,array('txt'=>$message,'product_sku'=>$ProcuctData['sku'],'error_level'=>1));
							return $this->simple_error;
						}
						$filePath1 = "/import";
						$basePath =  $$this->helper->getObjectManager()->create('Magento\Downloadable\Model\Link')->getBaseSamplePath();
						$sampleFileName = $this->helper->getObjectManager()->create('Magento\Downloadable\Helper\File')->moveFileFromTmp($filePath1,$basePath,$samplefile);
						try{			
							$linkModel->setSampleFile($sampleFileName)->save();											
						}catch(\Exception $e){
							array_push($this->simple_error,array('txt'=>$e->getMessage(),'product_sku'=>$ProcuctData['sku'],'error_level'=>1));
						}
					}else{
						try{
							$linkModel->save();
						}catch(\Exception $e){
							array_push($this->simple_error,array('txt'=>$e->getMessage(),'product_sku'=>$ProcuctData['sku'],'error_level'=>1));
						}
					}			
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
					$this->addProductCustomOptions($SetProductData, $productOption);
					
				}
			} catch (\Exception $e) {
				array_push($this->simple_error,array('txt'=>$e->getMessage(),'product_sku'=>$ProcuctData['sku']));
			}
	    }
	    return $this->simple_error;
	}
	protected function userCSVDataAsArray( $data )
	{
		return explode( ',', str_replace( " ", " ", $data ) );
	} 
	
	public function TitleOfDownloadableProduct($SetProductData){
		$connection = $this->getConnection('core_write');
		$DownloadableLinkData = $SetProductData->getDownloadableData('link');
		foreach($DownloadableLinkData as $LinkData){
			if(!empty($LinkData)){
				$LinkTitle = $LinkData['title'];
				if($LinkTitle != ""){
					$connection->beginTransaction();
					$_fields = array();
					$_fields['store_id']    =  "0";
					$where = $connection->quoteInto('title =?', $LinkTitle);  
					$connection->update('downloadable_link_title', $_fields, $where);  
					$connection->commit(); 
				}
			}
		}
		$DownloadableSampleData = $SetProductData->getDownloadableData('sample');
		if(!empty($DownloadableSampleData)){
			foreach($DownloadableSampleData as $SampleData){
				if(!empty($SampleData)){
					$SampleTitle = $SampleData['title'];
					if($SampleTitle != ""){
						$connection->beginTransaction();
						$_fields = array();
						$_fields['store_id']    =  "0";
						$where = $connection->quoteInto('title =?', $SampleTitle);  
						$connection->update('downloadable_sample_title', $_fields, $where);
						$connection->commit(); 
					}
				}
			}
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
	    foreach ($productOption as $arrayOption) {
	        $option = $this->helper->getObjectManager()->create('\Magento\Catalog\Model\Product\Option')
	                ->setProductId($product->getId())
	                ->setStoreId($product->getStoreId())
	                ->addData($arrayOption);
	        $option->save();
	        $product->addOption($option);
	    }
        $product->setHasOptions(true);
        $product->save();
    }
}
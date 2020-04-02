<?php
namespace Magebees\Products\Model\Convert\Adapter;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ResourceConnection;
use Magento\Catalog\Model\Product as ModelProduct;
use Magento\Store\Model\Website;

class Importproducts extends \Magento\Framework\Model\AbstractModel
{
    protected $attributes = array();
	protected $error = array();
	protected $resource;
	protected $date;
	protected $eavConfig;
	protected $request;
	protected $productRepository;
	protected $registry;
	protected $storeManager;
    protected $categoryCache = array();
    protected $productMetadata;
    protected $Importlog;
    protected $ProductRepositoryInterface;
    protected $helper;
    protected $imageFields = ['image','swatch_image','small_image','thumbnail','media_gallery','gallery','gallery_label'];
	
	 public function __construct(
		ResourceConnection $resource,
		\Magento\Framework\App\Request\Http $request,
        \Magento\Framework\Locale\FormatInterface $localeFormat,
		\Magento\Framework\Stdlib\DateTime\DateTime $date,
		\Magebees\Products\Model\Convert\Adapter\SimpleProduct $SimpleProduct,
		\Magebees\Products\Model\Convert\Adapter\ConfigurableProduct $ConfigurableProduct,
		\Magebees\Products\Model\Convert\Adapter\GroupedProduct $GroupedProduct,
		\Magebees\Products\Model\Convert\Adapter\VirtualProduct $VirtualProduct,
		\Magebees\Products\Model\Convert\Adapter\BundleProduct $BundleProduct,
		\Magebees\Products\Model\Convert\Adapter\DownloadableProduct $DownloadableProduct,
		\Magento\Catalog\Model\Product $Product,
		\Magento\Store\Model\Website $Website,
		\Magento\Catalog\Model\ProductRepository $productRepository,
		\Magento\Framework\Registry $registry,
		\Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Framework\App\ProductMetadataInterface $productMetadata,
        \Magebees\Products\Model\Importlog $Importlog,
        \Magento\Catalog\Api\ProductRepositoryInterface $ProductRepositoryInterface,
        \Magebees\Products\Helper\Data $helper
    ) {
		 $this->resource = $resource;
         $this->localeFormat = $localeFormat;
		 $this->date = $date;
		 $this->SimpleProduct = $SimpleProduct;
		 $this->ConfigurableProduct = $ConfigurableProduct;
		 $this->GroupedProduct = $GroupedProduct;
		 $this->VirtualProduct = $VirtualProduct;
		 $this->BundleProduct = $BundleProduct;
		 $this->DownloadableProduct = $DownloadableProduct;
		 $this->ProductModel = $Product; 
		 $this->website = $Website;
		 $this->request = $request; 
         $this->eavConfig = $eavConfig;
		 $this->productRepository = $productRepository;
		 $this->registry = $registry;
		 $this->storeManager = $storeManager;
		 $this->productMetadata = $productMetadata;
		 $this->Importlog = $Importlog;
		 $this->ProductRepositoryInterface = $ProductRepositoryInterface;
		 $this->helper = $helper;
    }
  
	public function runImport(array $data)
	{
		$this->error = array();
		$this->saveRow($this->helper->getUnserializeData($data['product_data']));
		if(!empty($this->error) && $this->error != "" && is_array($this->error)){
			foreach($this->error as $e) {
				try {
					if(isset($e['txt'])){
						$this->Importlog->setErrorInformation($e['txt']);
					}
					if(isset($e['error_level'])){
						$this->Importlog->setErrorType($e['error_level']);
					}
					if(isset($e['product_sku'])){
						$this->Importlog->setProductSku($e['product_sku']);
					}
					$this->Importlog->save();
				} catch (\Exception $e) {
					
				}
			}
			return $this->error;
		}
	}
	protected function getConnection($data){
		$this->connection = $this->resource->getConnection($data);
		return $this->connection;
	}
	public function saveRow(array $product)
	{
		if(!array_key_exists("sku",$product)){
			$message = "Your CSV file is not in proper Comma Separated format. Please use Google Sheet to convert it properly.";
			array_push($this->error,array('txt'=>$message,'product_sku'=>'CSV File Related Issue','error_level'=>1));
			return;	
		}
		foreach($product as $key => $value):
			$product[$key] = trim($value);
		endforeach;
		$behavior = $this->request->getParam('behavior');
		if($behavior=='delete'){
			try{
				$product_id = $this->ProductModel->getIdBySku($product['sku']);
				if ($product_id) {
					$this->registry->register('isSecureArea', true);
					$product_obj = $this->productRepository->getById($product_id);
					$this->productRepository->delete($product_obj);
					$this->registry->unregister('isSecureArea');
				}
			} catch (\Exception $e) {
				array_push($this->error,array('txt'=>$e->getMessage(),'product_sku'=>$product['sku']));
			}
			return ;
		}
		$product_id = $this->ProductModel->getIdBySku($product['sku']);
		if(!$product_id) {
			$_requiredFields = array("name","tax_class_id");
			  foreach ($_requiredFields as $field) {
                $attribute = $this->eavConfig->getAttribute(ModelProduct::ENTITY, $field);
                if (!isset($product[$field]) && $attribute && $attribute->getIsRequired()) {
                    $message = "Skip import row, required field ".$field." for new products not defined. Product SKU# ".$product['sku'];
					array_push($this->error,array('txt'=>$message,'product_sku'=>$product['sku']));
					return;
                }
            }
		}

		if(isset($product['type']) && $product['type'] != ""){
			$prodtype = $product['type'];
		}else{
			array_push($this->error,array('txt'=>'Type is not define','product_sku'=>$product['sku']));
			return;
		}
		foreach ( $product as $field => $value ) 
		{
			if($field == "price_view"){continue;}
			if($field == "shipment_type"){continue;}
			if ( in_array( $field, $this->imageFields ) ) {
				continue;
			} 
			$attribute = $this->eavConfig->getAttribute(ModelProduct::ENTITY, $field);
			if ( !$attribute ) { continue; }
			$isArray = false;
			$setValue = $value;
			if ( $attribute->getFrontendInput() == 'multiselect' ) {
				$multiDelimiterValue = $this->helper->getMultiselectattvalue();
				if(!isset($multiDelimiterValue) || trim($multiDelimiterValue == "")){
					$multiDelimiterValue = ",";
				}
				$value = explode($multiDelimiterValue, $value);
				$isArray = true;
				$setValue = array();
			} 
			if ($attribute->getData('is_global') == '1') {
				$arrayOfFieldstoSkip[] = $field;
			}
			if ( $value && $attribute->getBackendType() == 'decimal' ) {
				$setValue = $this->localeFormat->getNumber($value);
			}
			if ( $attribute->usesSource() ) {
				$options = $attribute->getSource()->getAllOptions(false);
				if(empty($options)){
					$product[$field] = $setValue;
				}else{
		                if ($isArray) {
		                    foreach ($options as $item) {
		                        if (in_array($item['label'], $value)) {
		                            $setValue[] = $item['value'];
		                        }
		                    }
		                } else {
		                    $setValue = false;
		                    foreach ($options as $item) {
		                        if (is_array($item['value'])) {
		                            foreach ($item['value'] as $subValue) {
		                                if (isset($subValue['value']) && $subValue['value'] == $value) {
		                                    $setValue = $value;
		                                }
		                            }
		                        } else if ($item['label'] == $value) {
		                            $setValue = $item['value'];
		                        }
		                    }
		                }
		            }
			}	
			$product[$field] = $setValue;
		}
        $isNewProduct = true;
        $productRepository = "";
		$productType = "";
            if ($productloadByAttribute = $this->ProductModel->loadByAttribute('sku', $product['sku'])) {
                //$productRepository = $this->ProductModel->load($productloadByAttribute->getId());
                $isNewProduct = false;
				//$productType = $productRepository->getTypeId();
				$productType = $productloadByAttribute->getTypeId();
            }
		if(isset($product['category_ids'])) {
			$catsarray = explode(",",$product['category_ids']);
			$product['category_ids'] = $catsarray;
		}
		$ProductData = $this->ProductData($product,$isNewProduct);
		if(isset($product['categories']) && $product['categories'] != "") 
		{
			if(isset($ProductData['categories']) && !empty($ProductData['categories'])){
				$catsarray = explode(",",$ProductData['categories']);
				$product['category_ids'] = $catsarray;
			}
		}
		$ProductAttributeData = $this->ProductAttributeData($product);
		$ProductImageGallery = $this->ProductImageGallery($product,$isNewProduct);
		if(!empty($this->error))
		{
			return ;
		}
		$ProductStockdata = $this->ProductStockdata($product);
		$ProductSupperAttribute = $this->ProductSupperAttribute($product);
		$ProductCustomOption = $this->ProductCustomOption($product);
		$this->CreateProductWithrequiredField(
			$prodtype,$ProductData,$ProductAttributeData,$ProductImageGallery,$ProductStockdata,$ProductSupperAttribute,$ProductCustomOption
		);
	}
	public function CreateProductWithrequiredField($prodtype,$ProductData,$ProductAttributeData,$ProductImageGallery,$ProductStockdata,$ProductSupperAttribute,$ProductCustomOption)
	{
		if($prodtype == "simple"){
			 $this->error = $this->SimpleProduct->SimpleProductData($ProductData,$ProductAttributeData,$ProductImageGallery,$ProductStockdata,$ProductSupperAttribute,$ProductCustomOption);
		}elseif($prodtype == "configurable"){
			 $this->error = $this->ConfigurableProduct->ConfigurableProductData($ProductData,$ProductAttributeData,$ProductImageGallery,$ProductStockdata,$ProductSupperAttribute,$ProductCustomOption);
		}elseif($prodtype == "grouped"){
			 $this->error = $this->GroupedProduct->GroupedProductData($ProductData,$ProductAttributeData,$ProductImageGallery,$ProductStockdata,$ProductSupperAttribute);
		}elseif($prodtype == "virtual"){
			 $this->error = $this->VirtualProduct->VirtualProductData($ProductData,$ProductAttributeData,$ProductImageGallery,$ProductStockdata,$ProductSupperAttribute,$ProductCustomOption);
		}elseif($prodtype == "bundle"){
			 $this->error = $this->BundleProduct->BundleProductData($ProductData,$ProductAttributeData,$ProductImageGallery,$ProductStockdata,$ProductSupperAttribute,$ProductCustomOption);
		}elseif($prodtype == "downloadable"){
			 $this->error = $this->DownloadableProduct->DownloadableProductData($ProductData,$ProductAttributeData,$ProductImageGallery,$ProductStockdata,$ProductSupperAttribute,$ProductCustomOption);
		}else{
			array_push($this->error,array('txt'=>'The "type" column is is_required.','product_sku'=>$ProductData['sku']));
		}
	}
	public function ProductData($product,$isNewProduct)
	{
		$defaultProductData['sku'] = $product['sku'];
		if(isset($product['url_key'])){$defaultProductData['url_key'] = $product['url_key']; }
		if(isset($product['store'])) {
			$stores = $this->storeManager->getStores($withDefault = false);
			foreach($stores as $store) {
				$store_code[] = $store->getCode();
			}
			if($product['store'] != ''){
				foreach ($store_code as $code) {
					if (strpos($code, $product['store']) !== FALSE) { 
						$defaultProductData['store'] = $code;
					}
				}
			}
			if(!isset($defaultProductData['store'])) {
				$defaultProductData['store'] = $product['store'];
			}
		}
        if(isset($product['store_id'])){$defaultProductData['store_id'] = $product['store_id']; }else{ $defaultProductData['store_id'] = "0"; }
       	if(isset($product['websites'])) { $defaultProductData['websites'] = $this->websitenamebyid($product['websites']); }
       	if(isset($product['attribute_set'])) { $defaultProductData['attribute_set'] = $this->attributeSetNamebyid($product['attribute_set']); }
		if(isset($product['type'])) { $defaultProductData['type'] = $product['type']; }
		if(isset($product['categories'])){$defaultProductData['categories'] = $this->addCategories($product['categories'], $defaultProductData['store']);}
		if(isset($product['category_ids'])) { $defaultProductData['category_ids'] = $product['category_ids']; }
		if(isset($product['name'])) { $defaultProductData['name'] = $product['name']; }
		if(isset($product['status'])) { $defaultProductData['status'] = $product['status']; }
		if(isset($product['weight'])) { $defaultProductData['weight'] = $product['weight']; }
		if(isset($product['price'])) { $defaultProductData['price'] = $product['price']; }
		if(isset($product['special_price'])) { $defaultProductData['special_price'] = $product['special_price']; }
		if(isset($product['visibility'])) { $defaultProductData['visibility'] = $product['visibility']; }
		if(isset($product['tax_class_id'])) { $defaultProductData['tax_class_id'] = $product['tax_class_id']; }
		if(isset($product['description'])) { $defaultProductData['description'] = $product['description']; }
		if(isset($product['short_description'])) { $defaultProductData['short_description'] = $product['short_description']; }
		return $defaultProductData;
	}
	public function ProductAttributeData($product)
	{
		$defaultAttributeData = array();
		foreach ($product as $field => $value) {
		    if(!in_array($field, $this ->imageFields)) { 
				$defaultAttributeData[$field] = $value;
			}
		}
		return $defaultAttributeData;
	}
	public function ProductSupperAttribute($product)
	{
		$defaultSupperAttributeData = array(
			'related_product_sku' 			=> (isset($product['related_product_sku'])) ? $product['related_product_sku'] : '',
			'upsell_product_sku' 			=> (isset($product['upsell_product_sku'])) ? $product['upsell_product_sku'] : '',
			'crosssell_product_sku'			=> (isset($product['crosssell_product_sku'])) ? $product['crosssell_product_sku'] : '',
			'cws_tier_price' 				=> (isset($product['cws_tier_price'])) ? $this->TierPricedata($product['cws_tier_price'], $product['sku'], $product['type']) : '',
			'child_products_sku' 			=> (isset($product['child_products_sku'])) ? $product['child_products_sku'] : '',
			'bundle_product_options' 		=> (isset($product['bundle_product_options'])) ? $product['bundle_product_options'] : '',
			'grouped_product_sku' 			=> (isset($product['grouped_product_sku'])) ? $product['grouped_product_sku'] : '',
			'group_price_price' 			=> (isset($product['group_price_price'])) ? $product['group_price_price'] : '',
			'downloadable_product_options' 	=> (isset($product['downloadable_product_options'])) ? $product['downloadable_product_options'] : '',
			'downloadable_product_samples' 	=> (isset($product['downloadable_product_samples'])) ? $product['downloadable_product_samples'] : '',
			'link_can_purchase_separately' 	=> (isset($product['link_can_purchase_separately'])) ? $product['link_can_purchase_separately'] : '',
		);
		return $defaultSupperAttributeData;
	}
	public function ProductCustomOption($product)
	{	
		$custom_options = array();
		foreach ( $product as $field => $value ){
			if(strpos($field,':')!==FALSE && strlen($value)) {
			   $values=explode('|',$value);
			   if(count($values)>0) {
					$iscustomoptions = "true";
					foreach($values as $v) {
					 $parts = explode(':',$v);
					 $title = $parts[0];
					}
				list($title,$type,$is_required,$sort_order) = explode(':',$field.':');
				//@list($title,$type,$is_required,$sort_order) = explode(':',$field);
				$title2 = str_replace("cws!","",$title);
				  $custom_options[] = array(
					 'is_delete'=>0,
					 'title'=>$title2,
					 'previous_group'=>'',
					 'previous_type'=>'',
					 'type'=>$type,
					 'is_require'=>$is_required,
					 'sort_order'=>$sort_order,
					 'values'=>array()
				  );
				  if($is_required ==1) {
						$iscustomoptionsrequired = "true";
				  }
				  foreach($values as $v) {
					 $parts = explode(':',$v);
					 $title = $parts[0];
					 if(count($parts)>1) {
						$price_type = $parts[1];
					 } else {
						$price_type = 'fixed';
					 }
					 if(count($parts)>2) {
						$price = $parts[2];
					 } else {
						$price =0;
					 }
					 if(count($parts)>3) {
						$sku = $parts[3];
					 } else {
						$sku='';
					 }
					 if(count($parts)>4) {
						$sort_order = $parts[4];
					 } else {
						$sort_order = 0;
					 }
					 if(count($parts)>5) {
						$max_characters = $parts[5];
					 } else {
						$max_characters = '';
					 }
					 if(count($parts)>6) {
						$file_extension = $parts[6];
					 } else {
						$file_extension = '';
					 }
					 if(count($parts)>7) {
						$image_size_x = $parts[7];
					 } else {
						$image_size_x = '';
					 }
					 if(count($parts)>8) {
						$image_size_y = $parts[8];
					 } else {
						$image_size_y = '';
					 }
					 switch($type) {
						case 'file':
							if($title != ""){
								 $title = $title;
							}else{
								 $title = 0;
							}
							if($price_type != ""){
								 $price_type = $price_type;
							}else{
								$price_type = "fixed";
							}
							if($price != ""){
								 $price = $price;
							}else{
								 $price = "";
							}
						$custom_options[count($custom_options) - 1]['price'] = $title;
						$custom_options[count($custom_options) - 1]['price_type'] = $price_type;
						$custom_options[count($custom_options) - 1]['sku'] = $price;
						$custom_options[count($custom_options) - 1]['file_extension'] = $sku;
						$custom_options[count($custom_options) - 1]['image_size_x'] = $sort_order;
						$custom_options[count($custom_options) - 1]['image_size_y'] = $max_characters;
						    break;
						case 'field':
							 if($title != ""){
								 $title = $title;
							}else{
								 $title = 0;
							}
							if($price_type != ""){
								 $price_type = $price_type;
							}else{
								$price_type = "fixed";
							}
							if($price != ""){
								 $price = $price;
							}else{
								 $price = "";
							}
							$custom_options[count($custom_options) - 1]['price'] = $title;
							$custom_options[count($custom_options) - 1]['price_type'] = $price_type;
							$custom_options[count($custom_options) - 1]['sku'] = $price;
							$custom_options[count($custom_options) - 1]['max_characters'] = $sku;
							break;
						case 'area':
							 if($title != ""){
								 $title = $title;
							}else{
								 $title = 0;
							}
							if($price_type != ""){
								 $price_type = $price_type;
							}else{
								$price_type = "fixed";
							}
							if($price != ""){
								 $price = $price;
							}else{
								 $price = "";
							}
							$custom_options[count($custom_options) - 1]['price'] = $title;
							$custom_options[count($custom_options) - 1]['price_type'] = $price_type;
							$custom_options[count($custom_options) - 1]['sku'] = $price;
							$custom_options[count($custom_options) - 1]['max_characters'] = $sku;
							break;
						case 'date':
							 if($title != ""){
								 $title = $title;
							}else{
								 $title = 0;
							}
							if($price_type != ""){
								 $price_type = $price_type;
							}else{
								$price_type = "fixed";
							}
							if($price != ""){
								 $price = $price;
							}else{
								 $price = "";
							}
							$custom_options[count($custom_options) - 1]['price'] = $title;
							$custom_options[count($custom_options) - 1]['price_type'] = $price_type;
							$custom_options[count($custom_options) - 1]['sku'] = $price;
							break;
						case 'date_time':
							 if($title != ""){
								 $title = $title;
							}else{
								 $title = 0;
							}
							if($price_type != ""){
								 $price_type = $price_type;
							}else{
								$price_type = "fixed";
							}
							if($price != ""){
								 $price = $price;
							}else{
								 $price = "";
							}
							$custom_options[count($custom_options) - 1]['price'] = $title;
							$custom_options[count($custom_options) - 1]['price_type'] = $price_type;
							$custom_options[count($custom_options) - 1]['sku'] = $price;
							break;
						case 'time':
							 if($title != ""){
								 $title = $title;
							}else{
								 $title = 0;
							}
							if($price_type != ""){
								 $price_type = $price_type;
							}else{
								$price_type = "fixed";
							}
							if($price != ""){
								 $price = $price;
							}else{
								 $price = "";
							}
							$custom_options[count($custom_options) - 1]['price'] = $title;
							$custom_options[count($custom_options) - 1]['price_type'] = $price_type;
							$custom_options[count($custom_options) - 1]['sku'] = $price;
							break;
						case 'drop_down':
							if($price_type != ""){
								 $price_type = $price_type;
							}else{
								$price_type = 0;
							}
							if($price != ""){
								 $price = $price;
							}else{
								 $price = "fixed";
							}
							if($sku != ""){
								 $sku = $sku;
							}else{
								 $sku = "";
							}
							$custom_options[count($custom_options) - 1]['values'][]=array(
							  'is_delete'=>0,
							  'title'=>$title,
							  'option_type_id'=>-1,
							  'price'=>$price_type,
							  'price_type'=>$price,
							  'sku'=>$sku,
							  'sort_order'=>$sort_order,
						   );
						    break;
						case 'radio':
							 if($price_type != ""){
								 $price_type = $price_type;
							}else{
								$price_type = 0;
							}
							if($price != ""){
								 $price = $price;
							}else{
								 $price = "fixed";
							}
							if($sku != ""){
								 $sku = $sku;
							}else{
								 $sku = "";
							}
						$custom_options[count($custom_options) - 1]['values'][]=array(
							  'is_delete'=>0,
							  'title'=>$title,
							  'option_type_id'=>-1,
							  'price'=>$price_type,
							  'price_type'=>$price,
							  'sku'=>$sku,
							  'sort_order'=>$sort_order,
						   );
						    break;
						case 'checkbox':
							 if($price_type != ""){
								 $price_type = $price_type;
							}else{
								$price_type = 0;
							}
							if($price != ""){
								 $price = $price;
							}else{
								 $price = "fixed";
							}
							if($sku != ""){
								 $sku = $sku;
							}else{
								 $sku = "";
							}
						$custom_options[count($custom_options) - 1]['values'][]=array(
							  'is_delete'=>0,
							  'title'=>$title,
							  'option_type_id'=>-1,
							  'price'=>$price_type,
							  'price_type'=>$price,
							  'sku'=>$sku,
							  'sort_order'=>$sort_order,
						   );
						     break;
						case 'multiple':
							 if($price_type != ""){
								 $price_type = $price_type;
							}else{
								$price_type = 0;
							}
							if($price != ""){
								 $price = $price;
							}else{
								 $price = "fixed";
							}
							if($sku != ""){
								 $sku = $sku;
							}else{
								 $sku = "";
							}
						$custom_options[count($custom_options) - 1]['values'][]=array(
							  'is_delete'=>0,
							  'title'=>$title,
							  'option_type_id'=>-1,
							  'price'=>$price_type,
							  'price_type'=>$price,
							  'sku'=>$sku,
							  'sort_order'=>$sort_order,
						   );
						   break;
					 }
				  }
			   }
			}
		}
		return $custom_options;
	}
	public function ProductImageGallery($product,$isNewProduct)
    {
    	if(isset($product['gallery']) || isset($product['image']) && isset($product['small_image']) && isset($product['thumbnail'])) 
		{
			if( trim($product['gallery']) == "" ||  trim($product['image']) == "" && trim($product['small_image']) == "" && trim($product['thumbnail']) == "" ) 
			{
				$ProductImageGallery = array(
					'gallery'       => (isset($product['gallery'])) ? $product['gallery'] : '',
					'image'       	=> (isset($product['image'])) ? $product['image'] : '',
					'small_image'   => (isset($product['small_image'])) ? $product['small_image'] : '',
					'thumbnail'     => (isset($product['thumbnail'])) ? $product['thumbnail'] : '',
					'swatch_image'  => (isset($product['swatch_image'])) ? $product['swatch_image'] : '',
					'gallery_label' => (isset($product['gallery_label'])) ? $product['gallery_label'] : ''
				);
				return $ProductImageGallery;
			}else{
			 
	        if (!$isNewProduct) 
	        {
	        	$this->DeleteImagesBeforeAdd($product);
	        	if(!empty($this->error))
	        	{
	        		return ;
	        	}
	        }
			if(trim($product["image"]) != "" && substr_count($product["image"],"/") == 0){
				$product["image"] = "/".$product["image"]; 
			}
			if(trim($product["small_image"]) != "" && substr_count($product["small_image"],"/") == 0){ 
				$product["small_image"] = "/".$product["small_image"]; 
			}
			if(trim($product["thumbnail"]) != "" && substr_count($product["thumbnail"],"/") == 0){ 
				$product["thumbnail"] = "/".$product["thumbnail"]; 
			}
			if(isset($product["swatch_image"])){
				if(trim($product["swatch_image"]) != "" && substr_count($product["swatch_image"],"/") == 0){ 
					$product["swatch_image"] = "/".$product["swatch_image"]; 
				}
			}

			if(trim($product["gallery"]) != ""){ 
				$gallery_image = array();
				$gallery_image = explode("|",$product["gallery"]);

				$temp = "";
				foreach($gallery_image as $value){
					if(trim($value) != "/"){
						if(substr_count(trim($value),"/") == 0){
							$temp .= "/".$value."|";
						}else{
							$temp .= $value."|";
						}
					}
				}
				$product["gallery"] = rtrim($temp, '|');
			}

	        $ProductImageGallery = [
	            'gallery'      	=> (isset($product['gallery'])) ? $product['gallery'] : '',
	            'image'        	=> (isset($product['image'])) ? $product['image'] : '',
	            'small_image'  	=> (isset($product['small_image'])) ? $product['small_image'] : '',
	            'thumbnail'    	=> (isset($product['thumbnail'])) ? $product['thumbnail'] : '',
	            'swatch_image' 	=> (isset($product['swatch_image'])) ? $product['swatch_image'] : '',
	            'gallery_label'	=> (isset($product['gallery_label'])) ? $product['gallery_label'] : ''
	        ];
	        
	        return $ProductImageGallery;
	    } 
		}
    }
    public function DeleteImagesBeforeAdd($product)
    {
	    $productModel = $this->ProductRepositoryInterface->get($product['sku']);
	    $productModel->setMediaGalleryEntries([]);
	    try{
	    	$this->ProductRepositoryInterface->save($productModel);
	    }
	    catch(\Exception $e)
		{
			array_push($this->error,array('txt'=>$e->getMessage(),'product_sku'=>$product['sku']));
			return ;
		}
	    $product_id = $productModel->getId();
	    $connection = $this->resource->getConnection();
	    $eav_attribute                              = $this->resource->getTableName('eav_attribute');
	    $catalog_product_entity_varchar             = $this->resource->getTableName('catalog_product_entity_varchar');
	    $catalog_product_entity_media_gallery       = $this->resource->getTableName('catalog_product_entity_media_gallery');
	    $catalog_product_entity_media_gallery_value = $this->resource->getTableName('catalog_product_entity_media_gallery_value');
	    $allImages = $connection->fetchAll("SELECT value_id FROM " . $catalog_product_entity_media_gallery_value . " WHERE entity_id = '" . $product_id . "'");
	    foreach ($allImages as $eachImageRemove) {
	        if ($eachImageRemove['value_id'] != "") {
	            $connection->query("DELETE FROM " . $catalog_product_entity_media_gallery . " WHERE value_id = '" . $eachImageRemove['value_id'] . "'");
	        }
	    }
	    $connection->query("DELETE FROM " . $catalog_product_entity_media_gallery_value . " WHERE entity_id = '" . $product_id . "'");
	    $connection->query("DELETE FROM " . $catalog_product_entity_varchar . " WHERE entity_id = '" . $product_id . "' AND ( attribute_id = ( SELECT attribute_id FROM " . $eav_attribute . " AS eav WHERE eav.attribute_code = 'image' AND eav.entity_type_id ='4') OR attribute_id = ( SELECT attribute_id FROM " . $eav_attribute . " AS eav WHERE eav.attribute_code = 'small_image' AND eav.entity_type_id ='4') OR attribute_id = ( SELECT attribute_id FROM " . $eav_attribute . " AS eav WHERE eav.attribute_code = 'thumbnail' AND eav.entity_type_id ='4') OR attribute_id = ( SELECT attribute_id FROM " . $eav_attribute . " AS eav WHERE eav.attribute_code = 'media_gallery' AND eav.entity_type_id ='4'))");
    }
	public function ProductStockdata($product)
	{
		$defaultStockData = array(
			'manage_stock'                  => (isset($product['manage_stock'])) ? $product['manage_stock'] : null,
			'use_config_manage_stock'       => (isset($product['use_config_manage_stock'])) ? $product['use_config_manage_stock'] : null,
			'qty'                           => (isset($product['qty'])) ? $product['qty'] : null,
			'min_qty'                       => (isset($product['min_qty'])) ? $product['min_qty'] : null,
			'use_config_min_qty'            => (isset($product['use_config_min_qty'])) ? $product['use_config_min_qty'] : null,
			'min_sale_qty'                  => (isset($product['min_sale_qty'])) ? $product['min_sale_qty'] : null,
			'use_config_min_sale_qty'       => (isset($product['use_config_min_sale_qty'])) ? $product['use_config_min_sale_qty'] : null,
			'max_sale_qty'                  => (isset($product['max_sale_qty'])) ? $product['max_sale_qty'] : null,
			'use_config_max_sale_qty'       => (isset($product['use_config_max_sale_qty'])) ? $product['use_config_max_sale_qty'] : null,
			'is_qty_decimal'                => (isset($product['is_qty_decimal'])) ? $product['is_qty_decimal'] : null,
			'backorders'                    => (isset($product['backorders'])) ? $product['backorders'] : null,
			'use_config_backorders'         => (isset($product['use_config_backorders'])) ? $product['use_config_backorders'] : null,
			'notify_stock_qty'              => (isset($product['notify_stock_qty'])) ? $product['notify_stock_qty'] : null,
			'use_config_notify_stock_qty'   => (isset($product['use_config_notify_stock_qty'])) ? $product['use_config_notify_stock_qty'] : null,
			'enable_qty_increments'         => (isset($product['enable_qty_increments'])) ? $product['enable_qty_increments'] : null,
			'use_config_enable_qty_inc'     => (isset($product['use_config_enable_qty_inc'])) ? $product['use_config_enable_qty_inc'] : null,
			'qty_increments'                => (isset($product['qty_increments'])) ? $product['qty_increments'] : null,
			'use_config_qty_increments'     => (isset($product['use_config_qty_increments'])) ? $product['use_config_qty_increments'] : null,
			'is_in_stock'                   => (isset($product['is_in_stock'])) ? $product['is_in_stock'] : null,
			'low_stock_date'                => (isset($product['low_stock_date'])) ? $product['low_stock_date'] : null,
			'stock_status_changed_auto'     => (isset($product['stock_status_changed_auto'])) ? $product['stock_status_changed_auto'] : null
		);
		return $defaultStockData;
	}
	public function pagelayout($pagelayout)
	{
		$data = "";
		if($pagelayout == "No layout"){ 
			$data = "";
		}else{
			$data = $pagelayout;
		}
		return $data;
	}
	Public function msrRetailpriceSuggested($msrp)
	{
		$data ="";
		if($msrp == "None"){
			$data = "";
		}else{
			$data = $msrp;
		}
		return $data;
	}
	public function TierPricedata($TPData, $product_sku, $product_type)
	{
		$version = $this->productMetadata->getVersion();
		$pId = $this->ProductModel->getIdBySku($product_sku);
		$connectionRead = $this->getConnection('core_write');
		$_eav_attribute_set = $this->resource->getTableName('catalog_product_entity_tier_price');
		$connectionRead->query('DELETE FROM '.$_eav_attribute_set.' WHERE entity_id = '.intval($pId));
		$existing_tps = array();
		$etp_lookup = array();
		foreach($existing_tps as $key => $etp){
			$etp_lookup[intval($etp['price_qty'])] = $key;
		}
		$incoming_tierps = explode('|',$TPData);
		$tps_toAdd = array();  
		$tierpricecount=0;
		foreach($incoming_tierps as $tier_str){
			if (empty($tier_str)) continue;		
			$tmp = array();
			$tmp = explode('=',$tier_str);
			if ($tmp[1] == 0 && $tmp[2] == 0) continue;
			if($version < '2.1.0'){
	            $tps_toAdd[$tierpricecount] = array(
	                'website_id' => 0,
	                'cust_group' => $tmp[0],
	                'price_qty' => $tmp[1],
	                'price' => $tmp[2],
	                'delete' => ''
	            );
			}else{
				if($product_type == "bundle"){
					if(count($tmp)==4){	
		            	$tps_toAdd[$tierpricecount] = array(
		                'website_id' => 0,
		                'cust_group' => $tmp[0],
		                'price_qty' => $tmp[1],
		                'value_type' => "percent",
		                'percentage_value' => $tmp[3],
		                'delete' => ''
		            );
		            }
		            else{
		            	$tps_toAdd[$tierpricecount] = array(
		                'website_id' => 0,
		                'cust_group' => $tmp[0],
		                'price_qty' => $tmp[1],
		                'value_type' => "percent",
		                'percentage_value' => $tmp[2],
		                'delete' => ''
		            );    
		            }
		        }else{
		        	if(count($tmp)==4){	
	        			if($tmp[2]=='percent'){
	        	 			$tps_toAdd[$tierpricecount] = array(
			                'website_id' => 0,
			                'cust_group' => $tmp[0],
			                'price_qty' => $tmp[1],
			                'value_type' => "percent",
			                'percentage_value' => $tmp[3],
			                'delete' => ''
		              	);
          			  	}else{
			        		$tps_toAdd[$tierpricecount] = array(
			                'website_id' => 0,
			                'cust_group' => $tmp[0],
			                'price_qty' => $tmp[1],
			                'value_type' => "fixed",
			                'price' => $tmp[3],
			                'delete' => ''
	            		);
        			  	}
        			}
        			else{
	        			$tps_toAdd[$tierpricecount] = array(
			                'website_id' => 0,
			                'cust_group' => $tmp[0],
			                'price_qty' => $tmp[1],
			                'value_type' => "fixed",
			                'price' => $tmp[2],
			                'delete' => ''
		            		);
        			}
        		}
			}
			if(isset($etp_lookup[intval($tmp[1])])){
				unset($existing_tps[$etp_lookup[intval($tmp[1])]]);
				$tps_toAdd[$tierpricecount] = array();
			}
			$tierpricecount++;
		}
		$tps_toAdd =  array_merge($existing_tps, $tps_toAdd);
		
		return $tps_toAdd;
	}
   protected function addCategories($categories, $storeId)
    {
		$rootId = $this->storeManager->getStore($storeId)->getRootCategoryId();
		if (!$rootId) {
			$storeId = 1;
		 	$rootId = $this->storeManager->getStore($storeId)->getRootCategoryId();
        }

		if($categories == ""){
		   return array();
		}
        $rootPath = '1/'.$rootId;

        if (empty($this->categoryCache[$storeId])) {
			$collection = $this->helper->getObjectManager()->create('Magento\Catalog\Model\Category')->getCollection()
                ->setStoreId($storeId)
                ->addAttributeToSelect('name');
            $collection->getSelect()->where("path like '".$rootPath."/%'");
            foreach ($collection as $cat) {
                $pathArr = explode('/', $cat->getPath());
                $namePath = '';
                for ($i=2, $l=sizeof($pathArr); $i<$l; $i++) {
                    $name = $collection->getItemById($pathArr[$i])->getName();
                    $namePath .= (empty($namePath) ? '' : '/').trim($name);
                }
                $cat->setNamePath($namePath);
            }
            $cache = array();
            foreach ($collection as $cat) {
                $cache[$cat->getNamePath()] = $cat;
                $cat->unsNamePath();
            }
            $this->categoryCache[$storeId] = $cache;
        }
        $cache =& $this->categoryCache[$storeId];
        $catIds = array();

       	foreach (array_filter(explode(',', $categories)) as $categoryPathStr) {
           $categoryPathStr = preg_replace('#\s*/\s*#', '/', trim($categoryPathStr));
            if (!empty($cache[$categoryPathStr])) {
                $catIds[] = $cache[$categoryPathStr]->getId();
                continue;
            }
            $path = $rootPath;
            $namePath = '';
             foreach (explode('/', $categoryPathStr) as $catName) {
             	try {
	                $namePath .= (empty($namePath) ? '' : '/').$catName;
					$urlKey = strtolower($catName);
					$cleanurl = trim(preg_replace('/ +/', '', preg_replace('/[^A-Za-z0-9 ]/', '', urldecode(html_entity_decode(strip_tags($urlKey))))));
					if (empty($cache[$namePath])) {
						    $cat = $this->helper->getObjectManager()->create('Magento\Catalog\Model\Category')
		                        ->setStoreId($storeId)
		                        ->setPath($path)
		                        ->setName($catName)
								->setIsActive(1)
								->setUrlKey($cleanurl)
		                        ->save();
		                    $cache[$namePath] = $cat;
	                }
	                $catId = $cache[$namePath]->getId();
	                $path .= '/'.$catId;
					if ($catId) {
						$catIds[] = $catId;
					}
			 } catch (\Exception $e) {
				array_push($this->error,array('txt'=>$e->getMessage(),'product_sku'=>$catName));
			 }
            } //Forloop
        }
        return join(',', $catIds);
    }
	public function attributeSetNamebyid($attributeSetName){
        $entityType = $this->helper->getObjectManager()->create('\Magento\Eav\Model\Entity\Type')->loadByCode('catalog_product');
        $attributeSetCollection = $this->helper->getObjectManager()->create('\Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\Collection');
        $attributeSetCollection->addFilter('attribute_set_name', $attributeSetName);
        $attributeSetCollection->addFilter('entity_type_id', $entityType->getId());
        $attributeSetCollection->setOrder('attribute_set_id');
        $attributeSetCollection->setPageSize(1);
        $attributeSetCollection->load();
        $attributeSet = $attributeSetCollection->fetchItem();
        if ($attributeSet) {
            return $attributeSet->getId();
        } else {
            throw new \Magento\Framework\Exception\LocalizedException(__('Attribute Set "' . $attributeSetName . '" does NOT exist.'));
        }
	}
	public function websitenamebyid($webid){
		$webidX = explode(',', $webid);
		$WebsiteId = array();
		foreach($webidX as $webids){
			$WebsiteId[] = $this->website->load($webids)->getId();
		}
		return $WebsiteId;
	}
	public function dateformat($date){
		$data = $this->date->gmtDate('Y-m-d',$date);
		return $data;
	}
}
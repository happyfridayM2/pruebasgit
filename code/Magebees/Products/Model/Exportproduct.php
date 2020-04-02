<?php
namespace Magebees\Products\Model;
class Exportproduct extends \Magento\Framework\Model\AbstractModel
{
	protected $proceed_next=true;
	protected $coreRegistry = null;
	protected $resultPageFactory;
	protected $storeManager;
	protected $configurable;
	protected $store;
	protected $attributes = array();
	protected $objectManager;
	protected $product_data_collection=array();
	protected $image_related_fields =  ['store' => 0,'websites' => 1,'attribute_set' => 2,'type' => 3,'sku' => 4,'image' => 5,'small_image' => 6,'thumbnail' => 7,'swatch_image' => 8,'gallery' => 9];
	protected $inventory_related_fields = ['store' => 0,'websites' => 1,'attribute_set' => 2,'type' => 3,'sku' => 4,'qty' => 5,'min_qty' => 6,'use_config_min_qty' => 7,'is_qty_decimal' => 8,'backorders' => 9,'use_config_backorders' => 10,'min_sale_qty' => 11,'use_config_min_sale_qty' => 12,'max_sale_qty' => 13,'use_config_max_sale_qty' => 14,'is_in_stock' => 15,'use_config_notify_stock_qty' => 16,'manage_stock' => 17,'use_config_manage_stock' => 18,'is_decimal_divided' => 19];
	protected $price_related_fields = ['store' => 0,'websites' => 1,'attribute_set' => 2,'type' => 3,'sku' => 4,'price' => 5,'special_price' => 6,'cws_tier_price' => 7];
	protected $releted_up_cross_sell_fields =  ['store' => 0,'websites' => 1,'attribute_set' => 2,'type' => 3,'sku' => 4,'related_product_sku' => 5,'crosssell_product_sku' => 6,'upsell_product_sku' => 7,'related_product_position' => 8,'crosssell_product_position' => 9,'upsell_product_position' => 10];
	protected $scopeConfig;
	protected $helper;

	public function __construct(
		\Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
		\Magento\Framework\Stdlib\DateTime\DateTime $date,
		\Magento\ConfigurableProduct\Model\Product\Type\Configurable $configurable,
		\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
		\Magebees\Products\Helper\Data $helper
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->coreRegistry = $registry;
		$this->storeManager = $storeManager;
		$this->configurable = $configurable;
		$this->_date = $date;
		$this->scopeConfig = $scopeConfig;
		$this->helper = $helper;
        parent::__construct($context,$registry);
    }
	public function getProductExportFile($page,$store_export_id,$attr_id,$export_for,$timestamp,$type_id,$status_id,$visibility_id,$cat_ids,$fromId,$toId,$export_fields){
		$last_page = $this->unparse($page,$store_export_id,$attr_id,$export_for,$type_id,$status_id,$visibility_id,$cat_ids,$fromId,$toId,$export_fields);
		if($last_page==$page){
			$this->proceed_next=false;
		}
		return $this->exportCSV($page,$timestamp);
	}
	
	public function exportCSV($page,$timestamp)
	{
		$current_time = $timestamp;	
		$filesystem = $this->helper->getObjectManager()->get('Magento\Framework\Filesystem');
		$reader = $filesystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::VAR_DIR);
			if (!file_exists($reader->getAbsolutePath().'export')) {
				mkdir($reader->getAbsolutePath().'export', 0777, true);
			}
			if($page!=1){
				$cws_csv_header = $reader->getAbsolutePath('export/cws_csv_header-'.$current_time.'.obj');
				$header_string_obj=file_get_contents($cws_csv_header);
				$header_template = $this->helper->getUnserializeData($header_string_obj);
			}else{
				$header_template=array();						
			}
			$f_name= 'export-products-'.$current_time.'.csv';
			$file_name = $reader->getAbsolutePath("export/".$f_name);
			$fp = fopen($file_name.'-tmp-'.$page, 'w');
			$header=true;
			$temp_order_data=array();
			foreach ($this->product_data_collection as $product) {
				foreach(array_keys($product) as $k=>$v){
					if (!in_array($v, $header_template)) {
						array_push($header_template,$v);
					}
				}				
			}
			$cws_csv_header = $reader->getAbsolutePath("export/cws_csv_header-".$current_time.".obj");
			file_put_contents($cws_csv_header, $this->helper->getSerializeData($header_template),LOCK_EX);

			fputcsv($fp, array_values($header_template));
			foreach ($this->product_data_collection as $product) {
				$o_data=array_fill_keys(array_values($header_template), '');
				foreach($product as $o_key=>$o_val){
					if (in_array($o_key, $header_template)) {
						$o_data[$o_key]=$o_val;
					}
				}
				fputcsv($fp, array_values($o_data));
			}
			fclose($fp);
			$page++;
			return array("filename"=>$f_name,'fullpath'=>$file_name,"proceed_next"=>$this->proceed_next,'page'=>$page,"exportedOrders"=>count($this->product_data_collection),'timestamp'=>$current_time);
	}
	
	public function unparse($page=1,$store_export_id,$attr_id,$export_for,$type_id,$status_id,$visibility_id,$cat_ids,$fromId,$toId,$export_fields)
    {
		if($attr_id != '*'){
			$_product_collection = $this->helper->getObjectManager()->create('\Magento\Catalog\Model\Product')->getCollection()->addAttributeToSelect("*")->addFieldToFilter('attribute_set_id',$attr_id)->setPageSize(200)->setCurPage($page);
		}else{
			$_product_collection = $this->helper->getObjectManager()->create('\Magento\Catalog\Model\Product')->getCollection()->addAttributeToSelect("*")->setPageSize(200)->setCurPage($page); 
		}
		$_systemFields = array('category_ids','entity_id','entity_type_id','attribute_set_id','type_id','created_at','updated_at','item_id','product_id','stock_id','required_options');
		if($store_export_id != '*'){
			$_product_collection->addStoreFilter($store_export_id);
		}
		if($type_id != '*'){
			$_product_collection->addFieldToFilter('type_id',$type_id);
		}
		if($status_id != '*'){
			$_product_collection->addAttributeToFilter('status', ['in' => $status_id]);
		}
		if($visibility_id != '*'){
			$_product_collection->addAttributeToFilter('visibility', $visibility_id);
		}
		if($fromId != '' && $toId != '' ){
			$_product_collection->addAttributeToFilter('entity_id', array('from' => $fromId,'to' => $toId));
		}
		if(!empty($cat_ids)){
			$cat_ids = array_unique( $cat_ids );
			if(count(array_filter($cat_ids))>0){
				$_product_collection->addCategoriesFilter(['in' => array($cat_ids)]);
			}
		}
		$row = array();
		$category_model = $this->helper->getObjectManager()->create('\Magento\Catalog\Model\Category');

		$flag = 0;
		foreach($_product_collection as $product)
		{
				$att = $this->helper->getObjectManager()->create('\Magento\Eav\Model\Entity\Attribute\Set')->load($product->getAttributeSetId());
				 $row = array(
					'store'         => $this->getStore($store_export_id)->getCode(),
					'websites'      => '',
					'attribute_set' => $att->getAttributeSetName(),
					'type'          => $product->getTypeId(),
					'category_ids'  => join(',', $product->getCategoryIds())
				);
				if ($this->getStore($store_export_id)->getCode() == \Magento\Store\Model\Store::ADMIN_CODE) {
                $websiteCodes = array();
                /*
                foreach ($product->getWebsiteIds() as $websiteId) {
                    $websiteCode = $this->storeManager->getWebsite($websiteId)->getCode();
                    $websiteCodes[$websiteCode] = $websiteCode;
                }
                */
                	$existingWebsiteIds=array();
					foreach($this->storeManager->getWebsites() as $m){
					    $existingWebsiteIds[] = $m['website_id'];
	                }
	                foreach ($product->getWebsiteIds() as $websiteId) {
	                	if (in_array($websiteId, $existingWebsiteIds)){
		                    $websiteCode = $this->storeManager->getWebsite($websiteId)->getCode();
		                    $websiteCodes[$websiteCode] = $websiteCode;
		                }
	                }
					$row['websites'] = join(',', $websiteCodes);
				} else {
					$row['websites'] = $this->getStore(0)->getWebsite()->getCode();
					if ($this->getVar('url_field')) {
						$row['url'] = $product->getProductUrl(false);
					}
				}
				$parentcatDelimiterValue =  $this->scopeConfig->getValue('magebeesproducts/general/parent_cat_delimiter', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
				if(!isset($parentcatDelimiterValue) || trim($parentcatDelimiterValue == "")){
					$parentcatDelimiterValue = ",";
				}
				$childcatDelimiterValue =  $this->scopeConfig->getValue('magebeesproducts/general/child_cat_delimiter', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
				if(!isset($childcatDelimiterValue) || trim($childcatDelimiterValue == "")){
					$childcatDelimiterValue = "/";
				}
				$all_cats = $product->getCategoryIds();
				$main_cnt = count($all_cats);
				$cat_str_main = ''; 
				$j = 0;
				if(!empty($all_cats)){
					foreach($all_cats as $ac){
						$cat_str = "";
						$category = $category_model->load($ac);
						$pathIds = explode("/",$category->getPath());
						foreach($pathIds as $catid){
							$category_sub = $category_model->setStoreId($store_export_id)->load($catid);
							if($category_sub->getLevel() == 0){
								continue;
							}
							if($category_sub->getLevel() == 2){
								$cat_str = $category_sub->getName();
							}else if($category_sub->getLevel() > 2){
								$cat_str = $cat_str.$childcatDelimiterValue.$category_sub->getName();
							}
						}
						if($j < 1){
							$cat_str_main = $cat_str;
						}else {
							$cat_str_main = trim($cat_str_main .$parentcatDelimiterValue.$cat_str);
						}
						$j = $j+1;						
					}
					
					if(isset($cat_str_main[0])){
						if($cat_str_main[0] == $parentcatDelimiterValue){
							$cat_str_main = ltrim ($cat_str_main, $parentcatDelimiterValue);
						}
					}
				}
				$row['categories'] = $cat_str_main;
				unset($row["category_ids"]);
				
				$productData = $product->getData();
				if(!empty($productData)){
					foreach ($product->getData() as $field => $value) {
						if (in_array($field, $_systemFields) || is_object($value)) {
							continue;
						}
						$attribute = $this->getAttribute($field);
						if (!$attribute) { continue; }
						if ($attribute->usesSource()) {
							if($attribute->getFrontendInput() == "text"){
								$row[$field] = $value;
							}else{
								$option = $attribute->getSource()->getOptionText($value);
								if ($value && empty($option) && $option != '0') {
									continue;
								}
								if (is_array($option)) {
									$value = join(",", $option);
								} else {
									$value = $option;
								}
								unset($option);
							}	
						} elseif (is_array($value)) {
							continue;
						}
							$row[$field] = $value;
						}
				}
				$gimages = $this->helper->getObjectManager()->create('\Magento\Catalog\Model\Product')->load($product->getId())->getMediaGalleryImages();
				if(is_array($gimages) || isset($gimages)){
					$mediagallery='';
					$index=0;
					$img_labels = '';
					$index1=0;
					foreach($gimages as $_image){
						if($_image['file']!=$product->getThumbnail() && $_image['file']!=$product->getSmallImage() && $_image['file']!=$product->getImage()){
							if($index==0){
								$mediagallery=$_image['file'];
							}else{
								$mediagallery=$mediagallery.'|'.$_image['file'];
							}
							$index++;				
						}
						if($_image['label'] != ""){
							if($index1==0){
								$img_labels = $_image['label'];
							}else{
								$img_labels = $img_labels.'|'.$_image['label'];
							}
						}
						$index1++;	
					}
					if($mediagallery == ""){
						$row["gallery"] = " ";
					}else{
						$row["gallery"]=$mediagallery;
					}
					$row["img_label"] = $img_labels;
				}

				unset($row["image_label"]); 
				unset($row["small_image_label"]); 
				unset($row["thumbnail_label"]);

				$stockRegistry = $this->helper->getObjectManager()->get('Magento\CatalogInventory\Api\StockRegistryInterface');
				$stockItem = $stockRegistry->getStockItem($product->getId(),$product->getStore()->getWebsiteId());
				$stockItemData = $stockItem->getData();
				if ($stockItemData) {
					foreach ($stockItemData as $field => $value) {
						if (in_array($field, $_systemFields) || is_object($value)) {
							continue;
						}
						$row[$field] = $value;
					}
				}
			if($product->getTypeId()=='configurable'){
				$super_attr=array();
				$child_sku=array();
				$option_data=array();
				$attrs  = $product->getTypeInstance(true)->getConfigurableAttributesAsArray($product);
				if($attrs){
					foreach($attrs as $attr) {					
						array_push($super_attr,$attr['attribute_code']);				
						$optiondata=$attr['values'];
					} 
				}
				$childProducts = $product->getTypeInstance()->getUsedProducts($product);
				foreach($childProducts as $child){				
					array_push($child_sku,$child->getSku()); 
				}
				$row['used_attribute']=implode(",",$super_attr);
				$row['child_products_sku']=implode(",",$child_sku);	
			} else if($product->getTypeId()=='bundle'){
				$optionCollection = $product->getTypeInstance(true)->getOptionsCollection($product);
				$selectionCollection = $product->getTypeInstance(true)->getSelectionsCollection($product->getTypeInstance(true)->getOptionsIds($product),$product);
				$options = $optionCollection->appendSelections($selectionCollection);
				$bundle_opt_str = "";
				$numItems = count($options);
				$item_count = 0;
				if($options){
					foreach( $options as $option ){
						$bundle_opt_str .= $option->getDefaultTitle().','.$option->getType().','.$option->getRequired().','.$option->getPosition();	
						$_selections = $option->getSelections();
						$i=0;
						if(!empty($_selections)){
							foreach( $_selections as $selection ){
								if($i==0){
								$bundle_opt_str=$bundle_opt_str.":".$selection->getSku().",".round($selection->getSelectionQty()).",".$selection->getSelectionCanChangeQty().",".$selection->getPosition().",".$selection->getIsDefault();
								}else{
								$bundle_opt_str=$bundle_opt_str."!".$selection->getSku().",".round($selection->getSelectionQty()).",".$selection->getSelectionCanChangeQty().",".$selection->getPosition().",".$selection->getIsDefault();
								}
								$i++;
							}
						}
						if(++$item_count === $numItems) {
						}else{
							$bundle_opt_str .= "|";
						}
					}
				}
				if($product->getPriceType() == 0){
					$row['price_type'] = "dynamic";
				}else{
					$row['price_type'] = "fixed";
				}
				if($product->getPriceView() == 0){
					$row['price_view'] = "price range";
				}else{
					$row['price_view'] = "as low as";
				}
				if($product->getShipmentType() == 0){
					$row['shipment_type'] = "together";
				}else{
					$row['shipment_type'] = "separately";
				}
				$row['bundle_product_options'] = $bundle_opt_str;
				}else if($product->getTypeId()=='grouped'){
					$groupedassociatedProducts = $product->getTypeInstance(true)->getAssociatedProducts($product);
					$gchild_sku=array();
					foreach($groupedassociatedProducts as $child){
						array_push($gchild_sku,"".$child->getSku());
					}
					$row['grouped_product_sku']=implode(",",$gchild_sku);
				}else if($product->getTypeId()=='downloadable'){
					$download_links = array();
					$is_sharable = array("");
					$download_samplelinks = array();
					$samplelinks = $this->helper->getObjectManager()->create('\Magento\Downloadable\Model\Sample')->getCollection()->addFieldToFilter('product_id',array('eq'=>$product->getId()))->addTitleToResult();
					if($samplelinks){
					  foreach($samplelinks as $samplelink){
					  	$download_sample_opt_str = $samplelink->getTitle().",".$samplelink->getSampleType();
						   if($samplelink->getSampleType()=='url'){
								$download_sample_opt_str = $download_sample_opt_str.",".$samplelink->getSampleUrl();
						   }else{
								$download_sample_opt_str = $download_sample_opt_str.",".ltrim($samplelink->getSampleFile(), ".");
						   }
						   array_push($download_samplelinks,$download_sample_opt_str);
					  }
					 }
					$row['downloadable_product_samples'] = implode('|',$download_samplelinks);
					$links= $this->helper->getObjectManager()->create('\Magento\Downloadable\Model\Link')->getCollection()->addFieldToFilter('product_id',array('eq'=>$product->getId()))->addTitleToResult()->addPriceToResult(true);
					if($links){
					  foreach($links as $link){
						   $download_opt_str=$link->getTitle().",".round($link->getPrice(),2).",".$link['number_of_downloads'].",".$link['is_shareable'].",".$link->getLinkType();
						   if($link->getLinkType()=='url'){
								$download_opt_str = $download_opt_str.",".$link->getLinkUrl();
						   }else{
								$download_opt_str = $download_opt_str.",".ltrim($link->getLinkFile(), '.');
						   }
							$download_opt_str = $download_opt_str.";".$link->getSampleType();
						   if($link->getSampleType()=='url'){
								$download_opt_str = $download_opt_str.",".$link->getSampleUrl();
						   }else{
								$download_opt_str = $download_opt_str.",".ltrim($link->getSampleFile(), ".");
						   }
						   array_push($download_links,$download_opt_str);
					   }
					}
					$row['downloadable_product_options'] = implode('|',$download_links);
					$row['link_can_purchase_separately'] = $product->getLinksPurchasedSeparately();
				}
				$tier_sku = $row['sku'] ;
				$tierPriceManagement = $this->helper->getObjectManager()->get('Magento\Catalog\Model\Product\TierPriceManagement');
				$productRepository = $this->helper->getObjectManager()->get('Magento\Catalog\Api\ProductRepositoryInterface');

				if($tier_sku){
					$product = $productRepository->get($tier_sku, ['edit_mode' => true]);
					$allTiers = $product->getData('tier_price');
					if(!empty($allTiers)){
						$row['cws_tier_price']='';
						foreach($allTiers as $cws_tier_str)
						{
							if($cws_tier_str['percentage_value']!='')
							{
								$price_type="percent";
								$row['cws_tier_price'] .= $cws_tier_str['cust_group'] . "=" . round($cws_tier_str['price_qty']) . "=" . $price_type . "=" . $cws_tier_str['percentage_value'] . "|";
							}else{
								$price_type="fixed";
								$row['cws_tier_price'] .= $cws_tier_str['cust_group'] . "=" . round($cws_tier_str['price_qty']) . "=" . $price_type . "=" . $cws_tier_str['price'] . "|";
							}
							
						}	
						$row['cws_tier_price']=trim($row['cws_tier_price'], "|");
					}
				}
				$customOptions = $this->helper->getObjectManager()->get('Magento\Catalog\Model\Product\Option')->getProductOptionCollection($product);
				$field_value = '';
				if($customOptions){
					foreach ($customOptions as $o) 
					{
						$optionType = $o->getType();
						$optionTitle = $o->getTitle(); 
						$is_required = $o->getIsRequire();
						$sort_order_cust=$o->getSortOrder();
						$price = $o->getPrice();
						$values = $o->getValues();
						$price_type = $o->getPriceType();
						$sku =	$o->getSku();
						$max_characters = $o->getMaxCharacters();
						$title='cws!'.$optionTitle.':'.$optionType.':'.$is_required.":".$sort_order_cust;
						$fileextension = $o->getFileExtension();
						$imagesizex = $o->getImageSizeX();
						$imagesizey = $o->getImageSizeY();
						switch($optionType){
							case 'file':
								$field_value = $price.":".$price_type.":".$sku.":".$fileextension.":".$imagesizex.":".$imagesizey;
								break;
							case 'area':
								$field_value = $price.":".$price_type.":".$sku.":".$max_characters;
								break;
							case 'date':
								$field_value = $price.":".$price_type.":".$sku;
								break;
							case 'date_time':
								$field_value = $price.":".$price_type.":".$sku;
								break;
							case 'field':
								$field_value = $price.":".$price_type.":".$sku.":".$max_characters;
								break;
							case 'time':
								$field_value = $price.":".$price_type.":".$sku;
								break;
							case 'drop_down':
							case 'radio':
							case 'multiple':
							case 'checkbox':
							default:
								$values = $o->getValues();
								$cnt = count($values);
								$i=0;			
								foreach ($values as $k => $v){
									$price = $v->getPrice();
									$price_type = $v->getPriceType();
									$sku = $v->getSku();
									$sort_order = $v->getSortOrder();
									if($cnt == 0){
										$field_value = $v->getData('default_title').":".$price.":".$price_type.":".$sku.":".$sort_order;
									}else{
										if($cnt - 1  > $i){
											$field_value = $field_value.''.$v->getData('default_title').":".$price.":".$price_type.":".$sku.":".$sort_order."|";
										}else{
											$field_value = $field_value.''.$v->getData('default_title').":".$price.":".$price_type.":".$sku.":".$sort_order;
										}
									}
									$i++;
								}
						}
						$row[$title] = $field_value;
						$field_value = "";
					}
				}
				$related_product_array = array();
				$related_product_position_array = array();
				$relatedProducts = $product->getRelatedProducts();
				if (!empty($relatedProducts)) {
					foreach ($relatedProducts as $relatedProduct) {
						array_push($related_product_array,$relatedProduct->getSku());
						array_push($related_product_position_array,$relatedProduct->getPosition());
					}
				}
				if(count($related_product_array)>0){
					$row['related_product_sku']=implode('|',$related_product_array);
					$row['related_product_position']=implode('|',$related_product_position_array);
				}
				$crosssell_product_array=array();
				$crosssell_product_position_array=array();
				$crossSellProducts = $product->getCrossSellProducts();
				if (!empty($crossSellProducts)) {
					foreach ($crossSellProducts as $crossSellProduct) {
						array_push($crosssell_product_array,$crossSellProduct->getSku());
						array_push($crosssell_product_position_array,$crossSellProduct->getPosition());
					}
				}
				if(count($crosssell_product_array)>0){
					$row['crosssell_product_sku']=implode('|',$crosssell_product_array);
					$row['crosssell_product_position']=implode('|',$crosssell_product_position_array);
				}
				$upsell_product_array=array();
				$upsell_product_position_array=array();
				$upSellProducts = $product->getUpSellProducts();
				if (!empty($upSellProducts)) {
					foreach ($upSellProducts as $upSellProduct) {
						array_push($upsell_product_array,$upSellProduct->getSku());
						array_push($upsell_product_position_array,$upSellProduct->getPosition());
					}
				}
				if(count($upsell_product_array)>0){
					$row['upsell_product_sku']=implode('|',$upsell_product_array);
					$row['upsell_product_position']=implode('|',$upsell_product_position_array);
				}
			if($export_for == "image"){
				$row = array_intersect_key($row,$this->image_related_fields );
			}

			if($export_for == "customselection"){
				if($flag == 0){
					$export_fields = array_flip($export_fields);
					$flag++;
				}
				$row = array_intersect_key($row,$export_fields);
			}


			if($export_for == "inventory"){
				$row = array_intersect_key($row,$this->inventory_related_fields );
			}
			if($export_for == "price"){
				$row = array_intersect_key($row,$this->price_related_fields );
			}
			if($export_for == "ucrFields"){
				$row = array_intersect_key($row,$this->releted_up_cross_sell_fields );
			}else{
				unset($row["related_product_sku"]);
				unset($row["related_product_position"]);
				unset($row["crosssell_product_sku"]);
				unset($row["crosssell_product_position"]);
				unset($row["upsell_product_sku"]);
				unset($row["upsell_product_position"]);
			}
			 if(isset($row['image'])){
				 if($row['image'] == "no_selection"){
					 $row['image'] = "";
				 }
			 }
			 if(isset($row['small_image'])){
			 	if($row['small_image'] == "no_selection"){
					 $row['small_image'] = "";
			 	}
			 }
			 if(isset($row['thumbnail'])){
			 	if($row['thumbnail'] == "no_selection"){ 
				 	$row['thumbnail'] = "";
			 	}
			 }
			 
			array_push($this->product_data_collection,array_filter($row, 'strlen'));	
		}

		return $_product_collection->getLastPageNumber();
	}
	public function getStore($store_export_id)
    {
        if (is_null($this->store)) {
            try {
				$store = $this->storeManager->getStore($store_export_id);
            } catch (\Exception $e) {

            }
            $this->store = $store;
        }
        return $this->store;
    }
	
	public function getAttribute($code)
    {
        if (!isset($this->attributes[$code])) {
            $this->attributes[$code] = $this->helper->getObjectManager()->create('\Magento\Catalog\Model\Product')->getResource()->getAttribute($code);
        }
        return $this->attributes[$code];
    }	
}
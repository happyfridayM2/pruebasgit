<?php
namespace Magebees\Products\Model\Validator\Validate;

class Importvalidator extends \Magento\Framework\Model\AbstractModel
{
	protected 	$error = array();
	protected 	$bypass_import = false;
	protected 	$website_list = array();
	protected 	$attribute_sets = array();
	protected 	$storeManager;
	private   	$messageManager;
	protected 	$request;
	protected 	$helper;
	
	public function __construct(
	\Magento\Store\Model\StoreManagerInterface $storeManager,
	\Magento\Framework\App\Request\Http $request,
	\Magento\Framework\Message\ManagerInterface $messageManager,
	\Magebees\Products\Helper\Data $helper
	) {
		$this->storeManager = $storeManager;
		$this->initWebsites();
		$this->request = $request;
		$this->helper = $helper;
		$this->initAttributSets();
		$this->messageManager = $messageManager;
	}
	public function initWebsites()
	{	
		foreach ($this->storeManager->getWebsites() as $website) {
			$this->website_list[]=$website->getCode();
		}
	}
	public function initAttributSets()
	{
        $coll = $this->helper->getObjectManager()->create('\Magento\Catalog\Model\Product\AttributeSet\Options');
		$attributeSetCollection = $coll->toOptionArray();
		foreach ($attributeSetCollection as $attribute) {
			$this->attribute_sets[]= $attribute['label'];
		}
	}
	public function runValidator(array $data,$timestamp,$behavior)
	{
		$filesystem = $this->helper->getObjectManager()->get('Magento\Framework\Filesystem');
		$reader = $filesystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::VAR_DIR);
		$flagDir = $reader->getAbsolutePath("import/".'cws_product_import_flag_validator_do_not_delete-'.$timestamp.'.flag');
		if(file_exists($flagDir)){

		}else{
				$flag_file = fopen($flagDir, "w");
				$txt = "Do not delete this flag.";
				fwrite($flag_file, $txt);
				fclose($flag_file);
				$this->helper->getObjectManager()->create('\Magebees\Products\Model\ResourceModel\Validationlog')->truncate();
		}
		$this->saveRow($this->helper->getUnserializeData($data['product_data']),$behavior);
		foreach($this->error as $e)
		{
			try {
				$model = $this->helper->getObjectManager()->create('\Magebees\Products\Model\Validationlog');
				$model->setErrorInformation($e['txt']);
				$model->setErrorType($e['error_level']);
				$model->setProductSku($e['product_sku']);
				$model->save();
			} catch (\Exception $e) {
				
			}
		}
		return array('error'=>$this->error,'bypass'=>$this->bypass_import);
	}
	public function saveRow(array $importData,$behavior)
    {
		$row_cws_header=$importData['cws_row_header'];
		unset($importData['cws_row_header']);
		if(!array_key_exists("sku",$importData)){
			$message = "Your CSV file is not in proper Comma Separated format. Please use Google Sheet to convert it properly.";
			array_push($this->error,array('txt'=>$message,'product_sku'=>'CSV File Related Issue','error_level'=>1));
			return;	
		}
		foreach($importData as $key => $value):
			$importData[$key] = trim($value);
		endforeach;
		if ($importData['sku'] == "" && $importData['sku'] == NULL) {
			$message = 'Product SKU field is empty';
			$this->bypass_import=true;
			array_push($this->error,array('txt'=>$message,'product_sku'=>$importData['sku'],'error_level'=>1));
			return;		
		}else{		
			$product_sku_row_shower='Product SKU: '.$importData['sku'];
		}
		$product = $this->helper->getObjectManager()->create('\Magento\Catalog\Model\Product')->loadByAttribute('sku',$importData['sku']);
		if($product) {
			if($behavior=='append'){
			}
		}else{
			if($behavior=='delete'){
			$this->bypass_import=true;
			$message = 'Product SKU# :'.$importData['sku'].' does not exists.';
			array_push($this->error,array('txt'=>$message,'product_sku'=>$product_sku_row_shower,'error_level'=>1));		
			return;						
			}
		}
		if (empty($importData['websites'])) {
			$message = 'Required field websites not defined.';
			array_push($this->error,array('txt'=>$message,'product_sku'=>$product_sku_row_shower,'error_level'=>1));
		}else{
			$websites=explode(",",$importData['websites']);
			foreach($websites as $website){
				if(!in_array($website,$this->website_list)){
					$message = 'Website code "'.$website.'" not exists.';
					array_push($this->error,array('txt'=>$message,'product_sku'=>$product_sku_row_shower,'error_level'=>1));
				}
			}
		}
		if (empty($importData['store'])) {
			$message = 'Required field store not defined.';
			array_push($this->error,array('txt'=>$message,'product_sku'=>$product_sku_row_shower,'error_level'=>1));						
		}else{
			$store_available=false;
			$store_ids = $this->storeManager->getStores();
			foreach ($store_ids as $s_id)
			{
				if ($s_id->getCode()==$importData['store'])
				{
					$store_available = true;
				}else if($importData['store']=='admin')
				{
					$store_available = true;
				}
			}
			if(!$store_available)
			{
				$message = 'Store Code "'.$importData['store'].'" doest not exist.';
				array_push($this->error,array('txt'=>$message,'product_sku'=>$product_sku_row_shower,'error_level'=>0));
			}
		}
		if (isset($importData['categories']) && $importData['categories']==null) {
			$message = 'categories field having null value.';
			array_push($this->error,array('txt'=>$message,'product_sku'=>$product_sku_row_shower,'error_level'=>0));
		}
		if (isset($importData['visibility']) && $importData['visibility']==null) {
			$message = 'visibility field having null value.';
			array_push($this->error,array('txt'=>$message,'product_sku'=>$product_sku_row_shower,'error_level'=>0));
		}
		if (isset($importData['tax_class_id']) && $importData['tax_class_id']==null) {
			$no_skip=true;				
			if($product)
			{
				if($product->getTypeId()=='grouped')
				{
					$no_skip=false;
				}				
			}
			if($importData['type']=='grouped')
			{
					$no_skip=false;
			}
			if($no_skip){
				$message = 'tax_class_id field having null value.';
				array_push($this->error,array('txt'=>$message,'product_sku'=>$product_sku_row_shower,'error_level'=>0));
			}
		}
		if (isset($importData['status']) && $importData['status']==null) {
			$message = 'status field having null value.';
			array_push($this->error,array('txt'=>$message,'product_sku'=>$product_sku_row_shower,'error_level'=>0));
		}
		if (isset($importData['weight']) && $importData['weight']==null) 
		{
			$no_skip=true;				
			if($product)
			{
				if($product->getTypeId()=='configurable' || $product->getTypeId()=='downloadable' || $product->getTypeId()=='grouped' || $product->getTypeId()=='virtual')
				{
					$no_skip=false;
				}				
			}
			if($importData['type']=='configurable' || $importData['type']=='downloadable' || $importData['type']=='grouped' || $importData['type']=='virtual')
			{
				$no_skip=false;
			}
			if($no_skip){
				$message = 'weight field having null value.';
				array_push($this->error,array('txt'=>$message,'product_sku'=>$product_sku_row_shower,'error_level'=>0));
			}
		}
		if(isset($importData['price']) && $importData['price']==null){
			if ($importData['price']==null) {		
				$no_skip=true;				
				if($product)
				{
					if($product->getTypeId()=='grouped')
					{
						$no_skip=false;
					}				
				}
				if($importData['type']=='grouped')
				{
					$no_skip=false;
				}
				
				if($no_skip){
					$message = 'price field having null value.';
					array_push($this->error,array('txt'=>$message,'product_sku'=>$product_sku_row_shower,'error_level'=>0));
				}
			}else{		
				if(!is_numeric($importData['price']))
				{
					$message = 'price can not have non-integer value.';
					$this->bypass_import=true;
					array_push($this->error,array('txt'=>$message,'product_sku'=>$product_sku_row_shower,'error_level'=>1));
				}
			}
		}
		if(isset($importData['attribute_set']) && $importData['attribute_set']==null){
			if ($importData['attribute_set']==null) {
				$message = 'attribute_set field having null value.';
				array_push($this->error,array('txt'=>$message,'product_sku'=>$product_sku_row_shower,'error_level'=>0));									
			}
		}else if(isset($importData['attribute_set']) && $importData['attribute_set']!=null){			
				if(!in_array($importData['attribute_set'],$this->attribute_sets))
				{
					$message = 'Attribute Set : '.$importData['attribute_set'].' does not exists.';
					array_push($this->error,array('txt'=>$message,'product_sku'=>$product_sku_row_shower,'error_level'=>1));
					$this->bypass_import=true;
				}
			}
		if(isset($importData['categories_ids']) && $importData['categories_ids']==null){			
			if ($importData['categories_ids']==null) {
				$message = 'categories_ids field having null value.';
				array_push($this->error,array('txt'=>$message,'product_sku'=>$product_sku_row_shower,'error_level'=>0));
			}else{
				$category_list=explode(",",$importData['categories_ids']);
				foreach($category_list as $category){
					if(!is_numeric($category))
					{
						$message = 'categories_ids can not have non-integer value.';
						$this->bypass_import=true;
						array_push($this->error,array('txt'=>$message,'product_sku'=>$product_sku_row_shower,'error_level'=>1));
					}
				}
			}
		}
		$base_path = $this->helper->getMediaImportDirPath();
		if(isset($importData["image"]) != "" && substr_count($importData["image"],"/") == 0){$importData["image"] = "/".$importData["image"]; }
			if(isset($importData["small_image"]) != "" && substr_count($importData["small_image"],"/") == 0){ $importData["small_image"] = "/".$importData["small_image"]; }
			if(isset($importData["thumbnail"]) != "" && substr_count($importData["thumbnail"],"/") == 0){ $importData["thumbnail"] = "/".$importData["thumbnail"]; }
			if(isset($importData["gallery"]) != ""){ 
				$gallery_image = array();
				$gallery_image = explode("|",$importData["gallery"]);

				$temp = "";
				foreach($gallery_image as $value){
					if(substr_count(trim($value),"/") == 0){
						$temp .= "/".$value."|";
					}else{
						$temp .= $value."|";
					}
				}
				$importData["gallery"] = $temp;
			}
		if (isset($importData['image']) && $importData['image']!=null) {
			$image_path = $base_path.$importData['image'];
			if(!file_exists($image_path)){
				$message = 'Image: '.$importData['image'].' does not exist in folder.';
				array_push($this->error,array('txt'=>$message,'product_sku'=>$product_sku_row_shower,'error_level'=>1));
			}				
		}
		if (isset($importData['small_image']) && $importData['small_image']!=null) {
			$small_image_path = $base_path.$importData['small_image'];
			if(!file_exists($small_image_path)){
				$message = 'Image: '.$importData['small_image'].' does not exists in folder.';
				array_push($this->error,array('txt'=>$message,'product_sku'=>$product_sku_row_shower,'error_level'=>1));
			}			
		}
		if (isset($importData['thumbnail']) && $importData['thumbnail']!=null) {
			$thumbnail_path = $base_path.$importData['thumbnail'];
			if(!file_exists($thumbnail_path)){
				$message = 'Image: '.$importData['thumbnail'].' does not exists in folder.';
				array_push($this->error,array('txt'=>$message,'product_sku'=>$product_sku_row_shower,'error_level'=>1));
			}
		}
		if (isset($importData['gallery']) && $importData['gallery']!=null) {
			$galler_images=explode("|",$importData['gallery']);
			foreach($galler_images as $galler_image)
			{
				$galler_image_path = $base_path.$galler_image;
				if(!file_exists($galler_image_path)){
					$message = 'Image: '.$galler_image.' does not exists in folder.';
					array_push($this->error,array('txt'=>$message,'product_sku'=>$product_sku_row_shower,'error_level'=>1));
				}
			}
		}
		if(isset($importData['related_product_sku']) && $importData['related_product_sku']!=null)
		{
			$related_product_sku = $importData['related_product_sku'];
			$related_product_sku_single = explode('|', $related_product_sku);
			$r_data = array();
			foreach($related_product_sku_single as $r_sku)
			{
				$aRelatedProduct = $this->helper->getObjectManager()->create('\Magento\Catalog\Model\Product')->loadByAttribute('sku',$r_sku);
				if(!isset($aRelatedProduct['entity_id'])){
					$message = "Product SKU: ".$r_sku." does not exists. Can't assign to related product.";
					array_push($this->error,array('txt'=>$message,'product_sku'=>$importData['sku']));
				}
			}
		}
		if(isset($importData['crosssell_product_sku']) && $importData['crosssell_product_sku']!=null)
		{
			$crosssell_product_sku = $importData['crosssell_product_sku'];
			$crosssell_product_sku_single = explode('|', $crosssell_product_sku);
			$c_data = array();
			foreach($crosssell_product_sku_single as $c_sku)
			{
				$aCrossesellProduct = $this->helper->getObjectManager()->create('\Magento\Catalog\Model\Product')->loadByAttribute('sku',$c_sku);

				if(!isset($aCrossesellProduct['entity_id'])){
					$message="Product SKU: ".$c_sku." does not exists. Can't assign to cross-sell product.";
					array_push($this->error,array('txt'=>$message,'product_sku'=>$importData['sku']));			
				}
			}
		}
		if(isset($importData['upsell_product_sku']) && $importData['upsell_product_sku']!=null)
		{
			$upsell_product_sku = $importData['upsell_product_sku'];
			$upsell_product_sku_single = explode('|', $upsell_product_sku);
			$u_data = array();
			$z = 1;
			foreach($upsell_product_sku_single as $u_sku)
			{
				$aUpesellProduct = $this->helper->getObjectManager()->create('\Magento\Catalog\Model\Product')->loadByAttribute('sku',$u_sku);
				if(!isset($aUpesellProduct['entity_id'])){
					$message="Product SKU: ".$u_sku." does not exists. Can't assign to up-sell product.";
					array_push($this->error,array('txt'=>$message,'product_sku'=>$importData['sku']));
				}
			}
		}
		if(isset($importData['used_attribute']) && $importData['used_attribute']!=null)
		{		
			$used_attribute=explode(",", $importData['used_attribute']);
			foreach($used_attribute as $attrCode){
				$super_attribute = $this->helper->getObjectManager()->create('\Magento\Eav\Model\Entity\Attribute')->loadByCode('catalog_product','name');
				if($super_attribute){
					if($super_attribute->getId()===null){
					   $message='Attribute with code: '.$attrCode.' is not configurable attribute.';
					   array_push($this->error,array('txt'=>$message,'product_sku'=>$importData['sku'],'error_level'=>1));
					}					
				}else{
				   $message='Attribute Code: '.$attrCode.' doest not exists. ';
				   array_push($this->error,array('txt'=>$message,'product_sku'=>$importData['sku'],'error_level'=>1));
				}
			}
		}
		if(isset($importData['downloadable_product_options']) && $importData['downloadable_product_options']!=null)
		{		
			$downloadable_product_main_data = explode('|',$importData['downloadable_product_options']);
			foreach ($downloadable_product_main_data as $single) {
				$single_row = explode(";",$single);
				$linkdata = $single_row[0];	
				$linkdata = explode(",",$linkdata);	
				$sampledata = $single_row[1];		
				$sampledata = explode(",",$sampledata);						
				$linkimagename = $linkdata[5];	
				$sampleimagename = $sampledata[1];
				if($linkdata[4]=='file' || $linkdata[4]==''){
					$downloadFile = $base_path.$linkimagename;
					if (!file_exists($downloadFile)) {
					   $message='File('.$linkimagename.') Does Not Exist. SKU: '.$importData['sku'];
					   array_push($this->error,array('txt'=>$message,'product_sku'=>$importData['sku'],'error_level'=>1));
					}	
				}
				if($sampledata[0]=='file'){
					$sampleFile = $base_path.$sampleimagename;
					if (!file_exists($sampleFile)) {
					   $message='Sample File('.$sampleimagename.') Does Not Exist. SKU: '.$importData['sku'];
					   array_push($this->error,array('txt'=>$message,'product_sku'=>$importData['sku'],'error_level'=>1));	
					}								
				}		
			}
		}
	}
}
<?php
namespace Magebees\Products\Helper;
use Magento\Framework\App\Filesystem\DirectoryList;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $product;
    protected $productLinkInterfaceFactory;
    protected $urlRewriteCollection;
    protected $directoryList;
 
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
		\Magento\Catalog\Model\Product $product,
		\Magento\Catalog\Api\Data\ProductLinkInterfaceFactory $productLinkInterfaceFactory,
        \Magento\UrlRewrite\Model\UrlRewriteFactory $urlRewriteCollection,
        DirectoryList $directoryList,
        \Magento\Framework\ObjectManagerInterface $objectmanager
    )
    {
		$this->product 					   	= $product;
		$this->productLinkInterfaceFactory 	= $productLinkInterfaceFactory;
        $this->urlRewriteCollection 		= $urlRewriteCollection;
        $this->directoryList 				= $directoryList;
        $this->objectmanager 				= $objectmanager;
        parent::__construct($context);
    }
	
    public function assignProductImage($imageName, $columnName, $imageArray = array()) 
	{
		if($imageName=="") { return $imageArray; }
		if($imageName == "no_selection") {
			if (array_key_exists($imageName, $imageArray)) {
				array_push($imageArray[$imageName],$columnName);
			} else {
				$imageArray[$imageName] = array($columnName);
			}
			return $imageArray; 
		}
		$importDir = $this->getMediaImportDirPath();
		if($columnName == "media_gallery") {
			$galleryData = explode('|', $imageName);
			foreach( $galleryData as $gallery_img ) {
				if($gallery_img != ""){
					if(file_exists($importDir.$gallery_img)) {  
						if (array_key_exists($gallery_img, $imageArray)) {
							array_push($imageArray[$gallery_img],$columnName);
						} else {
							$imageArray[$gallery_img] = array($columnName);
						}
					} else {
						return $imageArray;
					}
				}
			}
		} else {
			if(file_exists($importDir.$imageName)) {  
				if (array_key_exists($imageName, $imageArray)) {
					array_push($imageArray[$imageName],$columnName);
				} else {
					$imageArray[$imageName] = array($columnName);
				}
			} else {
				return $imageArray; 
			}
		}
		return $imageArray;
	}

	public function getMediaImportDirPath()
    {
        return $this->directoryList->getPath(DirectoryList::MEDIA) . DIRECTORY_SEPARATOR . 'import';
    }
	
   	public function checkUrlKeyExists($storeId, $urlKey) 
   	{
		$urlrewritesCollection = $this->urlRewriteCollection->create()->getCollection();
		if($storeId !=0) {
			$urlrewritesCollection->addFieldToFilter('store_id', $storeId);
		} 
		$urlrewritesCollection->addFieldToFilter('entity_type', 'product')
							  ->addFieldToFilter('request_path', $urlKey.'.html') //html
							  ->setPageSize(1);
		return $urlrewritesCollection->getFirstItem();
	}
	
	public function AssignReCsUpProduct($ReCsUpProduct,$sku,$type)
	{
		$ReCsUpProductString = explode('|',$ReCsUpProduct);
		$linkReCsUpData = array();
		foreach($ReCsUpProductString as $linkedSku){
			if($linkedSku!="") {
				$id = $this->product->getIdBySku($linkedSku);
				if($id > 0) {
					$linkData = $this->productLinkInterfaceFactory->create()
						->setSku($sku)
						->setLinkedProductSku($linkedSku)
						->setLinkType($type);
					$linkReCsUpData[] = $linkData;
				} else {
					//echo "The column contains sku that does NOT exist";
				}
			}
		}
		return $linkReCsUpData;
	}
	public function getMultiselectattvalue(){
		$multiDelimiterValue =  $this->scopeConfig->getValue('magebeesproducts/general/multi_delimiter', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		return $multiDelimiterValue;
	}
	public function getObjectManager()
    {
        return $this->objectmanager;
    }
    public function getSerializeData($data)
	{
		return json_encode($data);		
	}
	public function getUnserializeData($data)
	{
		return json_decode($data,true);		
	}
}
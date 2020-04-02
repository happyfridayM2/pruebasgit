<?php
namespace Magebees\Products\Controller\Adminhtml\Import;

class Validaterecord extends \Magento\Backend\App\Action
{
    protected $registry = null;
    protected $resultPageFactory;
    protected $helper;
    
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Registry $registry,
        \Magebees\Products\Helper\Data $helper
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->registry = $registry;
        $this->helper = $helper;
        parent::__construct($context);
    }

    public function execute()
    {
        $direction=$this->getRequest()->getParam('direction');
        $timestamp = $this->getRequest()->getParam('timestamp');
        $behavior = $this->getRequest()->getParam('behavior');
        $imagelocation = $this->getRequest()->getParam('imagelocation');
        if (isset($imagelocation) && $imagelocation != "") {
            $catalogSession = $this->_objectManager->create('Magento\Catalog\Model\Session');
            $catalogSession->setMyvalue($imagelocation);
        }
        $url_reditect = "";
        if ($direction=='Validated') {
            $this->_objectManager->create('\Magebees\Products\Model\ResourceModel\Importlog')->truncate();
            if ($this->getRequest()->getParam('validationBehavior')=='skip') {
                return;
            }
            $max = ini_get('max_execution_time')/15;
            $collection = $this->_objectManager->create('\Magebees\Products\Model\Profiler')->getCollection()->addFieldToFilter('validate', '0');
            if ($max<10) {
                $collection->getSelect()->limit(10);
            } else {
                $collection->getSelect()->limit(20);
            }
            $next_flag=true;
            $error_ctn=0;
            foreach ($collection as $d) {
                $error = $this->_objectManager->create("\Magebees\Products\Model\Validator\Validate\Importvalidator")->runValidator($d->getData(), $timestamp, $behavior);
                if (count($error['error'])!=0) {
                    //$error_ctn++;
                }
                $d->setBypassImport($error['bypass']);
                $d->setValidate(true);
                $d->save();
            }
            $url_reditect = '';
            if (count($collection)==0) {
                //$vardir = \Magento\Framework\App\ObjectManager::getInstance();
                $filesystem = $this->_objectManager->get('Magento\Framework\Filesystem');
                $reader = $filesystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::VAR_DIR);
                $flagDir = $reader->getAbsolutePath("import/").'cws_product_import_flag_validator_do_not_delete-'.$timestamp.'.flag';
                
                if (file_exists($flagDir)) {
                    unlink($flagDir);
                }
                $next_flag=false;
                $url_reditect = $this->getUrl('products/import/index', ['active_tab'=>'validationlog','show_import_button'=>'true','behavior'=>$this->getRequest()->getParam('behavior'),'validationBehavior'=>$this->getRequest()->getParam('validationBehavior')]);
            }
            
            //Parag added
            $count = count($this->_objectManager->create('\Magebees\Products\Model\Profiler')->getCollection());
            $imported_products=count($collection);
            $this->getResponse()->representJson($this->_objectManager->get('Magento\Framework\Json\Helper\Data')->jsonEncode(['count'=>$count,'next'=>$next_flag,'imported'=>$imported_products,'error'=>$error_ctn,'url'=>$url_reditect]));
        } elseif ($direction=='Imported') {
            //$this->_objectManager->create('\Magebees\Products\Model\ResourceModel\Importlog')->truncate();//Added 17-01-2016
            $max = ini_get('max_execution_time')/15;
            $this->registry->register('cws_import_mode', true);
            $collection = $this->_objectManager->create('\Magebees\Products\Model\Profiler')->getCollection()->addFieldToFilter('validate', '1')->addFieldToFilter('imported', '0');
            //$count1 = $collection->count();
            if ($max<5) {
                $collection->getSelect()->limit(10);
            } else {
                $collection->getSelect()->limit(20);
            }
            
            $next_flag=true;
            $error_ctn=0;
            if (count($collection)==0) {
                $next_flag=false;
                $this->deleteImportFlag();
                $url_reditect=$this->getUrl('*/*/index', ['active_tab'=>'importlog','show_import_alert_box'=>'true']);
            } else {
                $this->createImportFlagIfNoExist();
            }
            $importproduct_adapter = $this->_objectManager->create('\Magebees\Products\Model\Convert\Adapter\Importproducts');
            
            foreach ($collection as $d) {
                if ($d->getBypassImport()) {
                    $error_ctn++;
                $product_data = $this->helper->getUnserializeData($d->getProductData());
                    $error_model = $this->_objectManager->create('\Magebees\Products\Model\Importlog');
                    $error_model->setErrorInformation('Product SKU: '.$product_data['sku'].' not imported due to major error.');
                    $error_model->setErrorType(1);
                    $error_model->setProductSku($product_data['sku']);
                    $error_model->save();
                    $d->setImported(true);
                    $d->save();
                    continue;
                }
                
                $error = $importproduct_adapter->runImport($d->getData());
                
                if (is_array($error)) {
                    if (count($error) !=0) {
                        $error_ctn++;
                    }
                }
                $d->delete();
            }
            //'count1'=>$count1,

            $this->getResponse()->representJson($this->_objectManager->get('Magento\Framework\Json\Helper\Data')->jsonEncode(['next'=>$next_flag,'imported'=>count($collection),'error'=>$error_ctn,'url'=>$url_reditect]));
        }
    }
    
    public function createImportFlagIfNoExist()
    {
        $timestamp = $this->getRequest()->getParam('timestamp');
        $filesystem = $this->_objectManager->get('Magento\Framework\Filesystem');
        $reader = $filesystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::VAR_DIR);
        $flagDir = $reader->getAbsolutePath("import/".'cws_product_import_flag_do_not_delete-'.$timestamp.'.flag');
        
        if (file_exists($flagDir)) {
            $flag_data = file_get_contents($flagDir);
            $load_product_related_info = $this->helper->getUnserializeData($flag_data);

            if ($load_product_related_info['product_type']!==null) {
                $this->registry->register('product_type', $load_product_related_info['product_type']);
            } else {
                $this->loadProductType();
            }
            if ($load_product_related_info['product_attribute_set']!==null) {
                $this->registry->register('product_attribute_set', $load_product_related_info['product_attribute_set']);
            } else {
                $this->loadAttributeSet();
            }
            if ($load_product_related_info['store']!==null && $load_product_related_info['store_code']!==null) {
                $this->registry->register('store_code', $load_product_related_info['store_code']);
                $this->registry->register('store', $load_product_related_info['store']);
            } else {
                $this->loadStores();
            }
            if ($load_product_related_info['load_basic_param']!==null) {
                $this->registry->register('load_basic_param', $load_product_related_info['load_basic_param']);
            } else {
                $this->initBasicParam();
            }
        } else {
                $flag_file = fopen($flagDir, "w");
                $this->loadAttributeSet();
                $this->loadProductType();
                $this->loadStores();
                $this->initBasicParam();
                $data = [];
                $data['product_type'] = $this->registry->registry('product_type');
                $data['product_attribute_set'] = $this->registry->registry('product_attribute_set');
                $data['store_code'] = $this->registry->registry('store_code');
                $data['store'] = $this->registry->registry('store');
                $data['load_basic_param'] = $this->registry->registry('load_basic_param');
            fwrite($flag_file, $this->helper->getSerializeData($data));

            fclose($flag_file);
            $this->_objectManager->create('\Magebees\Products\Model\ResourceModel\Importlog')->truncate();
        }
    }
    public function loadProductType()
    {
        $_productTypes = [];
        $options = $this->_objectManager->create('\Magento\Catalog\Model\Product\Type')->getOptionArray();
        foreach ($options as $k => $v) {
            $_productTypes[$k] = $k;
        }
        $this->registry->register('product_type', $_productTypes);
    }
    public function loadAttributeSet()
    {
        $_productAttributeSets = [];
        $collection = $this->_objectManager->create('\Magento\Catalog\Model\Product\AttributeSet\Options')->toOptionArray();
        foreach ($collection as $key => $set) {
            $_productAttributeSets[$set['label']] = $set['value'];
        }
        $this->registry->register('product_attribute_set', $_productAttributeSets);
    }
    public function loadStores()
    {
        $storeManager = $this->_objectManager->get('Magento\Store\Model\StoreManagerInterface');
        $_stores = $storeManager->getStores(true, true);
        $this->registry->register('store_code', $_stores);
        foreach ($_stores as $code => $store) {
            $_storesIdCode[$store->getId()] = $code;
        }
        $this->registry->register('store', $_storesIdCode);
    }
    public function initBasicParam()
    {
        $this->registry->register('load_basic_param', $this->getAttributeValue());
    }
    public function getAttributeValue()
    {
        $attributeValue = [];
        $simple = ['qty','min_qty','use_config_min_qty','is_qty_decimal','is_decimal_divided','backorders','use_config_backorders','min_sale_qty','use_config_min_sale_qty','max_sale_qty','use_config_max_sale_qty','is_in_stock','notify_stock_qty','use_config_notify_stock_qty','manage_stock','use_config_manage_stock'];
        $downloadable = ['qty','min_qty','use_config_min_qty','is_qty_decimal','backorders','use_config_backorders','min_sale_qty','use_config_min_sale_qty','max_sale_qty','use_config_max_sale_qty','is_in_stock','notify_stock_qty','use_config_notify_stock_qty','manage_stock','use_config_manage_stock'];
        $configurable = ['is_in_stock','manage_stock','use_config_manage_stock'];
        $requiredFields = ['name','description','short_description','weight','price','tax_class_id'];
        $_ignoreFields = ['attribute_set','type','category_ids','entity_id','attribute_set_id','type_id','required_options'];
        $_toNumber = ['qty','min_qty','min_sale_qty','max_sale_qty'];
        $attributeValue['_inventoryFieldsProductTypes']['simple'] = $simple;
        $attributeValue['_inventoryFieldsProductTypes']['virtual'] = $simple;
        $attributeValue['_inventoryFieldsProductTypes']['downloadable'] = $downloadable;
        $attributeValue['_inventoryFieldsProductTypes']['configurable'] = $configurable;
        $attributeValue['_inventoryFieldsProductTypes']['bundle'] = $configurable;
        $attributeValue['_inventoryFieldsProductTypes']['grouped'] = $configurable;
        $attributeValue['_inventoryFields'] = $simple;
        $attributeValue['_requiredFields'] = $requiredFields;
        $attributeValue['_ignoreFields'] = $_ignoreFields;
        $attributeValue['_toNumber'] = $_toNumber;

        $serializeAttributeValue = $this->helper->getSerializeData($attributeValue);
        $unserializeAttributeValue = $this->helper->getUnserializeData($serializeAttributeValue);

        return $unserializeAttributeValue;
    }
    public function deleteImportFlag()
    {
        $timestamp = $this->getRequest()->getParam('timestamp');
        $filesystem = $this->_objectManager->get('Magento\Framework\Filesystem');
        $reader = $filesystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::VAR_DIR);
        $flagDir = $reader->getAbsolutePath("import/".'cws_product_import_flag_do_not_delete-'.$timestamp.'.flag');
        if (file_exists($flagDir)) {
                unlink($flagDir);
        }
    }
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magebees_Products::import');
    }
}
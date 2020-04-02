<?php
namespace Magebees\Navigationmenu\Controller\Adminhtml\Menucreator;
use Magento\Framework\App\Filesystem\DirectoryList;

class Save extends \Magento\Backend\App\Action
{
	protected $_collection;
	 protected $_scopeConfig;
	protected $_coreSession;
	public function __construct( 
	\Magento\Backend\App\Action\Context $context,
	\Magento\Catalog\Model\ResourceModel\Product\Collection $collection,
 	\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
	\Magento\Framework\Session\SessionManagerInterface $coreSession,
	\Magebees\Navigationmenu\Model\Menucreator $menucreator,
	array $data = [])
	{
		$this->_collection = $collection;
		$this->_scopeConfig = $scopeConfig;
		$this->_coreSession = $coreSession;
		$this->menucreator = $menucreator;
		parent::__construct($context);
	}
	
	
	public function execute()
    {	
		
		$currentDate = date('Y-m-d h:i:s');	
       	$data=$this->getRequest()->getPost()->toarray();
		
		if($data)
		{
         	$model = $this->_objectManager->create('Magebees\Navigationmenu\Model\Menucreator');
			if(isset($data['menu_id']))
			{
			if ($data['menu_id'] != "") {
            	 $this->menucreator->load($data['menu_id']);
				}
			}
			$files = $this->getRequest()->getFiles()->toArray();
           if(isset($files['image']['name']) && $files['image']['name'] != '') {
               try 
        		{
        			$image=$files['image']['name'];
        			$temp = explode(".", $image);
        			$file = current($temp);
        			$extension = end($temp);
						
        			$profile_new=$file. date('mdYHis').".".$extension;
        		    $files['image']['name'] = $profile_new;
					
        			$uploader = $this->_objectManager->create('Magento\MediaStorage\Model\File\Uploader', array('fileId' =>'image'));
				    $uploader->setAllowCreateFolders(true);
        			$uploader->setAllowedExtensions(array('jpg', 'jpeg', 'gif', 'png'));
        			$uploader->setAllowRenameFiles(false);
        			$uploader->setFilesDispersion(true);
        			$mediaDirectory = $this->_objectManager->get('Magento\Framework\Filesystem')
        			->getDirectoryRead(DirectoryList::MEDIA);
        			$result = $uploader->save($mediaDirectory->getAbsolutePath('navigationmenu/image'));
        			unset($result['tmp_name']);
        			$data['image'] = $result['file'];
        		}
        		
        		catch (\Exception $e)
				{
					$this->messageManager->addError($e->getMessage());
                	$this->messageManager->addException($e, __('Please Select Valid File.'));
				}
			}else{
                unset($data['image']);
            }
			
            $this->menucreator->setTitle($data['title']);
            $this->menucreator->setGroupId($data['group_id']);
            $this->menucreator->setDescription($data['description']);
            //$model->setImage($data['image']);
            $this->menucreator->setType($data['type']);
			if(isset($data['category_id']))
			{
            $this->menucreator->setCategoryId($data['category_id']);
			}
			if(isset($data['cmspage_identifier']))
			{
            $this->menucreator->setCmspageIdentifier($data['cmspage_identifier']);
			}
			if(isset($data['staticblock_identifier']))
			{
            $this->menucreator->setStaticblockIdentifier($data['staticblock_identifier']);
			}
			if(isset($data['product_id']))
			{
            $this->menucreator->setProductId($data['product_id']);
			}
            $this->menucreator->setParentId($data['parent_id']);
			if(isset($data['url_value']))
			{
            $this->menucreator->setUrlValue($data['url_value']);
			}
			if(isset($data['usedlink_identifier']))
			{
            $this->menucreator->setUsedlinkIdentifier($data['usedlink_identifier']);
			}
			if(isset($data['image_status']))
			{
            $this->menucreator->setImageStatus($data['image_status']);
			}
			if(isset($data['show_category_image']))
			{
            $this->menucreator->setShowCategoryImage($data['show_category_image']);
			}
			if(isset($data['show_custom_category_image']))
			{
            $this->menucreator->setShowCustomCategoryImage($data['show_custom_category_image']);
			}
			else
			{
				$this->menucreator->setShowCustomCategoryImage("0");
			}
			if(isset($data['position']))
			{
            $this->menucreator->setPosition($data['position']);
			}
			if(isset($data['class_subfix']))
			{
            $this->menucreator->setClassSubfix($data['class_subfix']);
			}
			if(isset($data['permission']))
			{
           $this->menucreator->setPermission($data['permission']);
			}
			if(isset($data['status']))
			{
            $this->menucreator->setStatus($data['status']);
			}
           if(isset($data['target']))
			{
            $this->menucreator->setTarget($data['target']);
			}
             $this->menucreator->setTextAlign($data['text_align']);
             $this->menucreator->setSubcolumnlayout($data['subcolumnlayout']);
           
            try {
				if ( $this->menucreator->getCreatedTime() == NULL ||  $this->menucreator->getUpdateTime() == NULL) 
					{
						 $this->menucreator->setCreatedTime($currentDate)
								->setUpdateTime($currentDate);
					} 
					else 
					{
						 $this->menucreator->setUpdateTime($currentDate);
					}	
				
				
				 if(isset($files['image']['name']) && ($files['image']['name'] != '') && isset($result['file'])){	
				 $this->menucreator->setImage($result['file']);
				}
				
				
				
			$storeids="";
			if(isset($data['storeids']))
			{
			if(in_array("0", $data['storeids']))
			{
			$defaultstore_id="0".",";
							$storeids = '';
							$storemanager= $this->_objectManager->create('Magento\Store\Model\StoreManagerInterface');
							$allStores =$storemanager->getStores();
							/* Get All Store Id in the storeids*/
							foreach ($allStores as $_eachStoreId => $val) 
							{
								$_storeId = $storemanager->getStore($_eachStoreId)->getId();
								$storeids.=$_storeId.",";
							}
							/* Add O as store Id for all the store
							*/
							$storeids = $defaultstore_id.$storeids;
							 $this->menucreator->setStoreids($storeids);
							$storeids="";
			}else
			{
			$storeids="";
			foreach($data['storeids'] as $store):
			$storeids.=$store.",";
			endforeach;
			 $this->menucreator->setStoreids($storeids);
							$storeids="";
			}
			
			}
			if(isset($data['remove_img_main']))
			{
			if($data['remove_img_main']=="1")
			{
				
			if(isset($data['menu_id']))
			{
			if ($data['menu_id'] != "") {
            	$id=$data['menu_id'];
				}
			}
			$model_image_remove=  $this->menucreator->load($id);
			
			$image_name = $model_image_remove->getImage();
					$mediaDirectory = $this->_objectManager->get('Magento\Framework\Filesystem')
        			->getDirectoryRead(DirectoryList::MEDIA);
			$path=$mediaDirectory->getAbsolutePath('navigationmenu/image').$image_name;
			unlink($path);
			$model_image_remove->setImage("");
			$model_image_remove->setShowCustomCategoryImage("");
			$model_image_remove->save();
			}
			}
				
			if(isset($data['setrel']))
			{
			$setrel="";
			foreach($data['setrel'] as $relation):
			$setrel.=$relation." ";
			endforeach;
			$this->menucreator->setSetrel($setrel);
			$setrel="";
			}else
			{
			$setrel="";
			$this->menucreator->setSetrel($setrel);
			}
				
				if(isset($data['title_show_hide'])){
					$this->menucreator->setTitleShowHide($data['title_show_hide']);
			}
			if(isset($data['autosub'])){
					$this->menucreator->setAutosub($data['autosub']);
				}else{
					$this->menucreator->setAutosub(0);
				}
				
				if(isset($data['use_category_title'])){
					$this->menucreator->setUseCategoryTitle($data['use_category_title']);
				}else{
					$this->menucreator->setUseCategoryTitle(0);
				}
				if(isset($data['autosubimage'])){
					$this->menucreator->setAutosubimage($data['autosubimage']);
				}else{
					$this->menucreator->setAutosubimage(0);
				}
				if(isset($data['image_type']))
				{
				$this->menucreator->setShowCategoryImage($data['image_type']);
				/*show_category_image*/
				}
				if(isset($data['useexternalurl'])){
					$this->menucreator->setUseexternalurl($data['useexternalurl']);
				}else{
					$this->menucreator->setUseexternalurl(0);
				}
				/*if(isset($data['label_show_hide'])){
					$model->setLabelShowHide($data['label_show_hide']);
				}else{
					$model->setLabelShowHide($data['label_show_hide']);
				}*/
				
				$this->menucreator->setLabelTitle($data['label_title']);
				$this->menucreator->setLabelHeight($data['height']);
				$this->menucreator->setLabelColor($data['label_text_color']);
				$this->menucreator->setLabelBgColor($data['label_text_bg_color']);
				$this->menucreator->save();
				
					$this->messageManager->addSuccess(__('The Record has been saved.'));
				
					$developer_mode_enable_disable = $this->_scopeConfig->getValue('navigationmenu/optimize_performance/developer_mode_enable_disable', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
					if(!$developer_mode_enable_disable){
						$this->_coreSession->start();
						$this->_coreSession->setPublishHtml(true);
					}
					
				
					
				
					$this->_objectManager->get('Magento\Backend\Model\Session')->setFormData(false);
					if ($this->getRequest()->getParam('back')) {
						$this->_redirect('*/*/edit', array('id' => $model->getId(), '_current' => true));
						return;
					}
					$this->_redirect('*/*/');
					return;
            	}
				catch (\Magento\Framework\Model\Exception $e) 
				{
                	$this->messageManager->addError($e->getMessage());
            	}
				catch (\RuntimeException $e) 
				{
                	$this->messageManager->addError($e->getMessage());
            	} 
				catch (\Exception $e)
				{
                	$this->messageManager->addException($e, __('Something went wrong while saving the Record.'));
                	$this->messageManager->addError($e->getMessage());
            	}

				$this->_getSession()->setFormData($data);
				//$this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
				//return;
		}
		$this->_redirect('*/*/index');
    }
   
	 protected function _isAllowed()
    {
		return true;
        
    }
}

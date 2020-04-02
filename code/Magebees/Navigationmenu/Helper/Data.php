<?php
namespace  Magebees\Navigationmenu\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
	protected $child_menu_items = [];
	public function __construct(
        \Magento\Framework\App\Helper\Context $context,
		\Magento\Customer\Model\ResourceModel\Group\Collection $customergroup,
		\Magento\Store\Model\StoreRepository $storerepository,
		\Magebees\Navigationmenu\Model\Menucreatorgroup $menucreatorgroup,
		\Magebees\Navigationmenu\Model\Menucreator $menucreator,
		\Magento\Framework\Data\Form\FormKey $formkey,
		\Magento\Store\Model\StoreManagerInterface $storemanager,
		\Magento\Catalog\Model\Category $category,
		\Magento\Cms\Model\Page $page,
		\Magento\Cms\Model\Block $block,
		\Magento\Backend\Helper\Data $backendhelper,
		\Magento\Framework\Url $url,
		\Magento\Framework\Filesystem $filesystem,
		\Magento\Customer\Model\Group $group,
		\Magento\Store\Model\System\Store $store
	) {
		$this->customergroup = $customergroup;
		$this->storerepository = $storerepository;
		$this->menucreatorgroup = $menucreatorgroup;
		$this->menucreator = $menucreator;
		$this->formkey = $formkey;
		$this->storemanager = $storemanager;
		$this->category = $category;
		$this->page = $page;
		$this->block = $block;
		$this->backendhelper = $backendhelper;
		$this->url = $url;
		$this->filesystem = $filesystem;
		$this->group = $group;
		$this->store = $store;
		parent::__construct($context);
    }
	public function getCustomerGroupList(){
		return $this->customergroup->toOptionArray();
	}
	public function getStoreList(){
		return $this->storerepository->getList();;
	}
	public function getAllGroup(){
		$groupData =  [];
        $group_collection = $this->menucreatorgroup->getCollection();
            $groupData [] =  [
                    'value' => '',
                    'label' => 'Please Select Group',
            ];
            foreach ($group_collection as $group) {
                $groupData [] =  [
                    'value' => $group->getGroupId(),
                    'label' => $group->getTitle(),
                ];
            }
            return $groupData;
		
		
	}
	public function getFormkey(){
		return $this->formkey->getFormKey();
	}
	public function getImagePath(){
		return $this->storemanager->getStore()
				->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA).'navigationmenu/image/';
	}
	public function getCategoryListForForm(){
		$category_html = null;
		$categories = $this->category->getCollection()
						->addAttributeToSelect('*')
						->addAttributeToFilter('level', 1)
						->addAttributeToFilter('is_active', 1);
		$parent_categories = $categories->getData();
		foreach($parent_categories as $key => $value):
		$parent_category = $this->category->load($value['entity_id']);
		$Categories_list = $this->menucreator->getCategorieslist($value['entity_id'],true);
		if($Categories_list != ''){
		$category_html .= "<optgroup label=".$parent_category->getName()."><option value=".$parent_category->getId().">".$parent_category->getName()."</option>";
											$category_html .= $Categories_list;
											$category_html .= "</optgroup>";
										}
										endforeach;
		return $category_html;
									
	}
	public function getCMSPageCollection(){
		$cms_pages_collection =$this->page->getCollection();
		$cms_page_sort    = array();
		foreach($cms_pages_collection as $cms_key => $cms_value):
		if($cms_value->getIsActive() == "1")
		{
		$cms_page_sort[$cms_value->getId()][] = $cms_value->getData();
	 	  }
		endforeach;
		ksort($cms_page_sort);
		return $cms_page_sort;
	}
	public function getCMSBlockCollection(){
		$cms_blocks_collection =$this->block->getCollection();
		$cms_block_sort    = array();
		foreach($cms_blocks_collection as $cms_key => $cms_value):
		if($cms_value->getIsActive() == "1")
		{
		$cms_block_sort[$cms_value->getId()][] = $cms_value->getData();
	 	  }
		endforeach;
		ksort($cms_block_sort);
		return $cms_block_sort;
	}
	public function getMenuItemsInTreeStructure(){
		$groupcollection = $this->menucreatorgroup->getCollection()
        ->setOrder("group_id", "asc");
		$menugroup_backend = $groupcollection->getData();
		$menubackend = "<div id=navmenu class=navmenusorted>";
		foreach ($menugroup_backend as $key => $group) {
            $group_id = $group['group_id'];
            $group_status = $group['status'];
            if ($group_status == "1") {
                $status = ' enabled';
            } elseif ($group_status == "2") {
                $status = ' disabled';
            }
            
            if ($group_id != "0") {
                $group_details = $this->menucreatorgroup->load($group_id);
                $editgroup_url = $this->backendhelper->getUrl("navigationmenu/menucreatorgroup/edit/", ["id" => $group_id]);
                /* Add Li class 'mjs-nestedSortable-no-nesting' On the Group Li so can not add the sub child on the Group Li*/
                $menubackend .= "<h2 class='groupTitle' id=".$group_id."><a href=".$editgroup_url." title=".$group_details->getTitle()." class='edit'>Edit</a>".$group_details->getTitle()."</h2>";
                $menubackend .= "<ol class='sortable ui-sortable mjs-nestedSortable-branch mjs-nestedSortable-expanded groupid-".$group_id.' '.$status."' id=groupid-".$group_id.">";
                $menubackend .=$this->getChildAdminMenuTree($group_id);
                $menubackend .= "</ol>";
            }
        }
		$menubackend .= "</div>";
        return $menubackend;
		
	}
	public function getChildMenuItemCollection($parentId)
    {
        $chilMenu= $this->menucreator->getCollection()->setOrder("position", "asc");
        $chilMenu->addFieldToFilter('parent_id', $parentId);
        return $chilMenu;
    }
	public function getChildAdminMenuTree($group_id)
    {
        
        $allParent =$this->menucreator->getCollection()
                    ->addFieldToFilter('parent_id', "0")
                    ->setOrder("position", "asc")
                    /*->setOrder("created_time","asc")*/
                    ->addFieldToFilter('group_id', $group_id);
        $html = isset($html) ? $html : '';
        foreach ($allParent as $item) {
            $url = $this->backendhelper->getUrl("navigationmenu/menucreator/edit/", ["id" => $item->getMenuId()]);
            $add_sub_url = $this->backendhelper->getUrl('navigationmenu/menudata/addsubparent');
            $editformurl = $this->backendhelper->getUrl("navigationmenu/menucreator/editform/", ["id" => $item->getMenuId()]);
            $add_sub = $this->backendhelper->getUrl("navigationmenu/menucreator/new/", ["group_id" => $item->getGroupId(),"parent_id" => $item->getMenuId()]);
            $delete_url = $this->backendhelper->getUrl("navigationmenu/menucreator/deleteitems/", ["id" => $item->getMenuId()]);
            
            $current_url = $this->url->getCurrentUrl();
            $space = $this->getMenuSpace($item->getMenuId());
            $hasChild = $this->getChildMenuItemCollection($item->getMenuId());
            $menu_type = trim($item->getType());
        
            if (($item->getType() == "1")) {
                $this->item_type_label = 'cms';
            } elseif (($item->getType() == "2")) {
                $this->item_type_label = 'category';
            } elseif (($item->getType() == "3")) {
                $this->item_type_label = 'static-block';
            } /* For Product Pages*/
            elseif (($item->getType() == "4")) {
                $this->item_type_label = 'product';
            } elseif (($item->getType() == "5")) {
                $this->item_type_label = 'custom-url';
            } elseif (($item->getType() == "6")) {
                $this->item_type_label = 'alias';
            } elseif (($item->getType() == "7")) {
                $this->item_type_label = 'group';
            } elseif (($item->getType() == "account")) {
                $this->item_type_label = 'account';
            } elseif (($item->getType() == "cart")) {
                $this->item_type_label = 'cart';
            } elseif (($item->getType() == "wishlist")) {
                $this->item_type_label = 'wishlist';
            } elseif (($item->getType() == "checkout")) {
                $this->item_type_label = 'checkout';
            } elseif (($item->getType() == "login")) {
                $this->item_type_label = 'login';
            } elseif (($item->getType() == "logout")) {
                $this->item_type_label = 'logout';
            } elseif (($item->getType() == "register")) {
                $this->item_type_label = 'register';
            } elseif (($item->getType() == "contact")) {
                $this->item_type_label = 'contact';
            }
            if ($item->getStatus()=="1") {
                $status = ' enabled';
            } else {
                $status = ' disabled';
            }
            if ($item->getClassSubfix() != '') {
                $add_custom_class = $item->getClassSubfix();
            } else {
                $add_custom_class = '';
            }
            if (count($hasChild)>0) {
                $has_child_element = 'mjs-nestedSortable-branch mjs-nestedSortable-expanded ';
            } else {
                $has_child_element = 'mjs-nestedSortable-leaf ';
            }
            $column_layout = trim($item->getSubcolumnlayout());
            if (($column_layout == 'no-sub') && ($menu_type == "3")) {
                $html .= '<li class="'.$has_child_element.$status.' '.$add_custom_class.' no-sub" id="menuItem_'.$item->getMenuId().'" store="'.$item->getStoreids().'"> <div class="menuDiv">';
            } elseif (($menu_type == "7")) {
                $html .= '<li class="'.$has_child_element.$status.' '.$add_custom_class.' no-sub" id="menuItem_'.$item->getMenuId().'" store="'.$item->getStoreids().'"> <div class="menuDiv">';
            } else {
                $html .= '<li class="'.$has_child_element.$status.' '.$add_custom_class.'" id="menuItem_'.$item->getMenuId().'" store="'.$item->getStoreids().'"> <div class="menuDiv">';
            }

            if (count($hasChild)>0) {
                $html .= '<span class="disclose ui-icon ui-icon-minusthick" title="Click to show/hide children"><span></span></span>';
            }
			
			$item_label = null;
			if($item->getLabelTitle()):
			$item_label = '<sup class="menulbl" style="background-color:'.$item->getLabelBgColor().'; color:'.$item->getLabelColor().'; font-size:'.$item->getLabelHeight().'px; line-height:'.$item->getLabelHeight().'px;">'.$item->getLabelTitle().'</sup>';
			endif;
            $html .= '<span class="itemTitle" data-id="'.$item->getMenuId().'">'.$item->getTitle().$item_label.'</span><span class="mType"">'.$this->item_type_label.'</span><button data-editurl="'.$editformurl.'" class="scalable edit edit-menu-item" type="button" title="Edit '.$item->getTitle().'"><span>Edit Menu</span></button>
		<button data-groupid="'.$item->getGroupId().'" data-menuId="'.$item->getMenuId().'" data-addsuburl="'.$add_sub_url.'" class="scalable add add-menu-item" type="button" title="Add in '.$item->getTitle().'"><span>Add Sub</span></button>
		<button data-deleteurl="'.$delete_url.'" data-currenturl="'.$current_url.'" class="scalable delete delete-menu-item" type="button" title="Delete '.$item->getTitle().'"><span>Delete</span></button>
		</div>';
            $has_child_element = '';
            $parent_status = '';
            $add_custom_class = '';
        /* Use TO Get The Sub Category If set Autoi Sub On when the menu type is category*/

            if (count($hasChild)>0) {
                $html .= $this->getAdminMenuTreeChild($item->getMenuId(), true, $column_layout, $menu_type);
            }
            $html .= '</li>';
        }

        return $html;
    }
	function getAdminMenuTreeChild($parentId, $isChild, $column_layout, $menu_type)
    {
        $allChild = $this->menucreator->getCollection()
                    ->setOrder("position", "asc")
                    ->addFieldToFilter('parent_id', $parentId);

        $html = isset($html) ? $html : '';
        $Parent_menu = $this->menucreator->load($parentId);
        if ($Parent_menu->getStatus()=="1") {
            $parent_status = ' enabled';
        } else {
            $parent_status = ' disabled';
        }

        $class = ($isChild) ? "sub-cat-list" : "cat-list";

        if (($column_layout == 'no-sub')) {
            $html .= '<ol class="'.$class.$parent_status.' '.$column_layout.'">';
        } elseif (($menu_type == "3") && ($column_layout == 'no-sub')) {
            $html .= '<ol class="'.$class.$parent_status.' no-sub-static-block">';
        } else {
            $html .= '<ol class="'.$class.$parent_status.'">';
        }

        $parent_status = '';
        foreach ($allChild as $item) {
            if (($item->getType() == "1")) {
                $this->item_type_label = 'cms';
            } elseif (($item->getType() == "2")) {
                $this->item_type_label = 'category';
            } elseif (($item->getType() == "3")) {
                $this->item_type_label = 'static-block';
            } /* For Product Pages*/
            elseif (($item->getType() == "4")) {
                $this->item_type_label = 'product';
            } elseif (($item->getType() == "5")) {
                $this->item_type_label = 'custom-url';
            } elseif (($item->getType() == "6")) {
                $this->item_type_label = 'alias';
            } elseif (($item->getType() == "7")) {
                $this->item_type_label = 'group';
            } elseif (($item->getType() == "account")) {
                $this->item_type_label = 'account';
            } elseif (($item->getType() == "cart")) {
                $this->item_type_label = 'cart';
            } elseif (($item->getType() == "wishlist")) {
                $this->item_type_label = 'wishlist';
            } elseif (($item->getType() == "checkout")) {
                $this->item_type_label = 'checkout';
            } elseif (($item->getType() == "login")) {
                $this->item_type_label = 'login';
            } elseif (($item->getType() == "logout")) {
                $this->item_type_label = 'logout';
            } elseif (($item->getType() == "register")) {
                $this->item_type_label = 'register';
            } elseif (($item->getType() == "contact")) {
                $this->item_type_label = 'contact';
            }
            if ($item->getStatus()=="1") {
                $child_status = ' enabled';
            } else {
                $child_status = ' disabled';
            }

            $hasChild = $this->getChildMenuItemCollection($item->getMenuId());
            $url = $this->backendhelper->getUrl("navigationmenu/menucreator/edit/", ["id" => $item->getMenuId()]);
            $add_sub_url = $this->backendhelper->getUrl('navigationmenu/menudata/addsubparent');
            $editformurl = $this->backendhelper->getUrl("navigationmenu/menucreator/editform/", ["id" => $item->getMenuId()]);
            $add_sub = $this->backendhelper->getUrl("navigationmenu/menucreator/new/", ["group_id" => $item->getGroupId(),"parent_id" => $item->getMenuId()]);
            $delete_url = $this->backendhelper->getUrl("navigationmenu/menucreator/deleteitems/", ["id" => $item->getMenuId()]);
            $current_url = $this->url->getCurrentUrl();

            if (count($hasChild)>0) {
                $has_child_element = 'mjs-nestedSortable-branch mjs-nestedSortable-expanded ';
            } else {
                $has_child_element = 'mjs-nestedSortable-leaf ';
            }
            $sub_menu_type = trim($item->getType());
            $column_layout = trim($item->getSubcolumnlayout());
            if ($item->getClassSubfix() != '') {
                $add_custom_class = $item->getClassSubfix();
            } else {
                $add_custom_class = '';
            }
            if (($column_layout == 'no-sub') && ($sub_menu_type == "3")) {
                $html .= '<li class="'.$has_child_element.$child_status.' '.$add_custom_class.' no-sub" id="menuItem_'.$item->getMenuId().'" store="'.$item->getStoreids().'"><div class="menuDiv">';
            } else {
                $html .= '<li class="'.$has_child_element.$child_status.' '.$add_custom_class.'" id="menuItem_'.$item->getMenuId().'" store="'.$item->getStoreids().'"><div class="menuDiv">';
            }
            if (count($hasChild)>0) {
                $html .= '<span class="disclose ui-icon ui-icon-minusthick" title="Click to show/hide children"><span></span></span>';
            }
        	$item_label = null;
			
			if($item->getLabelTitle()):
			$item_label = '<sup class="menulbl" style="background-color:'.$item->getLabelBgColor().'; color:'.$item->getLabelColor().'; font-size:'.$item->getLabelHeight().'px; line-height:'.$item->getLabelHeight().'px;">'.$item->getLabelTitle().'</sup>';
			endif;
			$html .= '<span class="itemTitle" data-id="'.$item->getMenuId().'">'.$item->getTitle().$item_label.'</span><span class="mType"">'.$this->item_type_label.'</span>
		<button data-editurl="'.$editformurl.'" class="scalable edit edit-menu-item" type="button" title="Edit '.$item->getTitle().'"><span>Edit Menu</span></button>
		<button data-groupid="'.$item->getGroupId().'" data-menuId="'.$item->getMenuId().'" data-addsuburl="'.$add_sub_url.'" class="scalable add add-menu-item" type="button" title="Add in '.$item->getTitle().'"><span>Add Sub</span></button>
		<button data-deleteurl="'.$delete_url.'" data-currenturl="'.$current_url.'" class="scalable delete delete-menu-item" type="button" title="Delete '.$item->getTitle().'"><span>Delete</span></button>
		</div>';
            $child_status = '';


            if (count($hasChild)>0) {
                $column_layout = trim($item->getSubcolumnlayout());
                $menu_type = trim($item->getType());
                $html .= $this->getAdminMenuTreeChild($item->getMenuId(), true, $column_layout, $menu_type);
            }

            $html .= '</li>';
        }
        $html .= '</ol>';
        return $html;
    }
    public function getallMenuTypes()
    {
        return [
            '1' => 'CMS Page',
            '2' => 'Category Page',
            '3' => 'Static Block',
            '4' => 'Product Page',
            '5' => 'Custom Url',
            '6' => 'Alias [href=#]',
            '7'=>   'Group'
        ];
    }
    public function getallLinks()
    {
        return [
            'account'   => 'My Account',
            'cart'      => 'My Cart',
            'wishlist'  => 'My Wishlist',
            'checkout'  => 'Checkout',
            'login'     => 'Login',
            'logout'    => 'Logout',
            'register'  => 'Register',
            'contact'   => 'Contact Us'
        ];
    }
    public function getShowHideTitle()
    {
        return [
            '' => 'Please Select Show Hide Menu Title',
            '1' => 'Hide Group Title',
            '2' => 'Show Group Title',
        ];
    }
    public function getOptionArray()
    {
        return [
            
            '1' => 'Enabled',
            '2' => 'Disabled',
        ];
    }
    public function getGroupMenuType()
    {
        return [
            '' => 'Please Select Menu Type',
            'mega-menu' => 'Mega Menu',
            'smart-expand' => 'Smart Expand',
            'always-expand' => 'Always Expand',
            'list-item' => 'List Item'
        ];
    }
    
    public function getAlignmentType()
    {
        return [
            '' => 'Please Select Alignment',
            'horizontal' => 'Horizontal',
            'vertical' => 'Vertical'
            
        ];
    }
    public function getFontTransform()
    {
        return [
            'inherit' => 'Inherit',
            'uppercase' => 'Uppercase',
            'lowercase' => 'Lowercase',
            'capitalize' => 'Capitalize'
            
        ];
    }
    public function getAlignment()
    {
        return [
            'left' => 'Left',
            'right' => 'Right',
            'full-width' => 'Full Width',
	    'full-width-mouse-hover' => 'Full Width + Mouse Hover',
        ];
    }
    public function getMenuLevel()
    {
        return [
            '' => 'Please Select Level',
            '0' => 'Only Root Level',
            '1' => 'One Level',
            '2' => 'Second Level',
            '3' => 'Third Level',
            '4' => 'Fourth Level',
            '5' => 'Fifth Level',
        ];
    }
    public function getmassMenuLevel()
    {
        return [
            '0' => 'Only Root Level',
            '1' => 'One Level',
            '2' => 'Second Level',
            '3' => 'Third Level',
            '4' => 'Fourth Level',
            '5' => 'Fifth Level',
        ];
    }
    public function getDirection()
    {
        return [
        'ltr'   => 'Left To Right',
        'rtl'   => 'Right To Left',
        ];
    }
    public function getlevel($menu_id, $level)
    {
		
        $p_id=$this->menucreator->load($menu_id)->getParentId();
        if ($p_id!=0) {
            $this->menu_level = $level+1;
            $this->getlevel($p_id, $this->menu_level);
        } else {
            $level = '0';
            return $level;
        }
        
        return $this->menu_level;
    }
    public function getRelation()
    {
        $this->relations[]=['value'=>'alternate','label'=>'alternate'];
        $this->relations[]=['value'=>'author','label'=>'author'];
        $this->relations[]=['value'=>'bookmark','label'=>'bookmark'];
        $this->relations[]=['value'=>'help','label'=>'help'];
        $this->relations[]=['value'=>'license','label'=>'license'];
        $this->relations[]=['value'=>'next','label'=>'next'];
        $this->relations[]=['value'=>'nofollow','label'=>'nofollow'];
        $this->relations[]=['value'=>'noreferrer','label'=>'noreferrer'];
        $this->relations[]=['value'=>'prefetch','label'=>'prefetch'];
        $this->relations[]=['value'=>'prev','label'=>'prev'];
        $this->relations[]=['value'=>'search','label'=>'search'];
        $this->relations[]=['value'=>'tag','label'=>'tag'];
        return $this->relations;
    }
    
   	public function getStaticTemplateDirectoryPath()
    {
        $mediaPath = $this->filesystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::PUB)->getAbsolutePath()."magebees/navigationmenu/static/";
return $mediaPath;
    }
    public function getDynamicCSSDirectoryPath()
    {
        $mediaPath = $this->filesystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::PUB)->getAbsolutePath()."magebees/navigationmenu/css/";
return $mediaPath;
    }
  
    public function getParentIds($menu_id)
    {
        
        $p_id=$this->menucreator->load($menu_id)->getParentId();
        $p_ids=$p_id;
        //Stop this function when it parent is root node
        if ($p_id!=0) {
            $p_ids=$p_ids."-".$this->getParentIds($p_id);
        }
        return $p_ids;
    }
    public function getMenuSpace($menu_id)
    {
        $space="";
        $parentIds=explode("-", $this->getParentIds($menu_id));
        for ($i=1; $i<count($parentIds); $i++) {
            $space = $space."--";
        }
        return $space;
    }
    public function getPermissionforgrid()
    {
        $permission = [];
        $permission["-2"] = 'Public';
        $permission["-1"] = 'Registered';
        
        $collection = $this->group->getCollection();
        foreach ($collection as $value) {
            $permission[$value->getCustomerGroupId()] = $value->getCustomerGroupCode();
        }
        return $permission;
    }
    public function getPermission()
    {
        
        $this->groups[]=['value'=>'-2','label'=>'Public'];
        $this->groups[]=['value'=>'-1','label'=>'Registered'];
        $collection = $this->group->getCollection();
        foreach ($collection as $value) {
            $this->groups[] = [
                    'value'=>$value->getCustomerGroupId(),
                    'label' => $value->getCustomerGroupCode()
            ];
        }
        return $this->groups;
    }
    public function getstore_swatcher()
    {
        $store_info     =$this->store->getStoreValuesForForm(false, true);
        return $store_info;
    }
    public function columnLayout()
    {
        return [
            '' => 'Please Select Sub Column Layout',
            'no-sub' => 'No Sub Item',
            'column-1' => '1 Column Layout',
            'column-2' => '2 Column Layout',
            'column-3' => '3 Column Layout',
            'column-4' => '4 Column Layout',
            'column-5' => '5 Column Layout',
            'column-6' => '6 Column Layout',
            'column-7' => '7 Column Layout',
            'column-8' => '8 Column Layout',
        ];
    }
    function sanitize_output($buffer)
    {

        $search = [
        '/\>[^\S ]+/s',  // strip whitespaces after tags, except space
        '/[^\S ]+\</s',  // strip whitespaces before tags, except space
        '/(\s)+/s'       // shorten multiple whitespace sequences
        ];

        $replace = [
        '>',
        '<',
        '\\1'
        ];

        $buffer = preg_replace($search, $replace, $buffer);

        return $buffer;
    }

	public function getChildMenuItem($parentmenuid)
    {
        
        $chilMenu= $this->menucreator->getCollection()
        			->addFieldToSelect('menu_id')
        			->addFieldToFilter('parent_id', $parentmenuid);
        $childmenuitems = $chilMenu->getData();
        $this->child_menu_items[] = $parentmenuid;
        foreach ($childmenuitems as $key => $value) {
            $this->child_menu_items[] = $value['menu_id'];
            $hasChild = $this->getChildMenuByParentId($value['menu_id']);
            if (count($hasChild) > 0) {
                $this->child_menu_items[] = $this->getChildMenuItemTest($value['menu_id']);
            }
        }
        return $this->child_menu_items;
    }
    public function getChildMenuItemTest($parentmenuid)
    {
        $chilMenu= $this->menucreator->getCollection()
						->addFieldToSelect('menu_id')
						->addFieldToFilter('parent_id', $parentmenuid);

        $childmenuitems = $chilMenu->getData();
        foreach ($childmenuitems as $key => $value) {
            $this->child_menu_items[] = $value['menu_id'];
            $hasChild = $this->getChildMenuByParentId($value['menu_id']);
            if (count($hasChild) > 0) {
                $this->child_menu_items[] = $this->getChildMenuItemTest($value['menu_id']);
            }
        }
        return $this->child_menu_items;
    }
	public function getChildMenuByParentId($parentId)
    {
        $chilMenu= $this->menucreator->getCollection()->setOrder("position", "asc");
        $chilMenu->addFieldToFilter('parent_id', $parentId);
        return $chilMenu;
    }
	
}

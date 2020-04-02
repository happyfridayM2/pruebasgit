<?php

namespace Magebees\Navigationmenu\Model;

class Menustaticitem extends \Magento\Framework\Model\AbstractModel
{

    protected $optionData = "";

    protected $category_list = [];

    protected $item_available = "";

    protected $label_class = "";

    protected $item_autosub_cat = "";

    protected $url = "";

    protected $has_child_element = "";

    protected $has_smart_expand = "";

    protected $autosub_has_child_element = "";

    protected $autosub_has_smart_expand = "";

    protected $link_title = "";

    protected $link_relation = "";

    protected $item_target = "";

    protected $column_layout_align = "";

    protected $static_block_status = [];

    protected $static_block_count = "";

    protected $sub_items_available = [];

    protected $my_cat_image_type = "";

    protected $static_blcok_class = "";
	protected $current_store = "";

    

    public function __construct(
        \Magento\Framework\Model\Context $context,

                \Magento\Framework\Registry $registry,

                \Magento\Store\Model\StoreManagerInterface $storeManager,

                \Magebees\Navigationmenu\Model\Menucreator $menucreator,

                \Magebees\Navigationmenu\Model\Menucreatorgroup $menucreatorgroup,

                \Magebees\Navigationmenu\Model\Customer $modelcustomer,

                \Magento\Catalog\Model\CategoryFactory $category,

                \Magento\Framework\Url $urlhelper,

                \Magebees\Navigationmenu\Model\Category $modelcategory,

                \Magebees\Navigationmenu\Model\Product $modelproduct,

                \Magebees\Navigationmenu\Helper\Data $helper,

                \Magento\Cms\Model\BlockFactory $block,

                \Magento\Cms\Model\PageFactory $page,
				\Magento\Catalog\Model\ProductFactory $product,
				\Magento\Framework\View\Element\Template $template,

                array $data = []
    ) {

    

        $this->_registry = $registry;

        $this->_storeManager = $storeManager;

        $this->_modelcustomer = $modelcustomer;

        $this->_modelcategory = $modelcategory;

        $this->_modelproduct = $modelproduct;

        $this->_helper = $helper;

        $this->_category = $category;

        $this->_blockFactory = $block;

        $this->_pageFactory = $page;

        $this->_menucreator = $menucreator;

        $this->_urlhelper = $urlhelper;

        $this->_menucreatorgroup = $menucreatorgroup;
		$this->_product = $product;
		$this->_template = $template;
		
		parent::_construct();

        $this->_init('Magebees\Navigationmenu\Model\ResourceModel\Menucreator');
    }

    public function getMenuGroup()
    {

        $group=$this->_menucreator->getCollection()->addFieldToSelect('group_id')->distinct(true);

        return $group;
    }

    public function getMenuitem()
    {

        $menu_items=$this->_menucreator->getCollection()->setOrder("group_id", "asc")->setOrder("position", "asc");

        return $menu_items;
    }

    public function getChildMenuCollection($parentId,$current_storeid=null,$customerStatus=null,$customerGroupId=null)
    {
		$permission = $this->_modelcustomer->getUserPermission($customerStatus,$customerGroupId);
		$chilMenu = $this->_menucreator->getCollection()->setOrder("position", "asc");
		$chilMenu->addFieldToFilter('status', '1');
		$chilMenu->addFieldToFilter('parent_id', $parentId);
		$chilMenu->addFieldToFilter('storeids', [['finset' => $current_storeid]]);
		$chilMenu->addFieldToFilter('permission', ['in' => [$permission]]);
		return $chilMenu;
    }

    
    function getCategorieslistform($parentId, $isChild)
    {

            

            $allCats =$this->_category->create()->getCollection()

                ->addAttributeToSelect('*')

                ->addAttributeToFilter('parent_id', ['eq' => $parentId])

                ->addAttributeToSort('position', 'asc');



        foreach ($allCats as $category) {
            if ($category->getLevel() > 2) {
                $lable = '';

                for ($i=2; $i<=$category->getLevel(); $i++) {
                    $lable .= "\t".' -';
                }
            }

            $this->category_list[] = [

                    'value' => $category->getId(),

                    'label' => $lable . " ".$category->getName(),

                ];

            if ($class == "sub-cat-list") {
                $html .= '<option value="'.$category->getId().'">'.$lable." ".$category->getName().' </option>';
            } elseif ($class == "cat-list") {
                $html .= '<option value="'.$category->getId().'">'.$lable." ".$category->getName().'</option>';
            }

                   /*Remove Ul & Li End*/

                 $lable = '';

                $subcats = $category->getChildren();

            if ($subcats != '') {
                $html .= $this->getCategorieslistform($category->getId(), true);
            }
        }

        return $this->category_list;
    }

    function getCategorieslist($parentId, $isChild)
    {

   

            $allCats = $this->_category->create()->getCollection()

                ->addAttributeToSelect('*')

                ->addAttributeToFilter('parent_id', ['eq' => $parentId])

                ->addAttributeToSort('position', 'asc');

               

        $class = ($isChild) ? "sub-cat-list" : "cat-list";

    

        foreach ($allCats as $category) {
            if ($category->getLevel() > 2) {
                $lable = '';

                for ($i=2; $i<=$category->getLevel(); $i++) {
                    $lable .= "\t".' -';
                }
            }



            if ($class == "sub-cat-list") {
                $html .= '<option value="'.$category->getId().'">'.$lable." ".$category->getName().' </option>';
            } elseif ($class == "cat-list") {
                $html .= '<option value="'.$category->getId().'">'.$lable." ".$category->getName().'</option>';
            }

                   /*Remove Ul & Li End*/

                 $lable = '';

                $subcats = $category->getChildren();

            if ($subcats != '') {
                $html .= $this->getCategorieslist($category->getId(), true);
            }
        }

        return $html;
    }





    public function getStaticMenuContent($group_id,$website_id=null,$store_id=null,$storeCode=null,$customerStatus=null,$customerGroupId=null)
    {
		$this->_current_store = $this->_storeManager->getStore($store_id);
		$current_storeid = $store_id; 
		$permission = $this->_modelcustomer->getUserPermission($customerStatus,$customerGroupId);
		$allParent = $this->_menucreator->getCollection()
			        ->addFieldToFilter('parent_id', "0")
				    ->setOrder("position", "asc")
					->addFieldToFilter('group_id', $group_id)
					->addFieldToFilter('status', "1")
					->addFieldToFilter('storeids', [['finset' => $current_storeid]])
					->addFieldToFilter('permission', ['in' => [$permission]])
					->addFieldToFilter('type', ['neq' => '7']);
		$group_details = $this->_menucreatorgroup->load($group_id);
		$group_menutype = $group_details->getMenutype();
		$group_level = $group_details->getLevel();
		$i = 0;

        $len = count($allParent);

        $menu_item_count = 0;

        $html = isset($html) ? $html : '';

        foreach ($allParent as $item) {
            $this->label_class = '';

            $this->url = '';

            $this->has_child_element = '';

            $this->has_smart_expand = '';

    

            $this->item_available = '';

            $this->item_autosub_cat = '';

            $this->column_layout_align = '';

            $image_tag = '';

            $image_url = '';

            $staticblock_active = '';

            $this->link_title = '';

            $this->link_relation = '';

    

            $this->item_target = '';

            $parent_status = '';

            $add_custom_class = '';

            $this->static_blcok_class = '';

            $space = $this->_helper->getMenuSpace($item->getMenuId());

            $hasChild = $this->getChildMenuCollection($item->getMenuId(),$store_id,$customerStatus,$customerGroupId);

        /* Call the below function to check the static block status of the child & parent element.*/

            $sub_static_block_status = $this->getChildStaticblockStatus($item->getMenuId(),$customerStatus,$customerGroupId);

            $count_submenu_item = $this->getChildCount($item->getMenuId(),$store_id,$customerStatus,$customerGroupId);

        /* Here Sub Item available is check the sub item is available for the current store or not.*/

            $subitemsavailable = $this->subitemsavailable($item->getMenuId(),$store_id,$customerStatus,$customerGroupId);

    

            if (($item->getType() == "1")) {
            /* Check CMS Page is Active & From the Current Store Visible Or not*/

                $cms_page =  $this->_pageFactory->create()->setStoreId($current_storeid)->load($item->getCmspageIdentifier());

                $page_active_check = $cms_page->getIsActive();

                $page_Identifier = $cms_page->getIdentifier();

                if ($page_active_check == "1") {
                    $this->item_available = "1";

                    /* If CMS page is home page then no need to add the page Identifier.*/

                    if ($page_Identifier != 'home') {
                        $this->url=$this->_current_store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_LINK).$page_Identifier.'/';
                    } else {
                           $this->url = $this->_current_store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_LINK);
                    }
                } else {
                    $this->item_available = "0";

                    $this->url = '';
                }
            } elseif (($item->getType() == "2")) {
                $cat_id = $item->getCategoryId();
				$rootCatId = $this->_current_store->getRootCategoryId();
                $allow_cat =  $this->_modelcategory->checkCategoryAvailable($cat_id,$current_storeid,$rootCatId);
				  
				$current_category = $this->_category->create()->setStoreId($current_storeid)->load($cat_id);
                $rootCategoryId =$this->_current_store->getRootCategoryId();

                if (( $current_category->getId()) && ($allow_cat == "1")) {
                    $this->item_available = "1";

                    if ($current_category->getLevel() != '1') {
                        $this->url =  $current_category->getUrl();
                    } else {
                        $this->url = "javascript:void(0)";
                    }
                } else {
                    $this->item_available = "0";

                    $this->url = "";
                }
            } elseif (($item->getType() == "3")) {
            /* For Static Blocks*/

                if ($item->getStaticblockIdentifier() != '') {
                /* Static block is active for the current store then add into the menu item*/
					$active =$this->_blockFactory->create()->setStoreId($current_storeid)->load($item->getStaticblockIdentifier())->getIsActive();
                    $column_layout = trim($item->getSubcolumnlayout());
        
                    if ($active == 1) {
                        if ($column_layout!="no-sub") {
                            $this->item_available = "1";
                            $this->static_blcok_class = ' cmsbk';
                        } else {
                            $this->item_available = "0";
                            $this->static_blcok_class = ' ';
                        }
                    } else {
                        $this->item_available = "0";

                        $this->static_block_class = '';
                    }
                }
            } /* For Product Pages*/

            elseif (($item->getType() == "4")) {
                $pro_id = $item->getProductId();

                $allow_pro =$this->_modelproduct->checkProductavailable($pro_id,$current_storeid);

                $current_product = $this->_product->create()->setStoreId($current_storeid)->load($pro_id);

    

                if (($current_product->getId()) && ($allow_pro == "1")) {
                    $this->item_available = "1";

                    $this->url = $current_product->getProductUrl();
                } else {
                    $this->item_available = "0";

                    $this->url = "";
                }
            } elseif (($item->getType() == "5")) {
               /* For Custom URL*/

                if ($item->getUrlValue() != "") {
                    if ($item->getUseexternalurl() == "1") {
                        $this->url = $item->getUrlValue();
                    } elseif ($item->getUseexternalurl() == "0") {
                        $this->url = $this->_current_store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_LINK).$item->getUrlValue();
                    }

            

                    $this->item_available = "1";
                } else {
                    $this->url = "";

                    $this->item_available = "0";
                }

                    $urlString = $this->_urlhelper->getCurrentUrl();
            } elseif (($item->getType() == "6")) {
            /*For Alias Menu*/

                $this->url = "javascript:void(0)";
            } elseif (($item->getType() == "7")) {
            /*For Alias Menu*/

                $this->url = "javascript:void(0)";
            } elseif (($item->getType() == "account")) {
            /*For My Account  Menu*/

                $this->url = $this->_urlhelper->getUrl('customer/account');
            } elseif (($item->getType() == "cart")) {
            /*For My Cart  Menu*/

                $this->url = $this->_urlhelper->getUrl('checkout/cart');
            } elseif (($item->getType() == "wishlist")) {
            /*For My Wishlist  Menu*/

                $this->url = $this->_current_store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_LINK).'wishlist/';
            } elseif (($item->getType() == "checkout")) {
            /*For CheckOut Menu*/

                
				//$this->url =$checkout_model->getCheckoutUrl();
				$this->url = $this->_urlhelper->getUrl('checkout', ['_secure' => true]);
				
            } elseif (($item->getType() == "login")) {
            /*For Login Menu*/

                $this->url = $this->_urlhelper->getUrl('customer/account/login');

				if($customerStatus=='login')
				{
					$this->item_available = "0";
				}else{
					$this->item_available = "1";
				}

    

    

    

               /* if ($this->_modelcustomer->isLoggedIn() == "1") {
                    $this->item_available = "0";
                } else {
                    $this->item_available = "1";
                }*/
            } elseif (($item->getType() == "logout")) {
            /*For Logout Menu*/

                $this->url = $this->_urlhelper->getUrl('customer/account/logout');

				if($customerStatus=='login')
				{
					$this->item_available = "1";
				}else{
					$this->item_available = "0";
				}

    

    

                /*if ($this->_modelcustomer->isLoggedIn() == "1") {
                    $this->item_available = "1";
                } else {
                    $this->item_available = "0";
                }*/
            } elseif (($item->getType() == "register")) {
            /*For Register Menu*/

                $this->url = $this->_urlhelper->getUrl('customer/account/create');

    
				if($customerStatus=='login')
				{
					$this->item_available = "0";
				}else{
					$this->item_available = "1";
				}
    

                /*if ($this->_modelcustomer->isLoggedIn() == "1") {
                    $this->item_available = "0";
                } else {
                    $this->item_available = "1";
                }*/
            } elseif (($item->getType() == "contact")) {
            /*For Contact Us Menu*/

    

                $this->url = $this->_current_store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_LINK).'contact/';
            }
            if ($item->getLabelTitle()!="") {
                $this->label_class = "haslbl";
            } else {
                $this->label_class = "";
            }

            $column_layout = trim($item->getSubcolumnlayout());



            if (($item->getAutosub() == "1") && ($item->getType() == "2") && (count($hasChild)=="0") && ($group_level > 0)) {
                $category_id = $item->getCategoryId();

                $menu_cat= $this->_modelcategory;
				$rootCatId = $this->_current_store->getRootCategoryId();
				    $childcatcount = $menu_cat->getChildCategoryCount($category_id,$current_storeid,$rootCatId);

                    /* Here Make Custom Function to check the categories's sub child is active & set Include Yes in the menu then only it will display in the menu as sub cateogry.*/



        

                if ($childcatcount > 0) {
                    $this->has_child_element = 'parent';

                    $this->has_smart_expand = '1';

                    $this->item_autosub_cat = "1";
                } else {
                    $this->has_child_element = '';

                    $this->has_smart_expand = '';

                    $this->item_autosub_cat = "0";
                }
            }

    

            if (($item->getType() == "3")) {
            /* Static block have no sub item so no need to add parent class in the static block li */

                $staticblock_active = $this->_blockFactory->create()->setStoreId($current_storeid)->load($item->getStaticblockIdentifier())->getIsActive();

                if (($staticblock_active == "1") && ($group_level > 0) && ($column_layout != 'no-sub')) {
                    $this->has_child_element = 'parent';

                    $this->has_smart_expand = '1';
                }
            } elseif (((count($hasChild)>0) || ($this->item_autosub_cat == "1")) && ($group_level > 0) && ($item->getType() != "3") && ($column_layout != 'no-sub')) {
                if ($this->static_block_count > 0) {
                /* Check the Static block is active for the current store view then only add into the menu*/

                    if (!empty($sub_static_block_status)) {
                        $this->has_child_element = 'parent';

                        $this->has_smart_expand = '1';
                    }
                }

                if (($count_submenu_item > 0) && !empty($subitemsavailable)) {
                    $this->has_child_element = 'parent';

                    $this->has_smart_expand = '1';
                }
            } else {
                $this->has_child_element = '';

                $this->has_smart_expand = '';
            }

            if ($item->getClassSubfix() != '') {
                $add_custom_class = $item->getClassSubfix();
            }

            if ($item->getDescription() != '') {
                $this->link_title = trim($item->getDescription());
            }

            if ($item->getSetrel() != '') {
                $this->link_relation = trim($item->getSetrel());
            }

            $target = $item->getTarget();

            if ($target == "2") {
                $this->item_target = "target='_blank'";
            }

        /* Check item level with the group level Here Item menu level is greater then group level then that item is not allowed in the list.

        */



            $item_menu_level =$this->_helper->getlevel($item->getMenuId(), false);

            if ($item_menu_level > $group_level) {
                $this->item_available = "0";
            }

    

            if ($this->item_available != "0") {
                if ($group_menutype != "list-item") {
                    $text_align = $item->getTextAlign();

                    if ($text_align == 'left') {
                        $text_align = "aLeft";
                    } elseif ($text_align == 'right') {
                        $text_align = "aRight";
                    } elseif ($text_align == 'full-width') {
                        $text_align = $item->getTextAlign();
                    }

                    $this->column_layout_align = $column_layout.' '.$text_align;
                }

    

    

    

                /*

            Here Check Column Layout which is greater then the one column and menu type is

                mega-menu then add the mega menu class in the root li.  */

                if (($column_layout != 'no-sub') && ($column_layout != 'column-1') && ($group_menutype == "mega-menu")) {
                    $add_custom_class .= ' megamenu ';
                }

                if (($len == 1) && ($i == 0)) {
                    $html .= '<li class="Level'.$item_menu_level.' first last '.$this->has_child_element.' '.$add_custom_class.' '.$this->column_layout_align.$this->static_blcok_class.'">';
                } elseif (($i == 0) && ($len != 1)) {
                    $html .= '<li class="Level'.$item_menu_level.' first '.$this->has_child_element.' '.$add_custom_class.' '.$this->column_layout_align.$this->static_blcok_class.'">';
                } elseif ($i == $len - 1) {
                    $html .= '<li class="Level'.$item_menu_level.' last '.$this->has_child_element.' '.$add_custom_class.' '.$this->column_layout_align.$this->static_blcok_class.'">';
                } else {
                    $html .= '<li class="Level'.$item_menu_level.' '.$this->has_child_element.' '.$add_custom_class.' '.$this->column_layout_align.$this->static_blcok_class.'">';
                }

    

                /* Set Menu Item Image base on the menu type if menu type is

                category then use thumbnail & base image otherwise use custom uploaded images.*/

    

                if ((($item->getShowCategoryImage() != 'none') || ($item->getShowCustomCategoryImage() == '1')) && ($item->getType() == "2")) {
                    if ($item->getShowCustomCategoryImage() == '1') {
                        $image_url= $this->_current_store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA).'navigationmenu/image/'.$item->getImage();

                            $image_tag = "<span class=img><img src='".$image_url."'  /></span>";
                    } elseif ($item->getShowCategoryImage() == 'main_image') {
                        $cat_id = $item->getCategoryId();
                        $category_image =$this->_category->create()->load($cat_id);

                        if ($category_image->getImageUrl() != '') {
                            $image_url = $category_image->getImageUrl();

                            $image_tag = "<span class=img><img src='".$image_url."' alt='' /></span>";
                        }
                    }
                } else {
                    if (($item->getImageStatus() == '1') && ($item->getImage() != '') && ($item->getType() != "2") && ($item->getType() != "7")) {
                        $image_url= $this->_current_store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA).'navigationmenu/image/'.$item->getImage();

                        $image_tag = "<span class=img><img src='".$image_url."' alt='' /></span>";
                    }
                }

    

                if (($item->getType() == "3")) {
                    $active = $this->_blockFactory->create()->setStoreId($current_storeid)->load($item->getStaticblockIdentifier())->getIsActive();

                    if (($active == 1) && ($group_level > 0) && ($column_layout != 'no-sub')) {
                    /* Here If the Static block item is set as Root then we display the Menu item li A link of the static block.*/

                        $html .= '<a check rel="'.$this->link_relation.'" class="Level'.$item_menu_level.' '.$this->label_class.'" title="'.$this->link_title.'" href="'.$this->url.'"'.$this->item_target.'>';

                    /* For Static block Add Image Tag In between the A link in the Li*/

        

                        if (($item->getImageStatus() == '1') && ($item->getImage() != '')) {
                            $html .= $image_tag;
                        }

                        //$html .= $item->getTitle().'</a>';

                        $html .= $item->getTitle();

            

                        if (($group_menutype != "list-item") && ($this->has_smart_expand == "1")) {
                            $html .= '<span class="arw plush" title="Click to show/hide sub menu"></span>';
                        }

                        if ($item->getLabelTitle()!="") {
                            $label = '<sup class="menulbl" style="background-color:'.$item->getLabelBgColor().'; color:'.$item->getLabelColor().'; font-size:'.$item->getLabelHeight().'px; line-height:'.$item->getLabelHeight().'px;">'.$item->getLabelTitle().'</sup>';

                            $html .= $label;
                        }

                        $html .= '</a>';

                        /* Here If Group Level Is Greater then Zero then only Static block content Add into the menu item.*/
					  if (($group_level > 0) && ($group_level > 0) && ($column_layout != 'no-sub')) {
                            $html .= '<ul class="Level0 subMenu"><li class="Level1 first last">';

                            $html .= '<div class="'.$this->static_blcok_class.'">';

                            $html .= $this->_template->getLayout()->createBlock('Magento\Cms\Block\Block')->setBlockId($item->getStaticblockIdentifier())->toHtml();

        

        

                            $html .= '</div>';

                            $html .='</li></ul>';
                        }
                    }
                } else {
                    $html .= '<a rel="'.$this->link_relation.'" class="Level'.$item_menu_level.' '.$this->label_class.'" title="'.$this->link_title.'" href="'.$this->url.'"'.$this->item_target.'>';

                    /* Add Image Tag In between the A link in the Li*/

                    if ((($item->getImageStatus() == '1') && ($item->getImage() != '') ) || (($item->getShowCategoryImage() != 'none') && ($image_url != ''))) {
                        $html .= $image_tag;
                    } elseif (($item->getShowCustomCategoryImage() == '1') && ($item->getImage() != '') && ($image_url !='')) {
                        $html .= $image_tag;
                    }

                    $image_tag = '';

                    $html .= $item->getTitle();

    

                    if (($group_menutype != "list-item") && ($this->has_smart_expand == "1")) {
                        $html .= '<span class="arw plush" title="Click to show/hide children"></span>';
                    }

                    if ($item->getLabelTitle()!="") {
                        $label = '<sup class="menulbl" style="background-color:'.$item->getLabelBgColor().'; color:'.$item->getLabelColor().'; font-size:'.$item->getLabelHeight().'px; line-height:'.$item->getLabelHeight().'px;">'.$item->getLabelTitle().'</sup>';

    

    

                        $html .= $label;
                    }

                    $html .= '</a>';
                }

                /* Add + & - Sign for the Smart-Expand Menu Type in the Li item for all which has sub item.

            Here when Parent Item is the category with the Auto-sub then it will also add the plus & minus sign in the ul & li.

                */
            }

            $i++;

    

        /* Use TO Get The Sub Category If set Auto Sub On when the menu type is category.

        Here Check the Item Sub Column Layout If it set as no-sub then Auto Sub will not displayed.

        If Menu Item Level is Set Only Root then It will not display Sub Category In the List.

        group_level.

        Here item_autosub_cat is check the Category has sub item or not.

        */

            if ($this->item_available != "0") {
                if (($item->getAutosub() == "1") && ($item->getType() == "2") && (count($hasChild)=="0") && ($group_level > 0) && ($item_menu_level <= $group_level) && ($this->item_autosub_cat == "1") && ($column_layout != 'no-sub')) {
                    $this->my_cat_image_type = $item->getShowCategoryImage();

                    $cat_image_type = $item->getShowCategoryImage();

                    $show_hide_cat_image = $item->getAutosubimage();

                    $item_parentId = $item->getParent_Id();

                    $html .= $this->getCategoriesAutoSub($item->getCategoryId(), true, $item_menu_level, $group_level, $group_menutype, $cat_image_type, $show_hide_cat_image, $item_parentId,$store_id);
                }

                /* Here We restrict the Static block's subitem in the front so we can not display child of the static block item.

            Check the Menu Level with the Gruop Level.

            Here Our Item_menu_level start with 0 so we add (+1) in the Item_menuevel

            so set the correct menu item in the list

            Here Check the Sub Column Layout if it set the no-sub then child menu item not added into the list

    

    

                */

                if ((count($hasChild)>0) && ($item->getType() != "3") && ($item_menu_level <= $group_level) && ($item->getSubcolumnlayout() != "no-sub") && ($group_level != "0")) {
                    if (!empty($subitemsavailable)) {
                        $html .= $this->getChildHtml($item->getMenuId(), true,$group_id,$website_id,$store_id,$storeCode,$customerStatus,$customerGroupId,$add_custom_class);
                    }
                }
            }

    

            if ($this->item_available != "0") {
                $html .= '</li>';
            }
        }

        

        return $html;
    }

    function getChildCount($parent_menu_id,$current_storeid=null,$customerStatus=null,$customerGroupId=null)
    {

        $permission = $this->_modelcustomer->getUserPermission($customerStatus,$customerGroupId);
		 $allChildWithoutStaticblock =$this->_menucreator->getCollection()

                ->setOrder("position", "asc")

                ->addFieldToFilter('parent_id', $parent_menu_id)

                ->addFieldToFilter('type', ['neq' => '3'])

                ->addFieldToFilter('status', "1")

                ->addFieldToFilter('storeids', [['finset' => $current_storeid]])

                ->addFieldToFilter('permission', ['in' => [$permission]]);

                return count($allChildWithoutStaticblock);
    }



    function getChildStaticblockStatus($parent_menu_id,$customerStatus=null,$customerGroupId=null)
    {

        $this->static_block_status = [];

        $this->static_block_count = '';

        $current_storeid = $this->_storeManager->getStore()->getStoreId();

        $permission = $this->_modelcustomer->getUserPermission($customerStatus,$customerGroupId);

        $allChildStaticblock =$this->_menucreator->getCollection()

                ->setOrder("position", "asc")

    

                ->addFieldToFilter('parent_id', $parent_menu_id)

    

                ->addFieldToFilter('type', "3")

    

                ->addFieldToFilter('status', "1")

    

                ->addFieldToFilter('storeids', [['finset' => $current_storeid]])

    

                ->addFieldToFilter('permission', ['in' => [$permission]]);

        $this->static_block_count = count($allChildStaticblock);

        foreach ($allChildStaticblock as $item) {
            if (($item->getType() == "3")) {
                if ($item->getStaticblockIdentifier() != '') {
                    
                    $active = $this->_blockFactory->create()->setStoreId($current_storeid)->load($item->getStaticblockIdentifier())->getIsActive();

                    if ($active == 1) {
                        $this->static_block_status[$item->getStaticblockIdentifier()] = "1";
                    }
                }
            }
        }

        return $this->static_block_status;
    }

    function getChildHtml($parentId, $isChild,$groupId=null,$website_id=null,$store_id=null,$storeCode=null,$customerStatus=null,$customerGroupId=null,$parentclass=null)
    {
		$current_storeid = $store_id;
		$this->_current_store = $this->_storeManager->getStore($store_id);
		$permission = $this->_modelcustomer->getUserPermission($customerStatus,$customerGroupId);
		$allChild = $this->_menucreator->getCollection()

                ->setOrder("position", "asc")

                ->addFieldToFilter('parent_id', $parentId)

                /*Only Enabled Item will be list in the menu item*/

                ->addFieldToFilter('status', "1")

                /*Filter Collection By Store Id*/

                ->addFieldToFilter('storeids', [['finset' => $current_storeid]])

                    /*Filter Collection By User Permission */

                ->addFieldToFilter('permission', ['in' => [$permission]]);

    

        $Parent_menu = $this->_menucreator->load($parentId);

        /* Get Group Id From the Current Menu Item*/

        $group_id = $Parent_menu->getGroupId();

        $group_details = $this->_menucreatorgroup->load($group_id);

        $group_level = $group_details->getLevel();

        $group_menutype = $group_details->getMenutype();

        $menu_level = $this->_helper->getlevel($parentId, false);

        $class = ($isChild) ? " subMenu" : " ";

   

        /*

        Add Div in the Group Items */

    	$html = isset($html) ? $html : '';
		
		
			
			
		if ((strpos($parentclass, 'megamenu') !== false) && ($group_menutype=='mega-menu') && ($group_details->getPosition()=='horizontal') && ($Parent_menu->getTextAlign()=='full-width')) {
			$html .= '<div class="Level'.$menu_level.' subMenu ">';
			$html .= '<div class="container">';
			$html .= '<ul class="Level'.$menu_level.' ">';	
		}else{
			
		$html .= '<ul class="Level'.$menu_level.$class.' ">';	
		}
	
        

    

    

        $j = 0;

        $len_child = count($allChild);

    

        foreach ($allChild as $item) {
            $this->item_available = '';

            $this->item_autosub_cat = '';

            $this->label_class = '';

    

            $this->has_child_element = '';

            $this->has_smart_expand = '';

            $this->column_layout_align = '';

            $this->static_blcok_class = '';

            $this->url = '';

            $image_tag = '';

            $image_url = '';

            $this->link_title = '';

            $this->item_target = '';

            $child_status = '';

            $add_custom_class = '';

            $sub_static_block_status = $this->getChildStaticblockStatus($item->getMenuId(),$customerStatus,$customerGroupId);

            $count_submenu_item = $this->getChildCount($item->getMenuId(),$store_id,$customerStatus,$customerGroupId);

            $subitemsavailable = $this->subitemsavailable($item->getMenuId(),$store_id,$customerStatus,$customerGroupId);

            if ($item->getLabelTitle()!="") {
                $this->label_class = "haslbl";
            } else {
                $this->label_class = "";
            }

            $column_layout = trim($item->getSubcolumnlayout());

            if (($item->getType() == "1")) {
            /* Check CMS Page is Active & From the Current Store Visible Or not*/

        	  $cms_page =  $this->_pageFactory->create()->setStoreId($current_storeid)->load($item->getCmspageIdentifier());

                $page_active_check = $cms_page->getIsActive();

                $page_Identifier = $cms_page->getIdentifier();

                if ($page_active_check == "1") {
                /* If CMS page is home page then no need to add the page Identifier.*/

                    if ($page_Identifier != 'home') {
                        $this->url =$this->_current_store
                        ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_LINK).$page_Identifier.'/';
                    } else {
                        $this->url = $this->_current_store
                           ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_LINK);
                    }

                    $this->item_available = "1";
                } else {
                    $this->url = '';

                    $this->item_available = "0";
                }

    

                /* For Category Pages*/
            } elseif (($item->getType() == "2")) {
                $cat_id = $item->getCategoryId();
				$rootCatId = $this->_current_store->getRootCategoryId();
                $allow_cat = $this->_modelcategory->checkCategoryAvailable($cat_id,$current_storeid,$rootCatId);
				
$current_category = $this->_category->create()->setStoreId($current_storeid)->load($cat_id);
                $rootCategoryId = $this->_current_store->getRootCategoryId();

                if (($current_category->getId()) && ($allow_cat == "1")) {
                    if ($current_category->getLevel() != '1') {
                            $this->url = $current_category->getUrl($current_category);
                    } else {
                        $this->url = "javascript:void(0)";
                    }

                        $this->item_available = "1";
                } else {
                    $this->url = "";

                    $this->item_available = "0";
                }
            } /* For Static Blocks*/

            elseif (($item->getType() == "3")) {
                if ($item->getStaticblockIdentifier() != '') {
                    /* Static block is active for the current store then add into the menu item*/
					$active = $this->_blockFactory->create()->setStoreId($current_storeid)->load($item->getStaticblockIdentifier())->getIsActive();

                    /*if ($active == 1){

                    $this->item_available = "1";

                    $this->static_blcok_class = ' cmsbk';

                    }else

                    {

                    $this->item_available = "0";

                    $this->static_blcok_class = '';

                    }*/
                    $column_layout = trim($item->getSubcolumnlayout());
                    if ($active == 1) {
                        if ($column_layout!="no-sub") {
                            $this->item_available = "1";
                            $this->static_blcok_class = ' cmsbk';
                        } else {
                            $this->item_available = "0";
                            $this->static_blcok_class = ' ';
                        }
                    } else {
                        $this->item_available = "0";
                        $this->static_block_class = '';
                    }
                }
            } /* For Product Pages*/

            elseif (($item->getType() == "4")) {
                $pro_id = $item->getProductId();

                $current_product = $this->_product->create()->setStoreId($current_storeid)->load($pro_id);
				
				
                $allow_pro = $this->_modelproduct->checkProductavailable($pro_id,$current_storeid);

                if (($current_product->getId()) && ($allow_pro == "1")) {
                        $this->url = $current_product->getProductUrl();

                        $this->item_available = "1";
                } else {
                    $this->url = "";

                    $this->item_available = "0";
                }
            } elseif (($item->getType() == "5")) {
               /* For Custom URL*/

                if ($item->getUrlValue() != "") {
                    if ($item->getUseexternalurl() == "1") {
                        $this->url = $item->getUrlValue();
                    } elseif ($item->getUseexternalurl() == "0") {
                        $this->url = $this->_current_store
                        ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_LINK).$item->getUrlValue();
                    }

                    $this->item_available = "1";

                    /*Here Target Is custom URL SO We set to

                     Open in the New Tab*/
                } else {
                    $this->url = "";

                    $this->item_available = "0";
                }
            } elseif (($item->getType() == "6")) {
            /*For Alias Menu*/

                $this->url = "javascript:void(0)";
            } elseif (($item->getType() == "7")) {
            /*For Alias Menu*/

                $this->url = "javascript:void(0)";
            } elseif (($item->getType() == "account")) {
            /*For My Account  Menu*/

        

                $this->url =$this->_urlhelper->getUrl('customer/account');
            } elseif (($item->getType() == "cart")) {
            /*For My Cart  Menu*/

        

                $this->url = $this->_urlhelper->getUrl('checkout/cart');
            } elseif (($item->getType() == "wishlist")) {
            /*For My Wishlist  Menu*/

        

                    $this->url = $this->_current_store
                   ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_LINK).'wishlist/';
            } elseif (($item->getType() == "checkout")) {
            	$this->url = $this->_urlhelper->getUrl('checkout', ['_secure' => true]);
            } elseif (($item->getType() == "login")) {
            /*For Login Menu*/

                $this->url = $this->_urlhelper->getUrl('customer/account/login');


				if($customerStatus=='login')
				{
					$this->item_available = "0";
				}else{
					$this->item_available = "1";
				}
    

               /* if ($this->_modelcustomer->isLoggedIn() == "1") {
                    $this->item_available = "0";
                } else {
                    $this->item_available = "1";
                }*/
            } elseif (($item->getType() == "logout")) {
            /*For Logout Menu*/

                $this->url =$this->_urlhelper->getUrl('customer/account/logout');

    
				if($customerStatus=='login')
				{
					$this->item_available = "1";
				}else{
					$this->item_available = "0";
				}
    
    

               /* if ($this->_modelcustomer->isLoggedIn() == "1") {
                    $this->item_available = "1";
                } else {
                    $this->item_available = "0";
                }*/
            } elseif (($item->getType() == "register")) {
            /*For Register Menu*/

                $this->url = $this->_urlhelper->getUrl('customer/account/create');

    			if($customerStatus=='login')
				{
					$this->item_available = "0";
				}else{
					$this->item_available = "1";
				}

            } elseif (($item->getType() == "contact")) {
            /*For Contact Us Menu*/
				  $this->url = $this->_current_store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_LINK).'contact/';
            }

    

            $hasChild = $this->getChildMenuCollection($item->getMenuId(),$current_storeid,$customerStatus,$customerGroupId);

        

    

            $item_menu_level =$this->_helper->getlevel($item->getMenuId(), false);

    

    

            if (($item->getAutosub() == "1") && ($item->getType() == "2") && (count($hasChild)=="0") && ($item_menu_level <= $group_level)) {
                $category_id = $item->getCategoryId();
				$rootCatId = $this->_current_store->getRootCategoryId();
				    $childcatcount = $this->_modelcategory->getChildCategoryCount($category_id,$current_storeid,$rootCatId);

                    /* Here Make Custom Function to check the categories's sub child is active & set Include Yes in the menu then only it will display in the menu as sub cateogry.*/

        

                if ($childcatcount > 0) {
                    $this->has_child_element = 'parent';

                    $this->has_smart_expand = '1';

                    $this->item_autosub_cat = "1";
                } else {
                    $this->has_child_element = '';

                    $this->has_smart_expand = '';

                    $this->item_autosub_cat = "0";
                }
            }

            if (($item->getType() == "3")) {
            /* Static block have no sub item so no need to add parent class in the static block li */

                $staticblock_active = $this->_blockFactory->create()->setStoreId($current_storeid)->load($item->getStaticblockIdentifier())->getIsActive();
    
        
        
    

                if (($staticblock_active == "1") && ($group_level > $item_menu_level) && ($column_layout != 'no-sub')) {
                    $this->has_child_element = '';
                    $this->has_smart_expand = '';
                    //$this->has_child_element = '';
                    //$this->has_smart_expand = '';
                } else {
                    $this->has_child_element = '';
                    $this->has_smart_expand = '';
                }
            } elseif (((count($hasChild)>0) || ($this->item_autosub_cat == "1")) && ($item->getType() != "3") && ($item_menu_level < $group_level) && ($column_layout != 'no-sub')) {
                if ($this->static_block_count > 0) {
                /* Check the Static block is active for the current store view then only add into the menu*/

                    if (!empty($sub_static_block_status)) {
                        $this->has_child_element = 'parent';

                        $this->has_smart_expand = '1';
                    }
                }

                if (($count_submenu_item > 0) && !empty($subitemsavailable)) {
                    $this->has_child_element = 'parent';

                    $this->has_smart_expand = '1';
                }
            } else {
                $this->has_child_element = '';

                $this->has_smart_expand = '';
            }

    

            if ($item->getClassSubfix() != '') {
                $add_custom_class = $item->getClassSubfix();
            }

            if ($item->getDescription() != '') {
                $this->link_title = trim($item->getDescription());
            }

            if ($item->getSetrel() != '') {
                $this->link_relation = trim($item->getSetrel());
            }

            $target = $item->getTarget();

            if ($target == "2") {
                $this->item_target = "target='_blank'";
            }

    

        /* Check item level with the group level Here Item menu level is greater then group level then that item is not allowed in the list.

    

        */

            if ($item_menu_level > $group_level) {
                $this->item_available = "0";
            }

    

            if ($this->item_available != "0") {
                if ($group_menutype != "list-item") {
                    $text_align = $item->getTextAlign();

                      $column_layout = trim($item->getSubcolumnlayout());

                    if ($text_align == 'left') {
                        $text_align = "aLeft";
                    } elseif ($text_align == 'right') {
                        $text_align = "aRight";
                    } elseif ($text_align == 'full-width') {
                        $text_align = $item->getTextAlign();
                    }

                        $this->column_layout_align = $column_layout.' '.$text_align;
                }

     

                if (($item->getType() != "7")) {
                    if (($len_child == 1) && ($j == 0)) {
                            $html .= '<li class="Level'.$item_menu_level.' first last '.$this->has_child_element.' '.$add_custom_class.' '.$this->column_layout_align.$this->static_blcok_class.'" >';
                    } elseif (($j == 0) && ($len_child != 1)) {
                        $html .= '<li class="Level'.$item_menu_level.' first '.$this->has_child_element.' '.$add_custom_class.' '.$this->column_layout_align.$this->static_blcok_class.'" >';
                    } elseif ($j == $len_child-1) {
                        $html .= '<li class="Level'.$item_menu_level.' last '.$this->has_child_element.' '.$add_custom_class.' '.$this->column_layout_align.$this->static_blcok_class.'" >';
                    } else {
                        $html .= '<li class="Level'.$item_menu_level.' '.$this->has_child_element.' '.$add_custom_class.' '.$this->column_layout_align.$this->static_blcok_class.'" >';
                    }
                } elseif ($item->getType() == "7") {
                    $html .= '<li class="Level'.$item_menu_level.' '.$this->has_child_element.' '.$add_custom_class.' '.$this->column_layout_align.' hideTitle" >';
                }

    

    

                /*

            Set Menu Item Image in the li 

                */

                /* Set Menu Item Image base on the menu type if menu type is

                category then use thumbnail & base image otherwise use custom uploaded images.*/

    

                if ((($item->getShowCategoryImage() != 'none') || ($item->getShowCustomCategoryImage() == '1')) && ($item->getType() == "2")) {
                    if ($item->getShowCustomCategoryImage() == '1') {
                        $image_url= $this->_current_store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA).'navigationmenu/image/'.$item->getImage();

                        $image_tag = "<span class=img><img src='".$image_url."'  /></span>";
                    } elseif ($item->getShowCategoryImage() == 'main_image') {
                        $cat_id = $item->getCategoryId();

                        $category_image =$this->_category->create()->load($cat_id);

                        if ($category_image->getImageUrl() != '') {
                            $image_url = $category_image->getImageUrl();

                            $image_tag = "<span class=img><img src='".$image_url."' alt='' /></span>";
                        }
                    }
                } else {
                    if (($item->getImageStatus() == '1') && ($item->getImage() != '') && ($item->getType() != "2") && ($item->getType() != "7")) {
                        $image_url= $this->_current_store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA).'navigationmenu/image/'.$item->getImage();

                        $image_tag = "<span class=img><img src='".$image_url."' alt='' /></span>";
                    }
                }

    

    

    

                /* Here For the Static Block We Remove the Static BLock Item Content From the Li and direct display the static block content in that li insted of item content.*/

                if (($item->getType() == "3")) {
                    $active = $this->_blockFactory->create()->setStoreId($current_storeid)->load($item->getStaticblockIdentifier())->getIsActive();

    

                    if (($active == 1) && ($item_menu_level <= $group_level) && ($column_layout != 'no-sub')) {
                    /* Add Image Tag In between the A link in the Li*/

                        if ($item->getTitleShowHide() == 'show') {
                            $html .= '<a checksub rel="'.$this->link_relation.'" class="Level'.$item_menu_level.' '.$this->label_class.' '.$this->has_child_element.$child_status.'" title="'.$this->link_title.'" href="'.$this->url.'"'.$this->item_target.'>';

                            if (($item->getImageStatus() == '1') && ($item->getImage() != '')) {
                                $html .= $image_tag;
                            }

            

                            $html .= $item->getTitle();
                            /* Check the Static block is active for the current store view then only add into the menu*/
                            if (($active == 1) && ($group_menutype != "list-item")) {
                                $html .= '<span class="arw plush" title="Click to show/hide children"></span>';
                            }
            

                            if ($item->getLabelTitle()!="") {
                                $label = '<sup class="menulbl" style="background-color:'.$item->getLabelBgColor().'; color:'.$item->getLabelColor().'; font-size:'.$item->getLabelHeight().'px; line-height:'.$item->getLabelHeight().'px;">'.$item->getLabelTitle().'</sup>';

            

                                $html .= $label;
                            }

                            $html .= '</a>';
                        } elseif ($item->getTitleShowHide() == 'hide') {
                            if (($item->getImageStatus() == '1') && ($item->getImage() != '')) {
                                $html .= $image_tag;
                            }
                        }

        
                        $html .= '<div class="222'.$this->static_blcok_class.'">';

                        $html .= $this->_template->getLayout()->createBlock('Magento\Cms\Block\Block')->setBlockId($item->getStaticblockIdentifier())->toHtml();

                        $html .= '</div>';
                    }
                } elseif (($item->getType() == "6")) {
                //if (($item_menu_level <= $group_level) && ($column_layout != 'no-sub')){

                    if (($item_menu_level <= $group_level)) {
                    /* Add Image Tag In between the A link in the Li*/

                        $html .= '<a rel="'.$this->link_relation.'" class="Level'.$item_menu_level.' '.$this->label_class.' '.$this->has_child_element.$child_status.'" title="'.$this->link_title.'" href="'.$this->url.'"'.$this->item_target.'>';

                        if (($item->getImageStatus() == '1') && ($item->getImage() != '')) {
                            $html .= $image_tag;
                        }

                    //$html .= $item->getTitle().'</a>';

                        $html .= $item->getTitle();

        

                        if (($group_menutype != "list-item") && ($this->has_smart_expand == "1")) {
                            $html .= '<span class="arw plush" title="Click to show/hide children"></span>';
                        }

        

                        if ($item->getLabelTitle()!="") {
                            $label = '<sup class="menulbl" style="background-color:'.$item->getLabelBgColor().'; color:'.$item->getLabelColor().'; font-size:'.$item->getLabelHeight().'px; line-height:'.$item->getLabelHeight().'px;">'.$item->getLabelTitle().'</sup>';

            

                            $html .= $label;
                        }

                        $html .= '</a>';
                    }
                } else {
                    $html .= '<a rel="'.$this->link_relation.'" class="Level'.$item_menu_level.' '.$this->label_class.' '.$this->has_child_element.$child_status.'" title="'.$this->link_title.'" href="'.$this->url.'"'.$this->item_target.'>';

                    /* Add Image Tag In between the A link in the Li*/

    

    

                    if ((($item->getImageStatus() == '1') && ($item->getImage() != '') ) || (($item->getShowCategoryImage() != 'none') && ($image_url != ''))) {
                        $html .= $image_tag;
                    } elseif (($item->getShowCustomCategoryImage() == '1') && ($item->getImage() != '') && ($image_url !='')) {
                        $html .= $image_tag;
                    }

                    $image_tag = '';

    

                    //$html .= $item->getTitle().'</a>';

                    $html .= $item->getTitle();

    

                    if (($group_menutype != "list-item") && ($this->has_smart_expand == "1")) {
                        $html .= '<span class="arw plush" title="Click to show/hide children"></span>';
                    }

                    if ($item->getLabelTitle()!="") {
                            $label = '<sup class="menulbl" style="background-color:'.$item->getLabelBgColor().'; color:'.$item->getLabelColor().'; font-size:'.$item->getLabelHeight().'px; line-height:'.$item->getLabelHeight().'px;">'.$item->getLabelTitle().'</sup>';

                                $html .= $label;
                    }

                    $html .= '</a>';
                }
            }

            $j++;

        /* Use TO Get The Sub Category If set Autoi Sub On when the menu type is category.

        Here Check the Item Sub Column Layout If it set as no-sub then Auti Sub will not displayed.

        If Menu Item Level is Set Only Root then It will not display Sub Category In the List.

        group_level.

        Here item_autosub_cat is check the Category has sub item or not.

        */

    

            if ($this->item_available != "0") {
                if (($item->getAutosub() == "1") && ($item->getType() == "2") && (count($hasChild)=="0") && ($item_menu_level < $group_level) && ($this->item_autosub_cat == "1") && ($column_layout != 'no-sub')) {
                    $cat_image_type = $item->getShowCategoryImage();

                    $show_hide_cat_image = $item->getAutosubimage();

                    $item_parentId = $item->getParent_Id();

                    $html .= $this->getCategoriesAutoSub($item->getCategoryId(), true, $item_menu_level, $group_level, $group_menutype, $cat_image_type, $show_hide_cat_image, $item_parentId,$store_id);
                }

                /* Here We restrict the Static block's subitem in the front so we can not display child of the static block item.

            Check the Menu Level with the Gruop Level.

                */

    

                if ((count($hasChild)>0) && ($item->getType() != "3") && ($item_menu_level < $group_level) && ($item->getSubcolumnlayout() != "no-sub")) {
                    $child_menu_item_count = 0;

    

                    if (!empty($subitemsavailable)) {
                        $html .= $this->getChildHtml($item->getMenuId(), true,$group_id,$website_id,$store_id,$storeCode,$customerStatus,$customerGroupId,$add_custom_class);
                    }
                }

                //if($item->getType() != "7"){

                if (($item->getType() != "7")) {
                    // $html .= '</li>';
                }

                 $html .= '</li>';
            }

        /* Add Static Block Content At the End of all it's child Element */
        }

    
		if ((strpos($parentclass, 'megamenu') !== false) && ($group_menutype=='mega-menu') && ($group_details->getPosition()=='horizontal') && ($Parent_menu->getTextAlign()=='full-width')) {
			$html .= '</ul>';
			$html .= '</div>';
			$html .= '</div>';
			
		}else{
		$html .= '</ul>';
		}
        

    

    

        return $html;
    }

    public function getMenuGroupdetails()
    {
        $group_menu = [];

            $menugroup_grid = $this->getMenuGroup();

            

        foreach ($menugroup_grid as $group) {
            $group_details = $this->_menucreatorgroup->load($group->getGroupId());

            $group_id = $group->getGroupId();

            $group_menu[$group_id] = $group_details->getTitle();
        }

            return $group_menu;
    }

    

    function getCategoriesAutoSub($parentId, $isChild, $item_menu_level, $group_level, $group_menutype, $cat_image_type, $show_hide_cat_image, $item_parentId,$store_id)
    {

    //echo 'show_hide_cat_image  ::'.$show_hide_cat_image;echo '<br/>';

    

    /* Load Category Base On the Current Store*/

      $this->_current_store = $this->_storeManager->getStore($store_id);
		
        $current_storeid = $this->_current_store->getStoreId();
		$rootCategoryId = $this->_current_store->getRootCategoryId();

        $rootpath = $this->_category->create()->setStoreId($current_storeid)->load($rootCategoryId)->getPath();

        $allCats = $this->_category->create()->setStoreId($current_storeid)

                    ->getCollection()

                    ->addAttributeToSelect('*')

                    ->addAttributeToFilter('include_in_menu', ['eq' => 1])

                    ->addAttributeToFilter('is_active', ['eq' => 1])

                    ->addAttributeToFilter('parent_id', ['eq' => $parentId])

                    ->addAttributeToFilter('path', ["like"=>$rootpath."/"."%"])

                     ->addAttributeToSort('position', 'asc');

    

        $class = ($isChild) ? " subMenu" : " ";

        $html = isset($html) ? $html : '';

        $html .= '<ul class="Level'.$item_menu_level.$class.'">';

    

        $item_menu_level = $item_menu_level+1;

        $cat_count = count($allCats);

        $k=0;


        foreach ($allCats as $category) {
            $childcatcount =$this->_modelcategory->getChildCategoryCount($category->getId(),$current_storeid,$rootCategoryId);

            $cat_level = $category->getLevel()-2;

            $subcats = $category->getChildren();

            /*Check Group Level With the Menu Item Level For the

            Auto SUb Category

            */

            if (($childcatcount > 0) && ($subcats != '') && ($item_menu_level < $group_level)) {
            //$this->has_child_element = ' has-children';

            /*if(($subcats != '')) {*/

                $this->autosub_has_child_element = ' parent';

                $this->autosub_has_smart_expand = '1';
            }

        

            /* Use to Set Auto Sub Category For the mega menu.*/

            if ($cat_level =="1") {
                $custom_layout = " column-1";
            } else {
                $custom_layout = "";
            }

            if ($item_menu_level <= $group_level) {
                if (($cat_count == 1) && ($k == 0)) {
                    $html .= '<li class="Level'.$item_menu_level.' '.$this->autosub_has_child_element.' first last'.$custom_layout.'">';
                } elseif (($k == 0) && ($cat_count != 1)) {
                    $html .= '<li class="Level'.$item_menu_level.' '.$this->autosub_has_child_element.' first'.$custom_layout.'">';
                } elseif ($k == $cat_count-1) {
                    $html .= '<li class="Level'.$item_menu_level.' '.$this->autosub_has_child_element.' last'.$custom_layout.'">';
                } else {
                    $html .= '<li class="Level'.$item_menu_level.' '.$this->autosub_has_child_element.''.$custom_layout.' ">';
                }

    

                if (($group_menutype != "list-item") && ($this->autosub_has_smart_expand == "1")) {
                    $html .= '<a href='.$category->getUrl($category).' class="Level'.$item_menu_level.'" title="'.$category->getName().'">';

                    if ($show_hide_cat_image == "1") {
                        if ($cat_image_type != 'none') {
                            if ($cat_image_type == 'thumbnail_image') {
                                if ($category->getThumbnail() != '') {
                                    $image_url= $this->_current_store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA).'catalog/category/'.$category->getThumbnail();

            

                                    $html .= "<span class=img><img src='".$image_url."' /></span>";
                                }
                            } elseif ($cat_image_type == 'main_image') {
                                if ($category->getImageUrl() != '') {
                                    $image_url = $category->getImageUrl();

                                    $html .= "<span class=img><img src='".$image_url."' /></span>";
                                }
                            }
                        }
                    }

                    //$html .= $category->getName().'</a>';

                    $html .= $category->getName();

                    $html .= '<span class="arw plush" title="Click to show/hide children"></span>';

                    $html .= '</a>';
                } else {
                    $html .= '<a href='.$category->getUrl($category).' class="Level'.$item_menu_level.'" title="'.$category->getName().'">';

                    if ($show_hide_cat_image == "1") {
                        if ($cat_image_type != 'none') {
                            if ($cat_image_type == 'thumbnail_image') {
                                if ($category->getThumbnail() != '') {
                                    $image_url= $this->_current_store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA).'catalog/category/'.$category->getThumbnail();

            

                                    $html .= "<span class=img><img src='".$image_url."' /></span>";
                                }
                            } elseif ($cat_image_type == 'main_image') {
                                if ($category->getImageUrl() != '') {
                                    $image_url = $category->getImageUrl();

                                    $html .= "<span class=img><img src='".$image_url."' /></span>";
                                }
                            }
                        }
                    }

                    $html .= $category->getName().'</a>';
                }
            }   $k++;

    

                $this->autosub_has_child_element = '';

                $this->autosub_has_smart_expand = '';

        

            if (($childcatcount > 0) && ($subcats != '') && ($item_menu_level < $group_level)) {
                $html .= $this->getCategoriesAutoSub($category->getId(), true, $item_menu_level, $group_level, $group_menutype, $cat_image_type, $show_hide_cat_image, $item_parentId,$store_id);
            }

                 $html .= '</li>';
        }

        $html .= '</ul>';

        return $html;
    }

    public function subitemsavailable($parent_id,$store_id,$customerStatus,$customerGroupId)
    {
		$this->sub_items_available = [];
		$this->_current_store = $this->_storeManager->getStore($store_id);
		
		$current_storeid = $store_id;
        $permission = $this->_modelcustomer->getUserPermission($customerStatus,$customerGroupId);

        $subitemsavailable = $this->_menucreator->getCollection()

                ->setOrder("position", "asc")

                ->addFieldToFilter('parent_id', $parent_id)

                ->addFieldToFilter('status', "1")

                ->addFieldToFilter('storeids', [['finset' => $current_storeid]])

                ->addFieldToFilter('permission', ['in' => [$permission]]);

        foreach ($subitemsavailable as $item) {
            if (($item->getType() == "1")) {
            /* Check CMS Page is Active & From the Current Store Visible Or not*/

                $cms_page =  $this->_pageFactory->create()->setStoreId($current_storeid)->load($item->getCmspageIdentifier());

                $page_active_check = $cms_page->getIsActive();

                $page_Identifier = $cms_page->getIdentifier();

    

                if ($page_active_check == "1") {
                    $this->sub_items_available[$item->getMenuId()] = "1";
                }
            } elseif (($item->getType() == "2")) {
            /* For Category Pages*/

                $cat_id = $item->getCategoryId();
				$rootCatId = $this->_current_store->getRootCategoryId();
                $allow_cat = $this->_modelcategory->checkCategoryAvailable($cat_id,$current_storeid,$rootCatId);

               $category = $this->_category->create()->setStoreId($current_storeid)->load($cat_id);

                if (($category->getId()) && ($allow_cat == "1")) {
                    $this->item_available = "1";

                    $this->sub_items_available[$item->getMenuId()] = "1";
                }
            } elseif (($item->getType() == "3")) {
                if ($item->getStaticblockIdentifier() != '') {
                    $active =$this->_blockFactory->create()->setStoreId($current_storeid)->load($item->getStaticblockIdentifier())->getIsActive();

                    if ($active == 1) {
                        $this->sub_items_available[$item->getMenuId()] = "1";
                    }
                }
            } elseif (($item->getType() == "4")) {
                $pro_id = $item->getProductId();

                $allow_pro =$this->_modelproduct->checkProductavailable($pro_id,$current_storeid);
				
				
				$current_product = $this->_product->create()->setStoreId($current_storeid)->load($pro_id);

                if (($current_product->getId()) && ($allow_pro == "1")) {
                        $this->sub_items_available[$item->getMenuId()] = "1";
                }
            } elseif (($item->getType() == "login")) {
                /*For Login Menu*/
				
				if($customerStatus!='login')
				{
					$this->sub_items_available[$item->getMenuId()] = "1";
					
				}
				
                /*if ($this->_modelcustomer->isLoggedIn() != "1") {
                    $this->sub_items_available[$item->getMenuId()] = "1";
                }*/
            } elseif (($item->getType() == "logout")) {
                /*For Logout Menu*/
				if($customerStatus=='login')
				{
					$this->sub_items_available[$item->getMenuId()] = "1";
					
				}
                
            } elseif (($item->getType() == "register")) {
                /*For Register Menu*/
				if($customerStatus!='login')
				{
					$this->sub_items_available[$item->getMenuId()] = "1";
					
				}
            } else {
                $this->sub_items_available[$item->getMenuId()] = "1";
            }
        }



        return $this->sub_items_available;
    }
}

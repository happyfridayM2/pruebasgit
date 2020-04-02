<?php

namespace Magebees\Navigationmenu\Model;

class Menucreator extends \Magento\Framework\Model\AbstractModel
{

   
    protected $parentitems = [];
    protected $optionData = "";
    protected $category_list = [];
    protected $child_menu_items = [];
    protected $item_type_label = "";
    public function _construct()
    {
        parent::_construct();
        $this->_init('Magebees\Navigationmenu\Model\ResourceModel\Menucreator');
    }
   
    public function getChildMenuCollection($parentId)
    {
        
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        $menucreator= $om->create('Magebees\Navigationmenu\Model\Menucreator');
        $chilMenu= $menucreator->getCollection()->setOrder("position", "asc");
        $chilMenu->addFieldToFilter('parent_id', $parentId);
        return $chilMenu;
    }
    public function getchild($parentID)
    {
        
            $childCollection=$this->getChildMenuCollection($parentID);
        foreach ($childCollection as $value) {
            $menuId = $value->getMenuId();
            //Check this menu has child or not
            $om = \Magento\Framework\App\ObjectManager::getInstance();
            $helper= $om->create('Magebees\Navigationmenu\Helper\Data');
            $this->optionData =$helper->getMenuSpace($menuId);
            $this->parentoption[$menuId] = ['title' => '----' . $this->optionData['blank_space'] .
            $value->getTitle(), 'group_id' => $value->getGroupId(), 'level' => $this->optionData['level']];
            $hasChild = $this->getChildMenuCollection($menuId);
            if (count($hasChild)>0) {
                $this->getchild($menuId);
            }
        }
    }
    
    public function getNewFileName($destFile)
    {
        $fileInfo = pathinfo($destFile);
        if (file_exists($destFile)) {
            $index = 1;
            $baseName = $fileInfo['filename'] . '.' . $fileInfo['extension'];
            while (file_exists($fileInfo['dirname'] . DIRECTORY_SEPARATOR . $baseName)) {
                $baseName = $fileInfo['filename']. '_' . $index . '.' . $fileInfo['extension'];
                $index ++;
            }
            $destFileName = $baseName;
        } else {
            return $fileInfo['basename'];
        }

        return $destFileName;
    }
    function getCategorieslistform($parentId, $isChild)
    {
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        $model_cat= $om->create('Magento\Catalog\Model\Category');
        $allCats = $model_cat->getCollection()
                ->addAttributeToSelect('*')
                ->addAttributeToFilter('include_in_menu', ['eq' => 1])
                ->addAttributeToFilter('is_active', ['eq' => 1])
                ->addAttributeToFilter('parent_id', ['eq' => $parentId])
                ->addAttributeToSort('position', 'asc');
               
        $class = ($isChild) ? "sub-cat-list" : "cat-list";

        foreach ($allCats as $category) {
            $lable = '';
            if ($category->getLevel() > 2) {
                $lable = '';
                for ($i=2; $i<=$category->getLevel(); $i++) {
                    $lable .= "\t".' -';
                }
            }
            $lable = ($lable) ? $lable : "";
            $html = isset($html) ? $html : "";
        ?>
    
        <?php   $this->category_list[] = [
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
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        $model_cat= $om->create('Magento\Catalog\Model\Category');
        $allCats = $model_cat->getCollection()
                ->addAttributeToSelect('*')
                ->addAttributeToFilter('parent_id', ['eq' => $parentId])
                ->addAttributeToSort('position', 'asc');
              
        $class = ($isChild) ? "sub-cat-list" : "cat-list";
        $html = isset($html) ? $html : '';
        foreach ($allCats as $category) {
            if ($category->getLevel() > 2) {
                $lable = '';
                for ($i=2; $i<=$category->getLevel(); $i++) {
                    $lable .= "\t".' -';
                }
            } else {
                $lable = '';
            }

            ?>
            <?php
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

/*Display All the Parent Items On the Form Parent Item Drop Down*/
    public function getParentItems($group_id, $current_menu_id)
    {
        $cur_menu_id = $current_menu_id;
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        $menucreator= $om->create('Magebees\Navigationmenu\Model\Menucreator');
        $allParent = $menucreator->getCollection()
                    ->addFieldToFilter('parent_id', "0")
                    ->setOrder("position", "asc")
                    ->addFieldToFilter('group_id', $group_id);
        $html = isset($html) ? $html : '';
        /* Use Current_menu_id to select the current Parent Element when the page is load for the edit the menu items*/
        $Currentmenu = $menucreator->load($current_menu_id);
        $current_parent_item = $Currentmenu->getParentId();
        if ($current_parent_item == "0") {
            $html = '<option value="">Please Select Parent</option><option value="0" selected>Root</option>';
        } else {
            $html = '<option value="">Please Select Parent</option><option value="0">Root</option>';
        }
        foreach ($allParent as $item) {
            $om = \Magento\Framework\App\ObjectManager::getInstance();
            $helper= $om->create('Magebees\Navigationmenu\Helper\Data');
            $space = $helper->getMenuSpace($item->getMenuId());
            if ($cur_menu_id != $item->getMenuId()) {
                if ($current_parent_item == $item->getMenuId()) {
                    $html .= '<option value="'.$item->getMenuId().'" selected>'.$space.$item->getTitle().' </option>';
                } else {
                    $html .= '<option value="'.$item->getMenuId().'">'.$space.$item->getTitle().' </option>';
                }
            }

            $hasChild = $this->getChildMenuCollection($item->getMenuId());
            if (count($hasChild)>0) {
                $html .= $this->getChildlist($item->getMenuId(), true, $cur_menu_id);
            }
        }
        return $html;
    }
    function getChildlist($parentId, $isChild, $cur_menu_id)
    {
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        $menucreator= $om->create('Magebees\Navigationmenu\Model\Menucreator');
        $allChild =$menucreator->getCollection()
                    ->setOrder("position", "asc")
                    ->addFieldToFilter('parent_id', $parentId);
        $Currentmenu = $menucreator->load($cur_menu_id);
        $current_parent_item = $Currentmenu->getParentId();

        $html = isset($html) ? $html : '';
        foreach ($allChild as $item) {
            $helper= $om->create('Magebees\Navigationmenu\Helper\Data');
            $space = $helper->getMenuSpace($item->getMenuId());
            if ($cur_menu_id != $item->getMenuId()) {
                if ($current_parent_item == $item->getMenuId()) {
                    $html .= '<option value="'.$item->getMenuId().'" selected>'.$space.$item->getTitle().' </option>';
                } else {
                    $html .= '<option value="'.$item->getMenuId().'">'.$space.$item->getTitle().'</option>';
                }
            }
            $hasChild = $this->getChildMenuCollection($item->getMenuId());
            if (count($hasChild)>0) {
                $html .= $this->getChildlist($item->getMenuId(), true, $cur_menu_id);
            }
        }
        return $html;
    }
/*Here We use Another Function To Fatch the Parent Drop down value when we directly add the Sub Child of the Parent Items*/
    public function getAddSubParentItems($group_id, $current_menu_id)
    {
        $cur_menu_id = $current_menu_id;
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        $menucreator= $om->create('Magebees\Navigationmenu\Model\Menucreator');
        $allParent = $menucreator->getCollection()
                    ->addFieldToFilter('parent_id', "0")
                    ->setOrder("position", "asc")
                    ->addFieldToFilter('group_id', $group_id);

        $html = '<option value="">Please Select Parent</option><option value="0">Root</option>';

        foreach ($allParent as $item) {
            $helper= $om->create('Magebees\Navigationmenu\Helper\Data');
            $space = $helper->getMenuSpace($item->getMenuId());

            if ($cur_menu_id == $item->getMenuId()) {
                $html .= '<option value="'.$item->getMenuId().'" selected>'.$space.$item->getTitle().' </option>';
            } else {
                $html .= '<option value="'.$item->getMenuId().'">'.$space.$item->getTitle().' </option>';
            }

            $hasChild = $this->getChildMenuCollection($item->getMenuId());
            if (count($hasChild)>0) {
                $html .= $this->getAddSubChildlist($item->getMenuId(), true, $cur_menu_id);
            }
        }
        return $html;
    }
    function getAddSubChildlist($parentId, $isChild, $cur_menu_id)
    {
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        $menucreator= $om->create('Magebees\Navigationmenu\Model\Menucreator');
        $allChild = $menucreator->getCollection()
                    ->setOrder("position", "asc")
                    ->addFieldToFilter('parent_id', $parentId);
        $Currentmenu = $menucreator->load($cur_menu_id);
        $current_parent_item = $Currentmenu->getParentId();
        $html = isset($html) ? $html : '';

        foreach ($allChild as $item) {
            $helper= $om->create('Magebees\Navigationmenu\Helper\Data');
            $space = $helper->getMenuSpace($item->getMenuId());

            if ($cur_menu_id == $item->getMenuId()) {
                $html .= '<option value="'.$item->getMenuId().'" selected>'.$space.$item->getTitle().' </option>';
            } else {
                $html .= '<option value="'.$item->getMenuId().'">'.$space.$item->getTitle().'</option>';
            }
            $hasChild = $this->getChildMenuCollection($item->getMenuId());
            if (count($hasChild)>0) {
                $html .= $this->getAddSubChildlist($item->getMenuId(), true, $cur_menu_id);
            }
        }
        return $html;
    }
}

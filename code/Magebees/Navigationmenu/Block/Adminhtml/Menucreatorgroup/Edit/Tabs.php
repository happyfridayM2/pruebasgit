<?php

namespace Magebees\Navigationmenu\Block\Adminhtml\Menucreatorgroup\Edit;

class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    protected function _construct()
    {
        parent::_construct();
        $this->setId('Menucreatorgroup_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Navigation Menu Pro Group'));
    }
    protected function _beforeToHtml()
    {
        $this->addTab('form_section', [
        'label'     => __('Menu Group'),
        'title'     => __('Menu Group'),
        'content'   => $this->getLayout()->createBlock('Magebees\Navigationmenu\Block\Adminhtml\Menucreatorgroup\Edit\Tab\Form')->toHtml(),
        ]);
        $this->addTab('rootitem_section', [
        'label'     => __('Menu Bar Options'),
        'title'     => __('Menu Bar Options'),
        'content'   => $this->getLayout()->createBlock('Magebees\Navigationmenu\Block\Adminhtml\Menucreatorgroup\Edit\Tab\Rootitem')->toHtml(),
        ]);
        $this->addTab('megaparent_form_color', [
        'label'     => __('Mega Menu Item Options'),
        'title'     => __('Mega Menu Item Options'),
        'content'   => $this->getLayout()->createBlock('Magebees\Navigationmenu\Block\Adminhtml\Menucreatorgroup\Edit\Tab\Megamenuitem')->toHtml(),
        ]);
        $this->addTab('subitems_form_color', [
        'label'     => __('Flyout option'),
        'title'     => __('Flyout option'),
        'content'   => $this->getLayout()->createBlock('Magebees\Navigationmenu\Block\Adminhtml\Menucreatorgroup\Edit\Tab\Submenuitem')->toHtml(),
        ]);
        $this->addTab('suboptions_form_color', [
        'label'     => __('Sub Menu Item option'),
        'title'     => __('Sub Menu Item option'),
        'content'   => $this->getLayout()->createBlock('Magebees\Navigationmenu\Block\Adminhtml\Menucreatorgroup\Edit\Tab\Suboptions')->toHtml(),
        ]);
      
      
      
        if ($this->getRequest()->getParam('id')) {
            $this->addTab('xml_section', [
                'label'         => __('Menu Embeded Code'),
                'title'         => __('Menu Embeded Code'),
                'content'   => $this->getLayout()->createBlock('Magebees\Navigationmenu\Block\Adminhtml\Menucreatorgroup\Edit\Tab\Codesnipet')->toHtml(),
            ]);
        }
     
        return parent::_beforeToHtml();
    }
}

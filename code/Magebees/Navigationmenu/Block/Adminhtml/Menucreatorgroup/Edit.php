<?php
namespace Magebees\Navigationmenu\Block\Adminhtml\Menucreatorgroup;

class Edit extends \Magento\Backend\Block\Widget\Form\Container
{
    protected function _construct()
    {
        $this->_objectId = 'id';
        $this->_controller = 'adminhtml_menucreatorgroup';
        $this->_blockGroup = 'Magebees_Navigationmenu';
     
         parent::_construct();

        $this->buttonList->update('save', 'label', __('Save Group'));
        $this->buttonList->update('delete', 'label', __('Delete Group'));
         $this->buttonList->add(
             'saveandcontinue',
             [
                'label' => __('Save and Continue Edit'),
                'class' => 'save',
                'data_attribute' => [
                    'mage-init' => ['button' => ['event' => 'saveAndContinueEdit', 'target' => '#edit_form']]
                ]
             ],
             -100
         );
         $this->_formScripts[] = "
		 window.onload = menutypeoption;
            function toggleEditor() {
                if (tinyMCE.getInstanceById('block_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'banner_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'banner_content');
                }
            }
			function menutypeoption(){
				document.getElementById('Menucreatorgroup_tabs_rootitem_section').parentElement.style.display = 'block';
document.getElementById('Menucreatorgroup_tabs_megaparent_form_color').parentElement.style.display = 'block';
document.getElementById('Menucreatorgroup_tabs_subitems_form_color').parentElement.style.display = 'block';

				var e = document.getElementById('page_menutype');
				var menutype = e.options[e.selectedIndex].value;
				menutaboptions(menutype);
			}
			function menutaboptions(value){
				if(value!=''){
					
					if(value=='smart-expand'){
						
document.getElementById('Menucreatorgroup_tabs_megaparent_form_color').parentElement.style.display = 'none';
document.getElementById('Menucreatorgroup_tabs_subitems_form_color').parentElement.style.display = 'block';
document.getElementById('Menucreatorgroup_tabs_subitems_form_color').parentElement.style.display = 'none';
document.getElementById('Menucreatorgroup_tabs_suboptions_form_color').parentElement.style.display = 'block';
document.getElementById('Menucreatorgroup_tabs_rootitem_section').parentElement.style.display = 'block';
	
					}else if(value=='always-expand'){
						

document.getElementById('Menucreatorgroup_tabs_megaparent_form_color').parentElement.style.display = 'none';
document.getElementById('Menucreatorgroup_tabs_subitems_form_color').parentElement.style.display = 'none';
document.getElementById('Menucreatorgroup_tabs_suboptions_form_color').parentElement.style.display = 'block';
document.getElementById('Menucreatorgroup_tabs_rootitem_section').parentElement.style.display = 'block';

					}else if(value=='list-item'){
						
						
document.getElementById('Menucreatorgroup_tabs_rootitem_section').parentElement.style.display = 'none';
document.getElementById('Menucreatorgroup_tabs_megaparent_form_color').parentElement.style.display = 'none';
document.getElementById('Menucreatorgroup_tabs_subitems_form_color').parentElement.style.display = 'none';
document.getElementById('Menucreatorgroup_tabs_suboptions_form_color').parentElement.style.display = 'none';
				
					}else if(value=='mega-menu'){
					document.getElementById('Menucreatorgroup_tabs_rootitem_section').parentElement.style.display = 'block';
					document.getElementById('Menucreatorgroup_tabs_megaparent_form_color').parentElement.style.display = 'block';
					document.getElementById('Menucreatorgroup_tabs_subitems_form_color').parentElement.style.display = 'block';
					document.getElementById('Menucreatorgroup_tabs_suboptions_form_color').parentElement.style.display = 'none';				
					}
						
				}
				else
				{
						document.getElementById('Menucreatorgroup_tabs_rootitem_section').parentElement.style.display = 'block';
					document.getElementById('Menucreatorgroup_tabs_megaparent_form_color').parentElement.style.display = 'block';
					document.getElementById('Menucreatorgroup_tabs_subitems_form_color').parentElement.style.display = 'block';
					document.getElementById('Menucreatorgroup_tabs_suboptions_form_color').parentElement.style.display = 'block';				
				}
			}
			;";
    }
}

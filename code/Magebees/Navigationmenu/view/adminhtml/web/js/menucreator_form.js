require([
    'jquery',
    'mage/backend/form',
    'mage/backend/validation'
], function ($) {

    $('#edit_form').form()
        .validation({
            validationUrl: '',
            highlight: function (element) {
                var detailsElement = $(element).closest('details');
                if (detailsElement.length && detailsElement.is('.details')) {
                    var summaryElement = detailsElement.find('summary');
                    if (summaryElement.length && summaryElement.attr('aria-expanded') === "false") {
                        summaryElement.trigger('click');
                    }
                }
                var result= $(element).trigger('highlight.validate');
            /* Code for switch to general tab when validation occur */
                if (result.length>0) {
                jQuery('#menucreator-tabs').tabs({
                        active: 0
                    });
                }
            }
        
        });

});

function stickyNav(stickyNavTop)
{
var scrollTop = jQuery(window).scrollTop();
    if (scrollTop > stickyNavTop) {
        jQuery('.main-col').addClass('sticky');
    } else {
        jQuery('.main-col').removeClass('sticky');
    }
}
function cancel()
{
var validator = jQuery("#edit_form").validate();
validator.resetForm();
document.getElementById("edit_form").reset();
jQuery("#storeids option:selected").removeAttr("selected");
jQuery("#setrel option:selected").removeAttr("selected");
menutype(0);


jQuery("#title").attr('readonly',false);
jQuery("#menucreator-tabs").tabs();
jQuery('#menucreator-tabs').tabs({
    active: 0
});
jQuery(".image-preview").remove();
jQuery('#menu_title').text('Create New Menu Item');
var selectedEffect = 'slide';

jQuery("#content").effect(selectedEffect, { direction: "right"  }, 500);
jQuery("#parent_id").val('');
jQuery("#group_id_content").show();
jQuery("#parent_id_content").show();
document.getElementById('menu_id').setAttribute("disabled", true);
document.getElementById('current_menu_id').setAttribute("disabled", true);
document.getElementById('menu_id').value = '';
document.getElementById('current_menu_id').value = '';
jQuery("#content").show();

}
function addnew()
{
var validator = jQuery("#edit_form").validate();
validator.resetForm();
document.getElementById("edit_form").reset();
jQuery("#storeids option:selected").removeAttr("selected");
jQuery("#setrel option:selected").removeAttr("selected");
menutype(0);
jQuery("#title").attr('readonly',false);
jQuery("#parent_id option").each(function () {
jQuery(this).remove();
});
jQuery('#parent_id').append('<option value="">Please Select Parent</option><option value="0">Root</option>');
jQuery(".image-preview").remove();
jQuery('#menu_title').text('Create New Menu Item');
var selectedEffect = 'slide';
jQuery("#content").effect(selectedEffect, { direction: "right"  }, 500);
jQuery("#content").show();
jQuery("#group_id_content").show();
jQuery("#parent_id_content").show();

jQuery("#menucreator-tabs").tabs();
jQuery('#menucreator-tabs').tabs({
    active: 0
});
document.getElementById('menu_id').setAttribute("disabled", true);
document.getElementById('current_menu_id').setAttribute("disabled", true);
document.getElementById('menu_id').value = '';
document.getElementById('current_menu_id').value = '';
}
function addsub(group_id,menu_id,url)
{
/* Here We Reset the Form when custom come from the edit to Add Sub using the Same Page.*/
document.getElementById('menu_id').setAttribute("disabled", true);
document.getElementById('current_menu_id').setAttribute("disabled", true);
var validator = jQuery("#edit_form").validate();
validator.resetForm();
document.getElementById("edit_form").reset();
jQuery(".image-preview").remove();

jQuery("#storeids option:selected").removeAttr("selected");
jQuery("#setrel option:selected").removeAttr("selected");

menutype(0);
jQuery("#title").attr('readonly',false);

jQuery("#group_id").val(group_id);
    var current_menu = menu_id;
    var e = document.getElementById("type");
    var Title = e.options[e.selectedIndex].text;
    var Value = e.options[e.selectedIndex].value;
    var url = url + 'current_menu/'+current_menu;
    var url = url+'/group_id/'+group_id;

    
    
    jQuery.ajax({
                    showLoader: true,
                    type: "GET",
                    url:url,
                    success: function (data) {
                      
                    jQuery('#parent_id').empty();
                    jQuery('#parent_id').append(data);
                    var parent_item = document.getElementById('parent_id').value;
                    
                    /* Set the Form Title When Going to Add Sub Mode Using the Add Sub Button..*/
                    var parent_item_text = document.getElementById("parent_id");
                    var Title = parent_item_text.options[parent_item_text.selectedIndex].text;
                    form_title = Title.replace(/-/g, '');
                    document.getElementById('menu_title').innerHTML = 'Add Sub Item for  "'+form_title+'" Menu Item';
                    var selectedEffect = 'slide';
                    jQuery("#content").effect(selectedEffect, { direction: "right"  }, 500);
                    jQuery("#menucreator-tabs").tabs();
                    jQuery('#menucreator-tabs').tabs({
                    active: 0
                    });
                    jQuery("#content").show();
                    }
                    });
    
jQuery("#group_id_content").hide();
jQuery("#parent_id_content").hide();


}

function showhideelement(menu_type)
{
            if (parseInt(menu_type) == "1") {
               /* Select CMS PAGES*/
                    jQuery("#cmspage_identifier").removeAttr('disabled');
                    jQuery("#select_cms_pages").show();
                    jQuery("#category_id").attr('disabled',true);
                    jQuery("#category_content").hide();
                    jQuery("#autosub").attr('disabled',true);
                    jQuery("#autosubimage").attr('disabled',true);
                    jQuery("#category_content_option").hide();
                    jQuery("#category_image_type").hide();
                    jQuery("#image_upload_show_hide").show();
                    jQuery("#image_upload").show();
                    jQuery("#image_type").attr('disabled',true);
                    jQuery("#staticblock_identifier").attr('disabled',true);
                    jQuery("#title_show_hide").attr('disabled',true);
                    jQuery("#select_static_block").hide();
                    jQuery("#show_hide_menu_title").hide();
                    jQuery("#product_id").attr('disabled',true);
                    jQuery("#product_page").hide();
                    jQuery("#url_value").attr('disabled',true);
                    jQuery("#custom_url").hide();
                    jQuery("#custom_link_content_option").hide();
            } else if (parseInt(menu_type) == "2") {
                   /* Select Category */
            
                    jQuery("#category_id").removeAttr('disabled');
                    jQuery("#category_content").show();
                    jQuery("#autosub").removeAttr('disabled');
                    jQuery("#autosubimage").removeAttr('disabled');
                    jQuery("#category_content_option").show();
                    jQuery("#image_type").removeAttr('disabled');
                    
                    /* Hide Image Upload Option for the Category Menu Type*/
                    jQuery("#image_upload_show_hide").hide();
                    jQuery("#image_upload").hide();
                    jQuery("#category_image_type").show();
                    jQuery("#cmspage_identifier").attr('disabled',true);
                    
                    jQuery("#select_cms_pages").hide();
                    
                    jQuery("#staticblock_identifier").attr('disabled',true);
                    jQuery("#title_show_hide").attr('disabled',true);
                    
                    jQuery("#select_static_block").hide();
                    jQuery("#show_hide_menu_title").hide();
                    
                    
                    jQuery("#product_id").attr('disabled',true);
                    
                    jQuery("#product_page").hide();
                    
                    jQuery("#url_value").attr('disabled',true);
                    
                    jQuery("#custom_url").hide();
                    jQuery("#custom_link_content_option").hide();
            } else if (parseInt(menu_type) == "3") {
                   /* Select Static Block */
                    
                    jQuery("#staticblock_identifier").removeAttr('disabled');
                    jQuery("#title_show_hide").removeAttr('disabled');
                    
                    jQuery("#select_static_block").show();
                    jQuery("#show_hide_menu_title").show();
                    
                
                    jQuery("#cmspage_identifier").attr('disabled',true);
                    
                    jQuery("#select_cms_pages").hide();
                    jQuery("#category_id").attr('disabled',true);
                    
                    jQuery("#category_content").hide();
                    
                    
                    jQuery("#autosub").attr('disabled',true);
                    jQuery("#autosubimage").attr('disabled',true);
                    
                    
                    jQuery("#category_content_option").hide();
                    jQuery("#category_image_type").hide();
                    jQuery("#image_upload_show_hide").show();
                    jQuery("#image_upload").show();
                    
                    jQuery("#image_type").attr('disabled',true);
                    
                    
                    jQuery("#product_id").attr('disabled',true);
                    
                    jQuery("#product_page").hide();
                    
                    jQuery("#url_value").attr('disabled',true);
                    
                    jQuery("#custom_url").hide();
                    jQuery("#custom_link_content_option").hide();
            } else if (parseInt(menu_type) == "4") {
                   /* Select Product Page */
                    
                    jQuery("#product_id").removeAttr('disabled');
                    
                    jQuery("#product_page").show();
                
                    jQuery("#cmspage_identifier").attr('disabled',true);
                    
                    jQuery("#select_cms_pages").hide();
                    
                    jQuery("#staticblock_identifier").attr('disabled',true);
                    jQuery("#title_show_hide").attr('disabled',true);
                    
                    
                    jQuery("#select_static_block").hide();
                    jQuery("#show_hide_menu_title").hide();
                    
                    
                    jQuery("#category_id").attr('disabled',true);
                    
                    jQuery("#category_content").hide();
                    
                    
                    jQuery("#autosub").attr('disabled',true);
                    jQuery("#autosubimage").attr('disabled',true);
                    
                    jQuery("#category_content_option").hide();
                
                    
                    jQuery("#image_type").attr('disabled',true);
                    
                    jQuery("#category_image_type").hide();
                    jQuery("#image_upload_show_hide").show();
                    jQuery("#image_upload").show();
                    jQuery("#url_value").attr('disabled',true);
                    
                    jQuery("#custom_url").hide();
                    jQuery("#custom_link_content_option").hide();
            } else if (parseInt(menu_type) == "5") {
                   /* Select Custom URL Page */
                    
                    jQuery("#url_value").removeAttr('disabled');
                    
                    jQuery("#custom_url").show();
                jQuery("#custom_link_content_option").show();
                    jQuery("#cmspage_identifier").attr('disabled',true);
                    
                    jQuery("#select_cms_pages").hide();
                    jQuery("#staticblock_identifier").attr('disabled',true);
                    jQuery("#title_show_hide").attr('disabled',true);
                    
                    jQuery("#select_static_block").hide();
                    jQuery("#show_hide_menu_title").hide();
                    
                    
                    jQuery("#category_id").attr('disabled',true);
                    
                    jQuery("#category_content").hide();
                    
                    
                    jQuery("#autosub").attr('disabled',true);
                    jQuery("#autosubimage").attr('disabled',true);
                    
                    jQuery("#category_content_option").hide();
                    jQuery("#category_image_type").hide();
                    jQuery("#image_type").attr('disabled',true);
                    
                    jQuery("#image_upload_show_hide").show();
                    jQuery("#image_upload").show();
                    
                    
                    jQuery("#product_id").attr('disabled',true);
                    
                    jQuery("#product_page").hide();
            } else if ((parseInt(menu_type) == "6") || (parseInt(menu_type) == "7")) {
            /* For Alias & Group Menu Item */
                    jQuery("#title_show_hide").attr('disabled',true);
                    jQuery("#show_hide_menu_title").hide();
                    jQuery("#url_value").attr('disabled',true);
                    jQuery("#custom_url").hide();
                    jQuery("#custom_link_content_option").hide();
                    jQuery("#cmspage_identifier").attr('disabled',true);
                    jQuery("#select_cms_pages").hide();
                    jQuery("#staticblock_identifier").attr('disabled',true);
                    jQuery("#select_static_block").hide();
                    jQuery("#category_id").attr('disabled',true);
                    jQuery("#category_content").hide();
                    jQuery("#autosub").attr('disabled',true);
                    jQuery("#autosubimage").attr('disabled',true);
                    jQuery("#category_content_option").hide();
                    jQuery("#category_image_type").hide();
                    jQuery("#image_type").attr('disabled',true);
                    jQuery("#image_upload_show_hide").show();
                    jQuery("#image_upload").show();
                    jQuery("#product_id").attr('disabled',true);
                    jQuery("#product_page").hide();
            } else {
                    jQuery("#title_show_hide").attr('disabled',true);
                    jQuery("#show_hide_menu_title").hide();
                    jQuery("#url_value").attr('disabled',true);
                    jQuery("#custom_url").hide();
                    jQuery("#custom_link_content_option").hide();
                    jQuery("#cmspage_identifier").attr('disabled',true);
                    jQuery("#select_cms_pages").hide();
                    jQuery("#staticblock_identifier").attr('disabled',true);
                    jQuery("#select_static_block").hide();
                    jQuery("#category_id").attr('disabled',true);
                    jQuery("#category_content").hide();
                    jQuery("#autosub").attr('disabled',true);
                    jQuery("#autosubimage").attr('disabled',true);
                    jQuery("#category_content_option").hide();
                    jQuery("#category_image_type").hide();
                    jQuery("#image_type").attr('disabled',true);
                    jQuery("#image_upload_show_hide").show();
                    jQuery("#image_upload").show();
                    jQuery("#product_id").attr('disabled',true);
                    jQuery("#product_page").hide();
            }
            
}
function formfill(response)
{
    
            var menu_type = response.type;
            var menu_id = response.menu_id;
            var group_id = response.group_id;
            var title = response.title;
            var custom_link_title = response.description;
            var image = response.image;
            var cat_id = response.category_id;
            var cms_page = response.cmspage_identifier;
            var link_identifier = response.usedlink_identifier;
            var static_block = response.staticblock_identifier;
            var title_show_hide = response.title_show_hide;
            var product = response.product_id;
            var custom_url = response.url_value;
            var external_url = response.useexternalurl;
            var status = response.status;
            var target_window = response.target;
            var store_ids = response.storeids;
            var cat_auto_sub = response.autosub;
            var auto_sub_image = response.autosubimage;
            var cat_change_title = response.use_category_title;
            var align = response.text_align;
            var image_type = response.image_type;
            var column_layout = response.subcolumnlayout;
            var custom_class = response.class_subfix;
            var permission = response.permission;
            var image_status = response.image_status;
            var storeids = response.storeids;
            var relations = response.setrel;
    
    
            document.getElementById('menu_id').value = menu_id;
            
            document.getElementById('current_menu_id').value = menu_id;
            
            jQuery('#menu_title').text('Edit "'+title+'" Menu Item');
            
            jQuery('select#type').find('option[value='+menu_type+']').prop('selected', true);
            
            jQuery('select#category_id').find('option[value='+cat_id+']').prop('selected', true);
            
            if (cat_auto_sub == "1") {
            jQuery('#autosub').val(1);
            jQuery('#autosub').attr('checked', 'checked');
            } else if (cat_auto_sub == "0") {
            jQuery('#autosub').val(0);
            jQuery('#autosub').removeAttr('checked', 'checked');
            }
            if (auto_sub_image == "1") {
            jQuery('#autosubimage').val(1);
            jQuery('#autosubimage').attr('checked', 'checked');
            } else if (auto_sub_image == "0") {
            jQuery('#autosubimage').val(0);
            jQuery('#autosubimage').removeAttr('checked', 'checked');
            }
            if (external_url == "1") {
            jQuery('#useexternalurl').val(1);
            jQuery('#useexternalurl').attr('checked', 'checked');
            } else if (external_url == "0") {
            jQuery('#useexternalurl').val(0);
            jQuery('#useexternalurl').removeAttr('checked', 'checked');
            }
            
            
            showhideelement(menu_type);
            setExternal(external_url);
            jQuery('select#cmspage_identifier').find('option[value='+cms_page+']').prop('selected', true);
            
            
            jQuery('select#staticblock_identifier').find('option[value='+static_block+']').prop('selected', true);
            jQuery('#title_show_hide').val(title_show_hide);
            
            jQuery('#product_id').val(product);
            jQuery('#url_value').val(custom_url);
            jQuery('#title').val(title);
            jQuery('#class_subfix').val(custom_class);
            jQuery('#status').val(status);
            jQuery('#group_id').val(group_id);
            
            jQuery('#permission').val(permission);
            jQuery('#target').val(target_window);
            jQuery('#description').val(custom_link_title);
            jQuery('#image_type').val(image_type);
            jQuery('#image_status').val(image_status);
            jQuery('#text_align').val(align);
            jQuery('#subcolumnlayout').val(column_layout);
             

             if (image=== null) {
             jQuery('#image').attr('style','margin-left:0px');
                jQuery("#image_image").remove();
                jQuery(".delete-image").remove();
                jQuery(".image-preview").remove();
             } else {
                    var image_path = document.getElementById('image_path').value;
                        jQuery('#image').attr('style','margin-left:25px');

                        var link='<a class="image-preview"><img width="50" height="50" class="small-image-preview v-middle" alt="' +
                        image + '" title="' + image + '" id="image_image" src="' + image_path + image + '"></a>';
                        var deleteCheckbox = '<span class="delete-image">' +
                        '<input type="checkbox" name="remove_img_main" id="remove_img_main" class="checkbox" value="0" onclick="this.value = this.checked ? 1 : 0;" name="image[delete]">' +
                        '<label for="remove_img_main"> Delete Image</label>' +
                        '<input type="hidden" value="' + image + '" name="image[value]"></span>';

                        // Remove exists item before append
                        jQuery(".delete-image").remove();
                        jQuery(".image-preview").remove();

                        jQuery("#image").parent().append(link);
                        jQuery("#image").parent().append(deleteCheckbox);
                }
            
            var storeArray = storeids.split(",");
    
            var select = document.getElementById("storeids");
            for (count=0; count<storeArray.length; count++) {
                for (i=0; i<select.options.length; i++) {
                    if (select.options[i].value == storeArray[count]) {
                        select.options[i].selected="selected";
                        }
                }
            }
            if (relations!=null) {
            var res = relations.split(" ");
            var relationsArray = relations.split(" ");
            var select_rel = document.getElementById("setrel");
            for (count=0; count<relationsArray.length-1; count++) {
                for (i=0; i<select_rel.options.length; i++) {
                    if (select_rel.options[i].value == relationsArray[count]) {
                        select_rel.options[i].selected="selected";
                        }
                }
            }
            }
            
            

        return true;
}
function editform(url)
{
jQuery("#menucreator-tabs").tabs();
jQuery('#menucreator-tabs').tabs({
    active: 0
});
jQuery(".image-preview").remove();
/* Show Group Id & Parent Item Drop Down.*/
jQuery("#group_id_content").show();
jQuery("#parent_id_content").show();
jQuery("#title").attr('readonly',false);
if (jQuery('#menu_id').attr('disabled')) {
jQuery('#menu_id').removeAttr('disabled');
}
//alert('edit_form');

/* To Reset the Magento Form Validation here editForm is the Object to set the validations.*/
var validator = jQuery("#edit_form").validate();
validator.resetForm();
document.getElementById("edit_form").reset();
    jQuery.ajax({
                    showLoader: true,
                    type: "GET",
                    url:url,
                    success: function (data) {
                      
            var response = jQuery.parseJSON(data);
            
                        if (response != '') {
            jQuery("#storeids option").each(function () {
                jQuery(this).removeAttr("selected");
            });
            jQuery("#setrel option").each(function () {
                jQuery(this).removeAttr("selected");
            });
            
            formfill(response);
            
                var parent_id = response.parent_id;
            var group_id = response.group_id;
            var url = document.getElementById('parent_url').value;
            
            parent_item_form(group_id,url);
                
            jQuery('select#parent_id').find('option[value='+parent_id+']').prop('selected', true);
            /* Slide the Form From the Left*/
             var options = {};
                    var selectedEffect = 'slide';
                    jQuery("#content").effect(selectedEffect, { direction: "right"  }, 500);
                    jQuery("#content").show();
            }
                    }
                    });

}

function openMyPopup(url)
{
    var dialogWindow = Dialog.info(null, {
            closable:true,
            resizable:false,
            draggable:true,
            className:'magento',
            windowClassName:'popup-window',
            title:'Change Menu Item Details',
            top:50,
            width:1000,
            height:800,
            zIndex:1000,
            recenterAuto:false,
            hideEffect:Element.hide,
            showEffect:Element.show,
            id:'browser_window',
            cache: false,
            url:url,
            onClose:function (param, el) {
               // alert('onClose');
            }
        });
        
    }
    function closePopup()
    {
        Windows.close('browser_window');
    }
function deleteitem(delete_url,current_url)
{

if (delete_url != '') {
jQuery("#deletedialog").css("display", "block");
 jQuery("#deletemenuitem #deleteitem").click(function () {
 var deleteoption = '';
 if (document.getElementById('deleteparent').checked) {
 var deleteoption = document.getElementById('deleteparent').value;
    var url = '';
    var url = delete_url+"deleteoption/"+deleteoption;
 } else if (document.getElementById('deleteparentchild').checked) {
  var deleteoption = document.getElementById('deleteparentchild').value;
  var url = '';
    var url = delete_url+"deleteoption/"+deleteoption;
}
    jQuery.ajax({
                    showLoader: true,
                    type: "GET",
                    url:url,
                    success: function (data) {
                      
                        if (data) {
                        location.href = current_url;
                        }
                    }
                    });
     document.getElementById('deletemenuitem').reset();
    jQuery("#deletedialog").css("display", "none");
 
url = '';
});
 jQuery('#deletemenuitem #cancelitem').click(function () {
 document.getElementById('deletemenuitem').reset();
jQuery("#deletedialog").css("display", "none");
 });
 jQuery('#deletemenuitem .ui-icon-closethick').click(function () {
 document.getElementById('deletemenuitem').reset();
jQuery("#deletedialog").css("display", "none");
 });
}
}

function parent_item(group_id,url)
{

    var e = document.getElementById("type");
    var Title = e.options[e.selectedIndex].text;
    var Value = e.options[e.selectedIndex].value;
    
    var url = url+'group_id/'+group_id;
    if (group_id != '') {
    jQuery.ajax({
                    showLoader: true,
                    type: "GET",
                    url:url,
                    success: function (data) {
                      
                
                    $('parent_id').innerHTML=data;
                    //$('parent_id').append=transport.responseText;
                    var parent_item = document.getElementById('parent_id').value;
                    /* Disable the Sub Column Layout and Text Align When the Page is load in the edit mode Here If Parent Item is 0 or blank then
					only subcolumn layout and Text Align is display other wise it will be disabled*/
                    
                    if ((parent_item.trim() == '') || (parent_item.trim() == '0')) {
                    var parent_node = document.getElementById("subcolumnlayout").parentNode.parentNode;
                    var align_parent_node = document.getElementById("text_align").parentNode.parentNode;
                    parent_node.style.display = 'display:block;'
                    align_parent_node.style.display = 'display:block;'
                    document.getElementById("subcolumnlayout").disabled=false;
                    document.getElementById("text_align").disabled=false;
                    } else {
                    var parent_node = document.getElementById("subcolumnlayout").parentNode.parentNode;
                    var align_parent_node = document.getElementById("text_align").parentNode.parentNode;
                    parent_node.style = 'display:none;'
                    align_parent_node.style = 'display:none;'
                    document.getElementById("text_align").disabled=true;
                    document.getElementById("subcolumnlayout").disabled=true;
                    }
                    }
                    });
    } else if (group_id == '') {
    $('parent_id').innerHTML = "<option value='0'>Please Select Parent</option><option value='0'>Root</option>";
    //$('parent_id').innerHTML = "<option value='0'>Root</option>";
    }

}
function parent_item_form(group_id,url)
{
    var current_menu = document.getElementById('current_menu_id').value;
    var e = document.getElementById("type");
    var Title = e.options[e.selectedIndex].text;
    var Value = e.options[e.selectedIndex].value;
    var url = url + 'current_menu/'+current_menu;
    var url = url+'/group_id/'+group_id;
    
    if (group_id != '') {
    jQuery.ajax({
                    showLoader: true,
                    type: "GET",
                    url:url,
                    success: function (data) {
                      alert('response');
                        console.log(data);
                        alert('response done');
                        jQuery('parent_id').Html=data;
                    jQuery('#parent_id').empty();
                    jQuery('#parent_id').append(data);
                    jQuery('parent_id').innerHTML=data;
                    
                    /* Remoev Root Option From the Parent Item's when Menu Item type is group.*/
                    var menu_type = document.getElementById('type').value;
                     if (menu_type=="7") {
                        jQuery("#parent_id option[value='0']").remove();
                        } else {
                        if (!jQuery("option[value='0']", "#parent_id").length) {
                            jQuery('#parent_id option:first').after(jQuery('<option />', { "value": '0', text: 'Root'}));
                            }
                        }
                    var parent_item = document.getElementById('parent_id').value;
                    /* Disable the Sub Column Layout and Text Align When the Page is load in the edit mode Here If Parent Item is 0 or blank then
					only subcolumn layout and Text Align is display other wise it will be disabled*/
                    
                    }
                    });
    } else if (group_id == '') {
    $('parent_id').innerHTML = "<option value='0'>Please Select Parent</option><option value='0'>Root</option>";
    
    return false;
    }

}
function staticblock_title()
{
    var e = document.getElementById("staticblock_identifier");
    var staticblock_name = e.options[e.selectedIndex].text;
    var staticblock_id = e.options[e.selectedIndex].value;
    
    if (staticblock_id != '') {
    /*document.getElementById("url_value").value = staticblock_id;*/
    document.getElementById("title").value = staticblock_name;
    } else {
        document.getElementById("title").value = '';
    }
    }
    function cat_title()
    {
    var e = document.getElementById("category_id");
    var Cat_name = e.options[e.selectedIndex].text;
    var Cat_id = e.options[e.selectedIndex].value;
        if (Cat_id != '') {
        var Cat_name = Cat_name.replace(/-/g, '');
        var Cat_name = Cat_name.trim();
        document.getElementById("title").value = Cat_name;
        } else {
            document.getElementById("title").value ="";
        }
    }
function cms_title()
{
        var e = document.getElementById("cmspage_identifier");
        var Cms_name = e.options[e.selectedIndex].text;
        var Cms_id = e.options[e.selectedIndex].value;

        if (Cms_id != '') {
        document.getElementById("title").value = Cms_name;
        } else {
            document.getElementById("title").value ="";
        }
    }
function menutype(type)
{
    showhideelement(type);
    if (isNaN(type)==true) {
    var e = document.getElementById("type");
    var Title = e.options[e.selectedIndex].text;
    var Value = e.options[e.selectedIndex].value;
    document.getElementById("title").value = Title;
    } else {
    document.getElementById("title").value = '';
    }
    if (type=="2") {
    document.getElementById("autosub").checked =false;
        document.getElementById("autosubimage").checked =false;
    }
    if (type=="5") {
    document.getElementById("useexternalurl").checked =false;
    }
    /* Remove Root Option From the Parent Item when menu type is group.*/
    if (type=="7") {
    jQuery("#parent_id option[value='0']").remove();
    } else {
    if (!jQuery("option[value='0']", "#parent_id").length) {
        jQuery('#parent_id option:first').after(jQuery('<option />', { "value": '0', text: 'Root'}));
        }
    }
    
}
function expandall()
{

var all_items = jQuery('ol.sortable li');
all_items.each(function () {
    var li_class = jQuery(this).attr('class');
    
    jQuery(this).removeClass('mjs-nestedSortable-collapsed');
    jQuery(this).addClass('mjs-nestedSortable-expanded');
    jQuery(this).find("div > span").removeClass('ui-icon-plusthick');
    jQuery(this).find("div > span").addClass('ui-icon-minusthick');
    
})
jQuery('#footerBut').addClass('noFixed');
jQuery('#footerBut').removeClass('fixed');
footersticky();
}
function collapseall()
{
var all_items = jQuery('ol.sortable li');
all_items.each(function () {
    var li_class = jQuery(this).attr('class');
    
    jQuery(this).addClass('mjs-nestedSortable-collapsed');
    jQuery(this).removeClass('mjs-nestedSortable-expanded');
    jQuery(this).find("div > span").addClass('ui-icon-plusthick');
    jQuery(this).find("div > span").removeClass('ui-icon-minusthick');
        
})
    jQuery('#footerBut').addClass('noFixed');
    jQuery('#footerBut').removeClass('fixed');
    footersticky();

}

function footersticky()
{
var winHeight = jQuery(window).height();
    footerTop = jQuery('#footerBut').offset().top;
var y = jQuery(this).scrollTop();
            if (y+winHeight>footerTop) {
                jQuery('#footerBut').addClass('noFixed');
                jQuery('#footerBut').removeClass('fixed');
            } else {
                jQuery('#footerBut').addClass('fixed');
                jQuery('#footerBut').removeClass('noFixed');
            }
}
function setExternal(value)
{
var isChecked = jQuery('#useexternalurl').is(':checked');
if (isChecked) {
jQuery('#useexternalurl').val("1");
    if (jQuery("#url_value").hasClass('required-entry')) {
    jQuery("#url_value").addClass("validate-url");
    }
} else {
jQuery('#useexternalurl').val("0");

    if (jQuery("#url_value").hasClass('validate-url')) {
    jQuery("#url_value").removeClass("validate-url");
    }
    if (jQuery('#advice-validate-url-url_value').length>0) {
    var advice_validate = document.getElementById('advice-validate-url-url_value');
    advice_validate.style.opacity = "0";
    advice_validate.style.display = "none";
    jQuery("#url_value").removeClass("validation-failed");
    }
}
}
function storeFilter(storeids)
{
            if (storeids != 0) {
            var i=0;
                jQuery("#navmenu ol li").each(function () {
                i++;
                    var id = jQuery(this).attr("id");
                    if (jQuery(this).attr('store')) {
                    if ((id != 0)) {
                        var menu_store = jQuery(this).attr("store");
                        var menuStoreArr = new Array();
                        menuStoreArr = menu_store.split(',');
                        
                        if (jQuery.inArray(storeids, menuStoreArr) != -1 || jQuery.inArray(0, menuStoreArr) != -1) {
                            jQuery(this).css("display", "block");
                        } else {
                        jQuery(this).css("display", "none");
                        }
                    }
                    } else {
                    }
                    
                    
                });
            } else {
                /* When all store view selected */
                jQuery("#navmenu ol li").each(function () {
                    jQuery(this).css("display", "block");
                });
            }
        /* Set Sticky when Change the filter from the drop down of the store.*/
        jQuery('#footerBut').addClass('noFixed');
        jQuery('#footerBut').removeClass('fixed');
        footersticky();
        }

define(
    [
    "jquery",
    "jquery/ui",
    "jquery/colorpicker/js/colorpicker",
    "mage/backend/form",
    "mage/backend/validation","nestedSortable",
    ],
    function (jQuery) {
      "use strict";
        jQuery.widget('Navigationmenu.menugrid',{
                
              _create: function () {
                 jQuery("#collapsed").click(jQuery.proxy(function () {
                    this._collapsed();
                    this._footersticky();
                }, this));
                jQuery("#expandall").click(jQuery.proxy(function () {
                    this._expandall();
                    this._footersticky();
                }, this));
                jQuery("#customimage").click(jQuery.proxy(this._custom_cat_image,this));
                
                jQuery("#saveorder").click(jQuery.proxy(this._saveorder,this));
                jQuery("#add_new_btn").click(jQuery.proxy(this.add_new_form,this));
                jQuery("#cancel_btn").click(jQuery.proxy(this.cancel_form,this));
                jQuery(".edit-menu-item").click(jQuery.proxy(this._editmenuitem,this));
                jQuery(".add-menu-item").click(jQuery.proxy(this._addsubmenuitem,this));
                jQuery(".delete-menu-item").click(jQuery.proxy(this._deletemenuitem,this));
                jQuery("#type").change(jQuery.proxy(this._type,this));
                jQuery("#cmspage_identifier").change(jQuery.proxy(this._set_cms_title,this));
                jQuery("#category_id").change(jQuery.proxy(this._set_cat_title,this));
                jQuery("#image_type").change(jQuery.proxy(this._set_cat_image,this));
                
                jQuery("#staticblock_identifier").change(jQuery.proxy(this._set_staticblock_title,this));
                jQuery("#useexternalurl").change(jQuery.proxy(this._setExternal,this));
                jQuery("#group_id").change(jQuery.proxy(this._parent_item_form,this));
                jQuery("#store_switcher").change(jQuery.proxy(function () {
                    this._store_filter();
                    this._footersticky();
                }, this));
                  
                  
                    this.fillJsColor('label_text_bg_color','#db0000');
                    this.fillJsColor('label_text_color','#ffff00');
                  
                  
                  
                  
                /* Toggle grid menu item plus && minus*/
                jQuery('.disclose').on('click', function () {
                jQuery(this).closest('li').toggleClass('mjs-nestedSortable-collapsed').toggleClass('mjs-nestedSortable-expanded');
                jQuery(this).toggleClass('ui-icon-plusthick').toggleClass('ui-icon-minusthick');
                });
                
                },
                
            _init: function () {
                this._load();
                this._form_validation();
                this._sortable();
                
            },
            
            _load: function () {
                jQuery('#menucreator-tabs').tabs({
                    active: 0
                });
                var stickyNavTop = jQuery('.main-col').offset().top;
                var stickyNavWidth = jQuery('#content.main-col').width();
                var winHeight = jQuery(window).height();
                var     boxWidth = jQuery('#navigation-pro div.side-col').width();
                jQuery('#footerBut').css('width',boxWidth);
                
                jQuery(window).scroll(function (event) {
                
                    var scrollTop = jQuery(window).scrollTop();
                    if (scrollTop > stickyNavTop) {
                        jQuery('.main-col').addClass('sticky');
                        jQuery('.main-col > .main-col-inner').width(stickyNavWidth);
                    } else {
                        jQuery('.main-col').removeClass('sticky');
                        jQuery('.main-col > .main-col-inner').width('auto');
                    }
                
                    var docHeight = jQuery(document).height()-250;
                    if (scrollTop+winHeight>docHeight) {
                        jQuery('#footerBut').addClass('noFixed');
                        jQuery('#footerBut').removeClass('fixed');
                    } else {
                        jQuery('#footerBut').addClass('fixed');
                        jQuery('#footerBut').removeClass('noFixed');
                    }
                });
                
                jQuery('#menucreator-tabs a[href="#label-option"]').click(function () {
                    //alert("top");
                    jQuery(window).scrollTop(100);
                });
                
                
            },
            _sortable:function () {
                jQuery('.sortable').nestedSortable({
                    connectWith: ".sortable",
                    forcePlaceholderSize: true,
                    handle: 'div',
                    helper:     'clone',
                    items: 'li',
                    opacity: .6,
                    placeholder: 'placeholder',
                    revert: 250,
                    tabSize: 25,
                    tolerance: 'pointer',
                    toleranceElement: '> div',
                    maxLevels: 10,
                    isTree: true,
                    expandOnHover: 700,
                    startCollapsed: false,
                    change: function () {
                        
                        }
                });
            },
        _form_validation:function () {
                jQuery('#edit_form').form()
                .validation({
                    validationUrl: '',
                    highlight: function (element) {
                        var detailsElement = jQuery(element).closest('details');
                        if (detailsElement.length && detailsElement.is('.details')) {
                            var summaryElement = detailsElement.find('summary');
                            if (summaryElement.length && summaryElement.attr('aria-expanded') === "false") {
                                summaryElement.trigger('click');
                            }
                        }
                        var result= jQuery(element).trigger('highlight.validate');
                        if (result.length>0) {
                        jQuery('#menucreator-tabs').tabs({
                                active: 0
                            });
                        }
                        if (element.id=="image") {
                            var result= jQuery(element).trigger('highlight.validate');
                            if (result.length>0) {
                            jQuery('#menucreator-tabs').tabs({
                                active: 1
                            });
                            }
                        }
                    }
                });
            },
            _store_filter: function () {
            var storeids = jQuery("#store_switcher option:selected").val();
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
                jQuery('#footerBut').addClass('noFixed');
                jQuery('#footerBut').removeClass('fixed');
            }
            this._footersticky();
            },
            _custom_cat_image:function () {
                if (jQuery("#customimage").is(':checked')) {
                    jQuery('#menucreator-tabs').tabs({
                    active: 1
                    });
                    jQuery("#image").addClass("required-file");
                    jQuery("#image_upload").show();
                } else {
                    jQuery("#image_upload").hide();
                    jQuery("#image").removeClass("required-file");
                }
                
                
            },
            _saveorder:function (event) {
                var url = jQuery(event.currentTarget).attr('data-saveOrder-url');
                    var indexList = [];
                    var menuitemorderList = [];
                    var Group_menus = jQuery('#navmenu ol.sortable')
                    Group_menus.each(function () {
                    var topIndex = jQuery(this).attr('id');
                        jQuery(this).addClass(topIndex);
                        indexList.push((topIndex));
                    });
                    var menuitemorderList = [];
                    var all_menuitemorder = '';
                    for (var i=0; i<indexList.length; i++) {
                    var serialized = jQuery('ol.'+indexList[i]).nestedSortable('serialize');
                        menuitemorderList.push((serialized));
                        var id = indexList[i].replace("groupid-","");
                        var group_id = "&"+"group[id]="+id+"&";
                        serialized = group_id + serialized;
                        
                        all_menuitemorder += serialized;
                    }
                    var all_menuitemorder_data = all_menuitemorder;
                    jQuery.ajax({
                        showLoader: true,
                        type: "POST",
                        url:url,
                        data: { menuitemorder : all_menuitemorder_data } ,
                        success: function (data) {
                        
                        location.reload();
                        
                        }
                    });
                    all_menuitemorder_data = '';
                    all_menuitemorder = '';
                    this._footersticky();
            },
            _collapsed:function () {
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
            },
            _expandall:function () {
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
            },
            _footersticky:function () {
                
                var winHeight = jQuery(window).height();
                var footerTop = jQuery('#footerBut').offset().top;
                var y = jQuery(this).scrollTop();
                if (y+winHeight>footerTop) {
                    jQuery('#footerBut').addClass('noFixed');
                    jQuery('#footerBut').removeClass('fixed');
                } else {
                    jQuery('#footerBut').addClass('fixed');
                    jQuery('#footerBut').removeClass('noFixed');
            }
            },
            _editmenuitem:function (event) {
                
                var url = jQuery(event.currentTarget).attr('data-editurl');
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
                var validator = jQuery("#edit_form").validate();
                validator.resetForm();
                jQuery('#edit_form')[0].reset();
                //jQuery("#label_text_bg_color").css("background-color", "");
                //jQuery("#label_text_color").css("background-color", "");
                
                //this.fillJsColor('label_text_bg_color',null);
                //this.fillJsColor('label_text_color',null);
                
                this._ajaxeditform(url);
                
            },
            _addsubmenuitem:function (event) {
                var url = jQuery(event.currentTarget).attr('data-addsuburl');
                var menu_id = jQuery(event.currentTarget).attr('data-menuid');
                var group_id = jQuery(event.currentTarget).attr('data-groupid');
                jQuery("#menu_id").attr('disabled',true);
                jQuery("#current_menu_id").attr('disabled',true);
                var validator = jQuery("#edit_form").validate();
                validator.resetForm();
                jQuery('#edit_form')[0].reset();
                jQuery(".image-preview").remove();
                jQuery("#storeids option:selected").removeAttr("selected");
                jQuery("#setrel option:selected").removeAttr("selected");
                jQuery("#label_text_bg_color").css("background-color", "");
                jQuery("#label_text_color").css("background-color", "");
                this.menutype(0);
                jQuery("#title").attr('readonly',false);
                jQuery("#group_id").val(group_id);
                var current_menu = menu_id;
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
                    var form_title = Title.replace(/-/g, '');
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
                    
            },
            _deletemenuitem:function (event) {
                var currenturl = jQuery(event.currentTarget).attr('data-currenturl');
                var deleteurl = jQuery(event.currentTarget).attr('data-deleteurl');
                
                    if (deleteurl != '') {
                    jQuery("#deletedialog").css("display", "block");
                    jQuery("#deletemenuitem #deleteitem").click(function () {
                        if (jQuery('#deleteparent').is(':checked')) {
                        var deleteoption = jQuery("#deleteparent").val();
                        var url = '';
                        var url = deleteurl+"deleteoption/"+deleteoption;
                        } else if (jQuery('#deleteparentchild').is(':checked')) {
                        var deleteoption = jQuery("#deleteparentchild").val();
                        var url = '';
                        var url = deleteurl+"deleteoption/"+deleteoption;
                        }
                        
                        jQuery.ajax({
                                showLoader: true,
                                type: "GET",
                                url:url,
                                success: function (data) {
                                
                                    if (data) {
                                        location.href = currenturl;
                                        }
                                }
                            });
                        //document.getElementById("deletemenuitem").reset();
                        jQuery('#deletemenuitem')[0].reset();
                        jQuery("#deletedialog").css("display", "none");
                     url = '';
                    });
                     jQuery('#deletemenuitem #cancelitem').click(function () {
                        jQuery('#deletemenuitem')[0].reset();
                    jQuery("#deletedialog").css("display", "none");
                     });
                     jQuery('#deletemenuitem .ui-icon-closethick').click(function () {
                     jQuery('#deletemenuitem')[0].reset();
                        jQuery("#deletedialog").css("display", "none");
                     });
                    }
            },
            _ajaxeditform:function (url) {
                var self = this;
                jQuery.ajax({
                    showLoader: true,
                    type: "GET",
                    url:url,
                    success: function (data) {
                    
                    var response = jQuery.parseJSON(data);
                    if (response!="") {
                        jQuery("#storeids option").each(function () {
                            jQuery(this).removeAttr("selected");
                        });
                        jQuery("#setrel option").each(function () {
                            jQuery(this).removeAttr("selected");
                        });
                        self._formfill(response);
                        
                        var parent_id = response.parent_id;
                        var group_id = response.group_id;
                        var url = document.getElementById('parent_url').value;
                        self._auto_fill_parent_item(group_id,url);
                        jQuery('select#parent_id').find('option[value='+parent_id+']').prop('selected', true);
                        var options = {};
                        var selectedEffect = 'slide';
                        jQuery("#content").effect(selectedEffect, { direction: "right"  }, 500);
                        jQuery("#content").show();
                        }
                
                    },
                });
                
            },
            add_new_form:function () {
                var validator = jQuery("#edit_form").validate();
                validator.resetForm();
                
                jQuery('#edit_form')[0].reset();
                jQuery("#storeids option:selected").removeAttr("selected");
                jQuery("#setrel option:selected").removeAttr("selected");
                this.menutype(0);
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
                jQuery("#menu_id").attr('disabled',true);
                jQuery("#menu_id").val('');
                jQuery("#current_menu_id").attr('disabled',true);
                jQuery("#current_menu_id").val('');
                jQuery("#label_text_bg_color").css("background-color", "");
                jQuery("#label_text_color").css("background-color", "");
                
                this.fillJsColor('label_text_bg_color','#db0000');
                this.fillJsColor('label_text_color','#ffff00');
            },
            cancel_form:function () {
                var validator = jQuery("#edit_form").validate();
                validator.resetForm();
                
                jQuery('#edit_form')[0].reset();
                jQuery("#storeids option:selected").removeAttr("selected");
                jQuery("#setrel option:selected").removeAttr("selected");
                this.menutype(0);
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
                //jQuery("#label_text_bg_color").css("background-color", "");
                //jQuery("#label_text_color").css("background-color", "");
                
                
                this.fillJsColor('label_text_bg_color','#db0000');
                this.fillJsColor('label_text_color','#ffff00');

            },
            _type:function (event) {
                var type = jQuery(event.currentTarget).val();
                this.menutype(type);
            },
            _set_cms_title:function () {
                var Cms_name = jQuery("#cmspage_identifier option:selected").text();
                var Cms_id = jQuery("#cmspage_identifier option:selected").val();
                if (Cms_id != '') {
                jQuery("#title").val(Cms_name);
                } else {
                    jQuery("#title").val("");
                }
            },
            _set_cat_title:function () {
                var Cat_name = jQuery("#category_id option:selected").text();
                var Cat_id = jQuery("#category_id option:selected").val();
                if (Cat_id != '') {
                var Cat_name = Cat_name.replace(/-/g, '');
                var Cat_name = Cat_name.trim();
                jQuery("#title").val(Cat_name);
                document.getElementById("title").value = Cat_name;
                } else {
                    jQuery("#title").val("");
                }
            },
            _set_cat_image:function () {
                var image_type = jQuery("#image_type option:selected").val();
                if (image_type == 'none') {
                document.getElementById("autosubimage").checked =false;
                document.getElementById("autosubimage").value ="0";
                } else {
                }
            },
            _set_staticblock_title:function () {
    
                var staticblock_name = jQuery("#staticblock_identifier option:selected").text();
                var staticblock_id = jQuery("#staticblock_identifier option:selected").val();
    
                if (staticblock_id != '') {
                jQuery("#title").val(staticblock_name);
                } else {
                    jQuery("#title").val("");
                }
            },
            _setExternal:function () {
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
            },
            _parent_item_form:function (event) {
                var parenturl = jQuery(event.currentTarget).attr('data-parenturl');
                var group_id = jQuery('#group_id').val();
                var current_menu = jQuery('#current_menu_id').val();
                var url = parenturl + 'current_menu/'+current_menu;
                var url = url+'/group_id/'+group_id;
                if (group_id != '') {
                jQuery.ajax({
                                showLoader: true,
                                type: "GET",
                                url:url,
                                success: function (data) {
                                
                                jQuery('parent_id').Html=data;
                                jQuery('#parent_id').empty();
                                jQuery('#parent_id').append(data);
                                jQuery('parent_id').innerHTML=data;
                                
                                /* Remoev Root Option From the Parent Item's when Menu Item type is group.*/
                                var menu_type = jQuery('#type').val();
                                if (menu_type=="7") {
                                    jQuery("#parent_id option[value='0']").remove();
                                    } else {
                                    if (!jQuery("option[value='0']", "#parent_id").length) {
                                        jQuery('#parent_id option:first').after(jQuery('<option />', { "value": '0', text: 'Root'}));
                                        }
                                    }
                                var parent_item = document.getElementById('parent_id').value;
                                }
                                });
                } else if (group_id == '') {
                jQuery('parent_id').innerHTML = "<option value='0'>Please Select Parent</option><option value='0'>Root</option>";
                }
    
            },
            _auto_fill_parent_item:function (group_id,url) {
                var current_menu = jQuery('#current_menu_id').val();
                var url = url + 'current_menu/'+current_menu;
                var url = url+'/group_id/'+group_id;
                if (group_id != '') {
                jQuery.ajax({
                                showLoader: true,
                                type: "GET",
                                url:url,
                                success: function (data) {
                                
                                jQuery('parent_id').Html=data;
                                jQuery('#parent_id').empty();
                                jQuery('#parent_id').append(data);
                                jQuery('parent_id').innerHTML=data;
                                
                                /* Remoev Root Option From the Parent Item's when Menu Item type is group.*/
                                var menu_type = jQuery('#type').val();
                                if (menu_type=="7") {
                                    jQuery("#parent_id option[value='0']").remove();
                                    } else {
                                    if (!jQuery("option[value='0']", "#parent_id").length) {
                                        jQuery('#parent_id option:first').after(jQuery('<option />', { "value": '0', text: 'Root'}));
                                        }
                                    }
                                var parent_item = jQuery('#parent_id').val();
                                }
                                });
                } else if (group_id == '') {
                jQuery('parent_id').innerHTML = "<option value='0'>Please Select Parent</option><option value='0'>Root</option>";
                }
            },
            menutype:function (type) {
                this.showhideelement(type);
                if (isNaN(type)==true) {
                var Title = jQuery("select[id='type'] option:selected").text();
                var Value = jQuery("select[id='type'] option:selected").val();
                jQuery("#type").val(Value);
                jQuery("#title").val(Title);
                } else {
                document.getElementById("title").value = '';
                }
                if (type=="2") {
                document.getElementById("autosub").checked =false;
                    document.getElementById("autosubimage").checked =false;
                    document.getElementById("customimage").checked =false;
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
            },
            showhideelement :function (menu_type) {
            
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
                    
                    if (jQuery('#customimage').val()=="1") {
                    jQuery('#image_upload').show();
                    } else {
                        jQuery('#image_upload').hide();
                    }
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
            },
            _hexToRgb:function (hexcolor) {
                var result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hexcolor);
                return result ? {
                    r: parseInt(result[1], 16),
                    g: parseInt(result[2], 16),
                    b: parseInt(result[3], 16)
                } : null;
            },
            fillJsColor:function (elementId,colorValue) {
                 var $el = jQuery("#"+elementId);
                    
                    
                    $el.css("backgroundColor", colorValue);
                    
                    // Attach the color picker
                    $el.ColorPicker({
                        color: colorValue,
                        onChange: function (hsb, hex, rgb) {
                            $el.css("backgroundColor", "#" + hex).val("#" + hex);
                        }
                    });
                  
            },
            _formfill:function (response) {
                
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
            var cat_custom_image = response.show_custom_category_image;
            var cat_change_title = response.use_category_title;
            var align = response.text_align;
            //var image_type = response.image_type;
            var image_type = response.show_category_image;
            var column_layout = response.subcolumnlayout;
            var custom_class = response.class_subfix;
            var permission = response.permission;
            var image_status = response.image_status;
            var storeids = response.storeids;
            var relations = response.setrel;
            //var label_show_hide = response.label_show_hide;
            var label_title = response.label_title;
            var label_height = response.label_height;
            //var label_width = response.label_width;
            var label_bg_color = response.label_bg_color;
            var label_color = response.label_color;
                
                
            document.getElementById('menu_id').value = menu_id;
            document.getElementById('current_menu_id').value = menu_id;
            jQuery('#menu_title').text('Edit "'+title+'" Menu Item');
            if (menu_type) {
            jQuery('select#type').find('option[value='+menu_type+']').prop('selected', true);
            }
            
            if (cat_id) {
            jQuery('select#category_id').find('option[value='+cat_id+']').prop('selected', true);
            }
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
            if (cat_custom_image == "1") {
            jQuery('#customimage').val(1);
            jQuery('#customimage').attr('checked', 'checked');
            jQuery('#image_upload').show();
            } else if (cat_custom_image == "0") {
            jQuery('#customimage').val(0);
            jQuery('#customimage').removeAttr('checked', 'checked');
            jQuery('#image_upload').hide();
            }
            
            if (external_url == "1") {
            jQuery('#useexternalurl').val(1);
            jQuery('#useexternalurl').attr('checked', 'checked');
            } else if (external_url == "0") {
            jQuery('#useexternalurl').val(0);
            jQuery('#useexternalurl').removeAttr('checked', 'checked');
            }
            
            
            this.showhideelement(menu_type);
            this._setExternal();
            if (cms_page) {
            jQuery('select#cmspage_identifier').find('option[value='+cms_page+']').prop('selected', true);
            }
            if (static_block) {
            jQuery('select#staticblock_identifier').find('option[value='+static_block+']').prop('selected', true);
            }
            
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
            
            if (image=="") {
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
            for (var count=0; count<storeArray.length; count++) {
                for (var i=0; i<select.options.length; i++) {
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
            //jQuery('#label_show_hide').val(label_show_hide);
            jQuery('#label_title').val(label_title);
            jQuery('#height').val(label_height);
            //jQuery('#width').val(label_width);
            jQuery('#label_text_bg_color').val(label_bg_color);
            jQuery('#label_text_color').val(label_color);
            if ((label_bg_color==null)) {
            } else {
                jQuery('#label_text_bg_color').css("background-color", 'rgb(' + this._hexToRgb(label_bg_color).r + ',' + this._hexToRgb(label_bg_color).g + ',' + this._hexToRgb(label_bg_color).b +'');
                this.fillJsColor('label_text_bg_color',label_bg_color);
            }
            if ((label_color==null)) {
            } else {
                jQuery('#label_text_color').css("background-color", 'rgb(' + this._hexToRgb(label_color).r + ',' + this._hexToRgb(label_color).g + ',' + this._hexToRgb(label_color).b +'');
                this.fillJsColor('label_text_color',label_color);
            }

            },
            
    });
    return jQuery.Navigationmenu.menugrid;
    }
);
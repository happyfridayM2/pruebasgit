define(
    [
        'jquery',
        'underscore',
        'mage/translate',
        'mage/template',
        'jquery/colorpicker/js/colorpicker',
        'prototype'
    ], function (jQuery, _, $t, mageTemplate) {
        return function(config) {
                var swatchVisualOption = {
                    rendered: 0,
                    isReadOnly: 1,
                    general: null,
                    template: mageTemplate('#swatch-visual-row-template'),

                    initColorPicker: function () {
                        var element = this,
                            hiddenColorPicker = !jQuery(element).data('colorpickerId');

                        jQuery(this).ColorPicker({

                            /**
                             * ColorPicker onShow action
                             */
                            onShow: function () {
                                var color = jQuery(element).parent().parent().prev().prev('input').val(),
                                    menu = jQuery(this).parents('.swatch_sub-menu_container');
                                if (typeof color == 'undefined') {
                                    color = '#000';
                                }
                                menu.hide();
                                jQuery(element).ColorPickerSetColor(color);
                                jQuery('.swatch_window_unvailable').hide();
                            },

                            onSubmit: function (hsb, hex, rgb, el) {
                                var container = jQuery(el).parent().parent().prev();

                                jQuery(el).ColorPickerHide();
                                container.parent().removeClass('unavailable');
                                container.prev('input').val('#' + hex);
                                container.css('background', '#' + hex);
                                jQuery('#group_visual').val('#'+hex);
                                jQuery('#group_type').val(1);
                            }
                        });

                        if (hiddenColorPicker) {
                            jQuery(this).ColorPickerShow();
                        }
                    },

                    remove: function (event) {
                        var element = $(Event.findElement(event, 'tr')),
                            elementFlags; // !!! Button already have table parent in safari

                        // Safari workaround
                        element.ancestors().each(function (parentItem) {
                            if (parentItem.hasClassName('option-row')) {
                                element = parentItem;
                                throw $break;
                            } else if (parentItem.hasClassName('box')) {
                                throw $break;
                            }
                        });

                        if (element) {
                            elementFlags = element.getElementsByClassName('delete-flag');

                            if (elementFlags[0]) {
                                elementFlags[0].value = 1;
                            }

                            element.addClassName('no-display');
                            element.addClassName('template');
                            element.hide();
                        }
                    },
                    render: function (element) {
                        Element.insert(this.general, {after: element});
                    },

                    renderWithDelay: function (data) {
                        var element;
                        element = this.template({
                            data: data
                        });
                        this.render(element);
                        jQuery('body').trigger('processStop');
                        return true;
                    }
                };

            if ($('group_visual')) {
                var general  = $('group_visual');
                var type  = $('group_type');
                swatchVisualOption.general = general;
                if (swatchVisualOption.rendered) {
                    return false;
                }
                jQuery('body').trigger('processStart');
                var visual = '';
                if (type.value == 1) {
                    visual = 'background:' + general.value;
                }
                if (type.value == 2) {
                    console.log(config.mediaHelper);
                   var fullMediaUrl = config.mediaHelper + general.value;
                    visual = 'background-image:url(' + fullMediaUrl + ');';
                }
                var data = {swatch0: visual, display: 'block'};
                if (data.swatch0.length) {
                    data.display = 'none';
                }
                swatchVisualOption.renderWithDelay(data);
                general.hide();
                jQuery('#group_visual').parent().on(
                    'click',
                    '.colorpicker_handler',
                    swatchVisualOption.initColorPicker
                );
            }

            jQuery('body').on('click', function (event) {
                var element = jQuery(event.target);

                if (
                    element.parents('.swatch_sub-menu_container').length === 1 ||
                    element.next('div.swatch_sub-menu_container').length === 1
                ) {
                    return true;
                }
                jQuery('.swatch_sub-menu_container').hide();
            });

            window.swatchVisualOption = swatchVisualOption;

            jQuery(function ($) {

                var swatchComponents = {

                    wrapper: null,

                    iframe: null,

                    form: null,

                    inputFile: null,

                    create: function () {

                        this.wrapper = $('<div>').css({
                            display: 'none'
                        }).appendTo($('body'));

                        this.iframe = $('<iframe />', {
                            id:  'upload_iframe',
                            name: 'upload_iframe'
                        }).appendTo(this.wrapper);

                        this.form = $('<form />', {
                            id: 'swatch_form_image_upload',
                            name: 'swatch_form_image_upload',
                            target: 'upload_iframe',
                            method: 'post',
                            enctype: 'multipart/form-data',
                            class: 'ignore-validate',
                            action: config.uploadActionUrl
                        }).appendTo(this.wrapper);

                        this.inputFile = $('<input />', {
                            type: 'file',
                            name: 'datafile',
                            class: 'swatch_option_file'
                        }).appendTo(this.form);

                        $('<input />', {
                            type: 'hidden',
                            name: 'form_key',
                            value: FORM_KEY
                        }).appendTo(this.form);
                    }
                };

                swatchComponents.create();

                swatchComponents.inputFile.change(function () {
                    var container = $('#' + $(this).attr('data-called-by')).parents().eq(2).children('.swatch_window'),
                        iframeHandler = function () {
                            var imageParams = $.parseJSON($(this).contents().find('body').html()),
                                fullMediaUrl = imageParams['swatch_path'] + imageParams['file_path'];

                            jQuery('#group_visual').val(imageParams['file_path']);
                            jQuery('.swatch_window').css({'background-image': 'url(' + fullMediaUrl + ')'});
                            jQuery('.swatch_window_unvailable').hide();
                            jQuery('#group_type').val(2);
                        };

                    swatchComponents.iframe.off('load');
                    swatchComponents.iframe.load(iframeHandler);
                    swatchComponents.form.submit();
                    $(this).val('');
                });

                $(document).on('click', '.btn_choose_file_upload', function () {
                    swatchComponents.inputFile.attr('data-called-by', $(this).attr('id'));
                    swatchComponents.inputFile.click();
                });

                $(document).on('click', '.btn_remove_swatch', function () {
                    var optionPanel = $(this).parents().eq(2);
                    optionPanel.children('input').val('');
                    optionPanel.children('.swatch_window').css('background', '');

                    jQuery('.swatch_window_unvailable').show();
                    jQuery('#group_visual').val('');
                    jQuery('#group_type').val(0);
                    jQuery('.swatch_sub-menu_container').hide();
                });

                /**
                 * Toggle color upload chooser
                 */
                $(document).on('click', '.swatch_window', function () {
                    $(this).next('div').toggle();
                });
            });
        };
    });

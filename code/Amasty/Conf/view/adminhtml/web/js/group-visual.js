define(
[
    'jquery',
    'underscore',
    'mage/translate',
    'mage/template',
    'jquery/colorpicker/js/colorpicker',
    'prototype',
    'jquery/ui'
], function ($j, _, $t, mageTemplate) {
    return function(config) {
        var swatchVisualOption = {
            rendered: 0,
            isReadOnly: 1,
            general: null,
            template: mageTemplate('#swatch-visual-row-template'),
            wrapper: null,
            iframe: null,
            form: null,
            inputFile: null,

            init: function () {
                var general  = $j('#group_visual').first(),
                    type  = $j('#group_type').first();

                /* swatches have already rendered */
                if (swatchVisualOption.rendered || !general) {
                    return false;
                }

                this.setGeneral(general);
                $j('body').trigger('processStart');

                var display = 'none';
                switch (type.val()) {
                    case '2':
                        var fullMediaUrl = config.mediaHelper + general.val();
                        var visual = 'background-image:url(' + fullMediaUrl + ');background-size:cover';
                        break;
                    case '1':
                        visual = 'background:' + general.val();
                        break;
                    default:
                        display = 'block';
                        visual = '';
                }

                this.renderWithDelay({swatch: visual, display: display});
                var self = this;
                $j(general).hide().parent().on(
                    'click',
                    '.colorpicker_handler',
                    self.initColorPicker
                );

                $j('body').on('click', function (event) {
                    var element = $j(event.target);
                    if (
                        element.parents('.swatch_sub-menu_container').length === 1 ||
                        element.next('div.swatch_sub-menu_container').length === 1
                    ) {
                        return true;
                    }
                    $j('.swatch_sub-menu_container').hide();
                });

                this.createSwatchComponents();
                this.observeSwatchComponents();
            },

            setColorForContainer: function(element) {
                var container = $j('#swatch_window_option');
                var hex = element.select('.colorpicker_hex input').first().value;

                $j(element).ColorPickerHide();
                container.parent().removeClass('unavailable');
                container.prev('input').val('#' + hex);
                container.css('background', '#' + hex);
                $j('#group_visual').val('#'+hex);
                $j('#group_type').val(1);
            },

            initColorPicker: function () {
                var element = this,
                    hiddenColorPicker = !$j(element).data('colorpickerId');

                $j(this).ColorPicker({
                    onShow: function () {
                        var color = swatchVisualOption.general.val(),
                            menu = $j(this).parents('.swatch_sub-menu_container');

                        if (typeof color === 'undefined') {
                            color = '#000';
                        }
                        menu.hide();
                        $j(element).ColorPickerSetColor(color);
                        $j('.swatch_window_unvailable').hide();
                    },

                    onSubmit: function (hsb, hex, rgb, element) {
                        swatchVisualOption.setColorForContainer(element);
                    },

                    onHide: function (element) {
                        swatchVisualOption.setColorForContainer(element);
                    }
                });

                if (hiddenColorPicker) {
                    $j(this).ColorPickerShow();
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
                this.general.after(element);
            },

            setGeneral: function (general) {
                this.general = general;
            },

            renderWithDelay: function (data) {
                var element = this.template({
                    data: data
                });

                this.render(element);
                $j('body').trigger('processStop');

                return true;
            },

            createSwatchComponents: function () {
                this.wrapper = $j('<div>').css({
                    display: 'none'
                }).appendTo($j('body'));

                this.iframe = $j('<iframe />', {
                    id:  'upload_iframe',
                    name: 'upload_iframe'
                }).appendTo(this.wrapper);

                this.form = $j('<form />', {
                    id: 'swatch_form_image_upload',
                    name: 'swatch_form_image_upload',
                    target: 'upload_iframe',
                    method: 'post',
                    enctype: 'multipart/form-data',
                    class: 'ignore-validate',
                    action: config.uploadActionUrl
                }).appendTo(this.wrapper);

                this.inputFile = $j('<input />', {
                    type: 'file',
                    name: 'datafile',
                    class: 'swatch_option_file'
                }).appendTo(this.form);

                $j('<input />', {
                    type: 'hidden',
                    name: 'form_key',
                    value: FORM_KEY
                }).appendTo(this.form);
            },

            observeSwatchComponents: function () {
                var self = this;
                this.inputFile.change(function () {
                    var container = $j('#' + $j(this).attr('data-called-by')).parents().eq(2).children('.swatch_window'),
                        iframeHandler = function () {
                            var imageParams = $j.parseJSON($j(this).contents().find('body').html()),
                                fullMediaUrl = imageParams['swatch_path'] + imageParams['file_path'];
                            //  $j('#group_visual').val('background-image: url(' + fullMediaUrl + ');background-size:cover');
                            $j('#group_visual').val(imageParams['file_path']);
                            $j('.swatch_window').css({
                                'background-image': 'url(' + fullMediaUrl + ')',
                                'background-size': 'cover'
                            });
                            $j('.swatch_window_unvailable').hide();
                            $j('#group_type').val(2);
                        };

                    self.iframe.off('load').load(iframeHandler);
                    self.form.submit();
                    $j(this).val('');
                });

                $j(document).on('click', '.btn_choose_file_upload', function () {
                    self.inputFile.attr('data-called-by', $j(this).attr('id'));
                    self.inputFile.click();
                });

                $j(document).on('click', '.btn_remove_swatch', function () {
                    var optionPanel = $j(this).parents().eq(2);
                    optionPanel.children('input').val('');
                    optionPanel.children('.swatch_window').css('background', '');

                    $j('.swatch_window_unvailable').show();
                    $j('#group_visual').val('');
                    $j('#group_type').val(0);
                    $j('.swatch_sub-menu_container').hide();
                });

                /**
                 * Toggle color upload chooser
                 */
                $j(document).on('click', '.swatch_window', function () {
                    $j(this).next('div').toggle();
                });
            }
        };

        swatchVisualOption.init();
        window.swatchVisualOption = swatchVisualOption;
    };
});

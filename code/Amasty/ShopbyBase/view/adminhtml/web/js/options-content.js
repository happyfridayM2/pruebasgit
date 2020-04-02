define([
    'jquery',
    'Magento_Ui/js/modal/alert'
], function ($, alert) {
    'use strict';

    return function (config) {
        var id = '';
        switch ($("#frontend_input").val()) {
            case 'swatch_visual' :
                id = 'swatch-visual-options-panel';
                break;
            case 'swatch_text' :
                id = 'swatch-text-options-panel';
                break;
            default :
                id = 'manage-options-panel';
        }

        var addButtonsFunction = function () {
            $('#' + id + ' td.col-delete').each(function () {
                if (!$(this).find('.amshopby-button-option').length) {
                    var optionId = $(this).attr('id').replace('delete_button_swatch_container_', '');
                    optionId = optionId.replace('delete_button_container_', '');

                    $(this).prepend('<button id="settings_button_' + optionId
                        + '" class="amshopby-button-option action-settings" data-option-id="'
                        + optionId + '"><span>' + config.buttonText + '</span></button>'
                    );
                }
            });

            $('.entry-edit').keydown(function (event) {
                if (event.keyCode === 13) {
                    event.preventDefault();
                    return false;
                }
            });

            $('.amshopby-button-option').on('click', function (e) {
                var $button = $(this),
                    optionId = $button.data('option-id'),
                    url = config.url.replace('__option_id__', optionId),
                    modalListSettings = alert({
                        title: config.modalHeadText,
                        content: $('#loader-spinner-html').html(),
                        actions: {
                            always: function() {
                                if(typeof tinyMceEditors != 'undefined'
                                    && tinyMceEditors.get('description') != undefined
                                ) {
                                    tinyMceEditors.get('description').turnOff();
                                }
                            }
                        },
                        buttons: [
                            {
                                text: 'Save',
                                class: 'action-primary action-accept',
                                click: function () {
                                    $("#edit_options_form").submit();
                                }
                            },
                            {
                                text: 'Cancel',
                                class: 'action-secondary',
                                click: function () {
                                    this.closeModal(true);
                                }
                            }
                        ]
                    });

                /*fix magento 2.2.6+ popup width*/
                $(modalListSettings).parents('.modal-inner-wrap').addClass('am-popup');

                var functionUpdateModal = function (data) {
                    $(modalListSettings).html(data);
                    $(modalListSettings).trigger('contentUpdated');

                    $('#preview_form').submit(function (e) {
                        var formObj = $(this);
                        $("#edit_options_form").append($('#loader-spinner-html').html());
                        var formURL = formObj.attr("action");
                        var formData = formObj.serialize();

                        $.ajax({
                            url: formURL,
                            type: 'GET',
                            data: formData,
                            cache: false,
                            processData: false,
                            success: functionUpdateModal
                        });

                        e.preventDefault();
                        e.stopPropagation();
                    });

                    $("#edit_options_form").submit(function (e) {
                        var formObj = $(this),
                            formURL = formObj.attr("action"),
                            formData = new FormData(this);

                        $("#edit_options_form").append($('#loader-spinner-html').html());
                        $.each(formObj.serializeArray(), function (index, field) {
                            formData.append(field.name, field.value);
                        });

                        $.ajax({
                            url: formURL,
                            type: 'POST',
                            data: formData,
                            mimeType: "multipart/form-data",
                            contentType: false,
                            dataType: "html",
                            cache: false,
                            processData: false,
                            success: functionUpdateModal
                        });
                        e.preventDefault();
                        e.stopPropagation();
                    });

                };

                $.ajax({
                    url: url,
                    dataType: "html",
                    data: {form_key: FORM_KEY},
                    success: functionUpdateModal
                });
                e.stopPropagation();
                e.preventDefault();
            });
        };

        if (config.canConfigureOptions) {
            $('body').on('processStop', addButtonsFunction);
        }
    }
});

/**
 * Copyright © 2019 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */
define(["jquery", "Magento_Ui/js/modal/confirm", "jquery/ui", "Magento_Ui/js/modal/modal"], function ($, confirm) {
    'use strict';
    return {
        generate: function (url) {
            confirm({
                title: "Generate a profile",
                content: "Generate a profile can take a while. Are you sure you want to generate it now ?",
                actions: {
                    confirm: function () {
                        $('.col-action select.admin__control-select').val("");
                        $.ajax({
                            url: url,
                            type: 'POST',
                            data: {url: url},
                            showLoader: false,
                            success: function () {
                                location.reload();
                            }
                        });
                    },
                    cancel: function () {
                        $('.col-action select.admin__control-select').val("");
                    }
                }
            });
        },
        delete: function (url) {
            confirm({
                title: "Delete a profile",
                content: "Are you sure you want to delete this profile ?",
                actions: {
                    confirm: function () {
                        document.location.href = url;
                    },
                    cancel: function () {
                        $('.col-action select.admin__control-select').val("");
                    }
                }
            });
        },
        generateFromEdit: function () {
            confirm({
                title: "Generate a profile",
                content: "Generate a profile can take a while. Are you sure you want to generate it now ?",
                actions: {
                    confirm: function () {
                        $('#generate_i').val('1');
                        $('#edit_form').submit();
                    }
                }
            });
        }

    };
});
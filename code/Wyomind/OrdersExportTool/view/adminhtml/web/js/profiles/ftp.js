/**
 * Copyright © 2018 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

define(["jquery","Magento_Ui/js/modal/alert"], function ($,alert) {
    "use strict";
    return {
        test: function (url) {

            var prefix = "";
            if (typeof arguments[1] === "undefined") {

                prefix = "";
            } else {
                prefix = arguments[1] + "_";

            }

            $.ajax({
                url: url,
                data: {
                    ftp_host: $('#' + prefix + 'ftp_host').val(),
                    ftp_port: $('#' + prefix + 'ftp_port').val(),
                    ftp_login: $('#' + prefix + 'ftp_login').val(),
                    ftp_password: $('#' + prefix + 'ftp_password').val(),
                    ftp_dir: $('#' + prefix + 'ftp_dir').val(),
                    ftp_active: $('#' + prefix + 'ftp_active').val(),
                    use_sftp: $('#' + prefix + 'use_sftp').val(),
                    file_path: $('#' + prefix + 'file_path').val()
                },
                type: 'POST',
                showLoader: true,
                success: function (data) {
                    alert({
                        title: $.mage.__('Ftp settings'),
                        content: data,
                        actions: {
                            always: function(){}
                        }
                    });
                }
            });
        }
    };
});
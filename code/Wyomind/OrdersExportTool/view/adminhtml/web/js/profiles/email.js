/**
 * Copyright © 2018 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

/*
 * Copyright © 2019 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

define(["jquery", "Magento_Ui/js/modal/alert"], function ($, alert) {
    "use strict";
    return {
        test: function (url) {


            $.ajax({
                url: url,
                data: {
                    mail_recipients: $('#mail_recipients').val(),
                    mail_subject: $('#mail_subject').val(),
                    mail_message: $('#mail_message').val(),
                    mail_sender: $('#mail_sender').val(),

                },
                type: 'POST',
                showLoader: true,
                success: function (data) {
                    alert({
                        title: $.mage.__('Email settings'),
                        content: data,
                        actions: {
                            always: function () {
                            }
                        }
                    });
                }
            });
        }
    };
});
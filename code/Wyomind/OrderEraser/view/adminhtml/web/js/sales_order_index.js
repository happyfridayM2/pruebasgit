/**
 * Copyright © 2017 Wyomind All rights reserved.
 * See LICENSE.txt for license details.
 */
require(["jquery", "Magento_Ui/js/modal/confirm"], function ($, confirm) {
    $(function () {
        OrderEraser = {
            delete: function (url) {
                confirm({
                    title: "Delete order",
                    content: "Delete this order ?",
                    actions: {
                        confirm: function () {
                            document.location.href = url;
                        }
                    }
                });
            }
        };
    });
});
    
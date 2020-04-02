/**
 * Copyright © 2018 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

/*
 * Copyright © 2019 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

define(["jquery"], function ($) {
    "use strict";
    return {
        init: function () {
            var data = new Array();
            $('.updater').each(
                function (i, u) {
                    var profile = {
                        id: $(u).attr('data-id'),
                        module: $(u).attr('data-module'),
                        cron: $(u).attr('data-cron'),
                        field: $(u).attr('data-field')
                    };
                    data.push(profile);
                }
            );
            $.ajax({
                url: updater_url,
                type: 'POST',
                showLoader: false,
                data: {data: data},
                async: true,
                success: function (response) {

                    if (typeof response === "object") {
                        response.each(function (r) {
                            $(".updater[data-id='" + r.id+"']").parent("TD").html(r.content)
                        });
                    }
                    require(["wyomind_core_updater"], function (updater) {
                        setTimeout(function () {
                            updater.init();
                        }, 1000);
                    });
                },

            });
        }
    };
});
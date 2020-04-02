define(["jquery"], function ($) {
    'use strict';
    return {
        waitFor: function (elt, callback) {
            var initializer = null;
            initializer = setInterval(function () {
                if ($(elt).length > 0) {
                    setTimeout(callback, 500);
                    clearInterval(initializer);
                }
            }, 200);
        },
        /**
         * Load the selected days and hours
         */
        loadExpr: function () {
            this.waitFor("#scheduled_task", function () {
                if ($('#scheduled_task').val() === "") {
                    $('#scheduled_task').val("{}");
                }
                var val = $.parseJSON($('#scheduled_task').val());
                if (val !== null) {
                    if (val.days)
                        val.days.each(function (elt) {
                            $('#d-' + elt).parent().addClass('selected');
                            $('#d-' + elt).prop('checked', true);
                        });
                    if (val.hours)
                        val.hours.each(function (elt) {
                            var hour = elt.replace(':', '');
                            $('#h-' + hour).parent().addClass('selected');
                            $('#h-' + hour).prop('checked', true);
                        });
                }
            });
        },
        /**
         * Update the json representation of the cron schedule
         */
        updateExpr: function () {
            var days = new Array();
            var hours = new Array();
            $('.cron-box.day').each(function () {
                if ($(this).prop('checked') === true) {
                    days.push($(this).attr('value'));
                }
            });
            $('.cron-box.hour').each(function () {
                if ($(this).prop('checked') === true) {
                    hours.push($(this).attr('value'));
                }
            });

            $('#scheduled_task').val(JSON.stringify({days: days, hours: hours}));
        }
    };
});
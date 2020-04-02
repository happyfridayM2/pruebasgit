define([
        'ko',
        'jquery',
        'Magento_Ui/js/form/element/date'
    ], function (
        ko,
        $,
        AbstractField
    ) {
        'use strict';
        return AbstractField.extend({
            defaults: {
                amcheckout_days: []
            },

            initConfig: function () {
                this._super();

                this.options.minDate = new Date();
                this.options.showOn = 'both';
                if (this.amcheckout_days.length > 0) {
                    this.options.beforeShowDay = this.restrictDates.bind(this);
                }

                return this;
            },

            restrictDates: function (d) {
                return [$.inArray(d.getDay(), this.amcheckout_days) != -1, ""];
            }
        });
    }
);

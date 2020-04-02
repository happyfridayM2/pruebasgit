define([
    'Magento_Ui/js/form/components/insert-form',
    'jquery'
], function (Insert, $) {
    'use strict';

    return Insert.extend({

        requestData: function (params, ajaxSettings) {
            var newData = window.mgzlinkbuilder ? window.mgzlinkbuilder : {};
            params['source'] = newData;
            return this._super(params, ajaxSettings);
        },

        updateData: function () {
            if (this.externalSource()) {
                var newData = window.mgzlinkbuilder ? window.mgzlinkbuilder : {};
                this.externalSource().set('data', newData);
            }
        }
    });
});
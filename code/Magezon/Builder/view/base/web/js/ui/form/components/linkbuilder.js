define([
    'underscore',
    'Magento_Ui/js/form/provider',
    'jquery'
], function (_, Element, $) {
    'use strict';

    return Element.extend({

        save: function (options) {
            var data   = this.get('data');
            var result = "";

            //https://github.com/kvz/locutus/blob/master/src/php/strings/addcslashes.js
            function addslashes(str) {
                return (str + '')
                .replace(/[\\"']/g, '\\$&')
                .replace(/\u0000/g, '\\0')
                .replace(/{/g, '\\{')
                .replace(/}/g, '\\}');
            }

            _.each(data, function (value, key) {
                if (key != 'target') {
                    if (result) result += ' ';
                    result += key + '="' + addslashes(value) + '"';
                }
            });
            result = '{{mgzlink ' + result + ' }}';
            $('#' + window.mgzlinkbuilder.target).val(result).trigger('change');
            $('.linkbuilder_form_linkbuilder_form_link_modal .action-close').trigger('click');
            return this;
        }

    });
});
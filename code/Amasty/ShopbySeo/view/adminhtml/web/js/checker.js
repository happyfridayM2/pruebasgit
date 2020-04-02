define([
    'jquery',
    'Magento_Ui/js/modal/alert',
    'domReady'
], function ($, modalConfirm) {
    'use strict';

    $.widget('mage.amConfigChecker', {
        options: {
            contentSelector: '#am_checker_message',
            fieldsSelector: '#amasty_shopby_seo_url_special_char, #amasty_shopby_seo_url_option_separator'
        },

        _create: function () {
            $(this.options.fieldsSelector).on('change', function (e) {
                var specialChar = $('#amasty_shopby_seo_url_special_char').val(),
                    separator = $('#amasty_shopby_seo_url_option_separator').val();
                if (specialChar == separator) {
                    modalConfirm({
                        title: $.mage.__('Attention'),
                        content: $(this.options.contentSelector).html(),
                    });
                }
            }.bind(this));
        }

    });

    return $.mage.amConfigChecker;
});

/**
 * @author    Amasty Team
 * @copyright Copyright (c) Amasty Ltd. ( http://www.amasty.com/ )
 * @package   Amasty_Shopby
 */
define([
    "jquery"
], function ($) {
    'use strict';

    $.widget('mage.amSearchOptions', {
        options: {
            itemsSelector: ''
        },

        previousSearch: '',

        _create: function () {
            var self = this,
                $items = $(this.options.itemsSelector + ' .item');
            $(self.element).keyup(function () {
                self.search(this.value, $items);
            });
        },

        search: function (text, $items) {
            var searchText = text.toLowerCase();

            if (searchText == this.previousSearch) {
                return;
            }
            this.previousSearch = searchText;

            $items.each(function (key, item) {
                var itemLabel = $(item).find('.option_value'),
                    val = $(itemLabel).attr('data-label').toLowerCase();

                if ($(itemLabel).attr('data-label')) {
                    if (!val || val.indexOf(searchText) > -1) {
                        item.show();
                    } else {
                        item.hide();
                    }
                }
            });
        }
    });
});

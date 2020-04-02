define([
    "jquery"
], function ($) {
    'use strict';

    $.widget('mage.amShopbySwatchesChoose', {
        options: {
            listSwatches: {}
        },

        _create: function () {
            var self = this;
            setTimeout(function () {
                if (self.options.listSwatches) {
                    $.each(self.element.find('.swatch-attribute-options'), function (elementId, element) {
                        $.each(self.options.listSwatches, function (attributeCode, optionId) {
                            var swatch = jQuery(element).find('[option-id="' + optionId + '"]');
                            if (swatch.length !== 0) {
                                swatch.trigger('click');
                                return false;
                            }
                        })
                    });
                }
            }, 100);
        }
    });
});

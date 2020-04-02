define([
    'jquery',
    'Magento_Ui/js/form/element/abstract'
], function($, Abstract) {

    return Abstract.extend({
        defaults: {
            jQuery: $
        },

        preselectProduct: function (index, target) {
            var sku = null,
                configurableContainer = this.containers.first().containers.first(),
                preselectContainer = this.jQuery('input[name="product[simple_preselect]"]');
            if (preselectContainer.length == 0) {
                return;
            }
            if (configurableContainer
                && typeof configurableContainer.getUnionInsertData == 'function'
            ) {
                var product = configurableContainer.getUnionInsertData()[index];
                if (product) {
                    sku = product.sku;
                }
            }
            if (!sku) {
                sku = this.jQuery(target).closest('.data-row').find('span[data-index="sku_text"]').text();
            }

            preselectContainer.val(sku);
            preselectContainer.trigger('change');
        }
    });
});

define([
    'jquery',
    'magento-variations'
], function($, variations) {

    /* fix magento bug( serialize deleted array after error validation with ajax ) */
    return variations.extend({

        serializeData: function () {
            if (this.source.data['configurable-matrix']) {
                this.source.data['configurable-matrix-serialized'] =
                    JSON.stringify(this.source.data['configurable-matrix']);

                delete this.source.data['configurable-matrix'];
            }

            if (this.source.data['associated_product_ids']) {
                this.source.data['associated_product_ids_serialized'] =
                    JSON.stringify(this.source.data['associated_product_ids']);

                delete this.source.data['associated_product_ids'];
            }
        }

    });
});

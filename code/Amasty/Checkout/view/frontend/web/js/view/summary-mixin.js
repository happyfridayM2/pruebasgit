define([], function () {
    'use strict';

    return function (Component) {
        return Component.extend({
            getNameSummary: function () {
                return window.checkoutConfig.quoteData.block_info.block_order_summary['value'];
            }
        });
    }
});

define([
    'underscore',
    'mage/utils/wrapper',
    'Magento_Checkout/js/model/quote'
], function (_, wrapper, quote) {
    'use strict';

    /**
     * Changing shipping address only if they are not equal, because core of knockout can't compare objects
     * It's necessary for avoid a subscribers update
     */
    return function (selectShippingAddressAction) {
        return wrapper.wrap(selectShippingAddressAction, function (original, shippingAddress) {
            if (!_.isEqual(shippingAddress, quote.shippingAddress())) {
                original(shippingAddress);
            }
        });
    };
});

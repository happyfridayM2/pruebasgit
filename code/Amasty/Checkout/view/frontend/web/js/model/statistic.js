define([
    'ko',
    'jquery',
    'uiComponent',
    'mage/storage',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/url-builder',
    'Magento_Customer/js/model/customer'
], function (ko, $, Component, storage, quote, urlBuilder, customer) {
    'use strict';

    return Component.extend({

        initialize: function () {
            this._super();
            var quoteId = quote.getQuoteId(),
                url = '',
                paymentMethod;

            quote.paymentMethod.subscribe(function (method) {
                if (method) {
                    paymentMethod = method.method;
                }
            });
            
            if (customer.isLoggedIn()) {
                url = urlBuilder.createUrl('/checkout/saveInsertedInfo', {});
            } else {
                url = urlBuilder.createUrl('/checkout/:cartId/saveInsertedInfo', {cartId: quoteId});
            }

            $(window).on('beforeunload', function () {
                var cashStorage = JSON.parse(window.localStorage.getItem('mage-cache-storage'));

                if (cashStorage && cashStorage.hasOwnProperty('checkout-data')) {
                    var checkoutData = cashStorage['checkout-data'],
                        request = {quote_id : quoteId};

                    if (customer.isLoggedIn() && !checkoutData.newCustomerShippingAddress) {
                        request.shippingAddressFromData = null;
                    } else {
                        request.shippingAddressFromData = checkoutData.newCustomerShippingAddress
                            ? checkoutData.newCustomerShippingAddress
                            : checkoutData.shippingAddressFromData;
                    }

                    request.newCustomerBillingAddress = checkoutData.newCustomerBillingAddress;

                    if (
                        checkoutData.selectedPaymentMethod
                        && checkoutData.selectedPaymentMethod.includes('braintree_cc_vault_')
                    ) {
                        checkoutData.selectedPaymentMethod = 'braintree_cc_vault';
                    }

                    request.selectedPaymentMethod = checkoutData.selectedPaymentMethod;
                    request.selectedShippingRate = checkoutData.selectedShippingRate;

                    if (customer.isLoggedIn()) {
                        request.validatedEmailValue = null;
                    } else {
                        request.validatedEmailValue = checkoutData.validatedEmailValue;
                    }

                    if (request.selectedPaymentMethod === null) {
                        request.selectedPaymentMethod = paymentMethod;
                    }

                    storage.post(
                        url,
                        JSON.stringify(request),
                        false
                    );
                }
            }.bind(this));

            return this;
        }
    });
});

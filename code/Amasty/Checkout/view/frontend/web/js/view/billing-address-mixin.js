/**
 * Billing address view mixin for store flag is billing form in edit mode (visible)
 */
define([
    'Magento_Checkout/js/model/quote',
    'Amasty_Checkout/js/model/address-form-state'
], function (quote, formService) {
    'use strict';

    return function (billingAddress) {
        return billingAddress.extend({
            initObservable: function () {
                this._super();

                this.isAddressSameAsShipping.subscribe(formService.updateBillingFormStates, formService);
                this.isAddressDetailsVisible.subscribe(formService.updateBillingFormStates, formService);

                if (window.checkoutConfig.displayBillingOnPaymentMethod) {
                    quote.paymentMethod.subscribe(formService.updateBillingFormStates, formService);
                }

                formService.updateBillingFormStates();

                return this;
            }
        });
    };
});

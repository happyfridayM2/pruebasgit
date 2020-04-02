define(
    [
        'Amasty_Checkout/js/model/resource-url-manager',
        'Magento_Checkout/js/model/totals',
        'Magento_Checkout/js/model/quote',
        'mage/storage',
        'Magento_Checkout/js/model/error-processor',
        'Magento_Checkout/js/model/shipping-rate-registry',
        'Magento_Checkout/js/model/shipping-rate-processor/new-address',
        'Magento_Checkout/js/model/shipping-rate-processor/customer-address',
        'Magento_Checkout/js/model/payment-service',
        'Magento_Checkout/js/model/payment/method-converter',
        'Magento_Customer/js/customer-data',
        'Amasty_Checkout/js/action/update-items-content',
    ],
    function (
        resourceUrlManager,
        totals,
        quote,
        storage,
        errorProcessor,
        rateRegistry,
        defaultProcessor,
        customerAddressProcessor,
        paymentService,
        methodConverter,
        customerData,
        updateItemsContent
    ) {
        "use strict";


        return function (itemId, formData) {
            if (totals.isLoading())
                return;

            totals.isLoading(true);
            var serviceUrl = resourceUrlManager.getUrlForUpdateItem(quote);

            storage.post(
                serviceUrl, JSON.stringify({
                    itemId: itemId,
                    formData: formData
                }), false
            ).done(
                function (result) {
                    if (!result) {
                        window.location.reload();
                    }

                    rateRegistry.set(quote.shippingAddress().getCacheKey(), null);
                    var type = quote.shippingAddress().getType();

                    if (type === 'customer-address') {
                        customerAddressProcessor.getRates(quote.shippingAddress());
                    } else {
                        defaultProcessor.getRates(quote.shippingAddress());
                    }

                    paymentService.setPaymentMethods(methodConverter(result.payment));
                    customerData.reload(['cart']);
                    totals.isLoading(false);
                    updateItemsContent(result.totals);
                }
            ).fail(
                function (response) {
                    errorProcessor.process(response);
                    totals.isLoading(false);
                }
            );
        }
    }
);

define(
    [
        'jquery',
        'underscore',
        'uiComponent',
        'ko',
        'uiRegistry',
        'Magento_Checkout/js/action/select-billing-address',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/payment/additional-validators',
        'Magento_Checkout/js/model/checkout-data-resolver',
        'Amasty_Checkout/js/action/is-equal-ignore-functions',
        'Amasty_Checkout/js/model/payment-validators/shipping-validator',
        'Amasty_Checkout/js/model/address-form-state'
    ],
    function (
        $,
        _,
        Component,
        ko,
        registry,
        selectBillingAddress,
        quote,
        paymentValidatorRegistry,
        checkoutDataResolver,
        isEqualIgnoreFunctions,
        shippingValidator,
        addressFormState
    ) {
        'use strict';

        return Component.extend({
            defaults: {
                mappingBlockName: {
                    block_shipping_address: 'checkout.steps.shipping-step.shippingAddress',
                    block_shipping_method: 'checkout.steps.shipping-step.shippingAddress',
                    block_delivery: 'checkout.steps.shipping-step.amcheckout-delivery-date',
                    block_payment_method: 'billing-step',
                    block_order_summary: 'sidebar'
                },
                orderedBlocks: {}
            },

            /** @inheritdoc */
            initialize: function () {
                this._super();
                var blockInfo = window.checkoutConfig.quoteData.block_info;

                this.replaceEqualityComparer();

                $.each(blockInfo, function (key, item) {
                    var sortOrder = 0;

                    if (item.hasOwnProperty('sort_order')) {
                        sortOrder = item.sort_order;
                    }

                    while (this.orderedBlocks.hasOwnProperty(sortOrder)) {
                        sortOrder++;
                    }

                    this.orderedBlocks[sortOrder] = {
                        blockCode: this.mappingBlockName[key],
                        blockKey: key
                    };
                }.bind(this));
            },

            getSortedBlock: function(index) {
                var orderedBlock = this.orderedBlocks[index];

                if (orderedBlock.blockCode === 'billing-step') {
                    return this.getChild('steps').getChild('billing-step');
                } else if (orderedBlock.blockCode === 'sidebar') {
                    return this.getChild('sidebar');
                }

                var requestComponent = this.requestComponent(orderedBlock.blockCode);

                if (orderedBlock.blockKey === 'block_shipping_address' && requestComponent()) {
                    requestComponent().template = 'Amasty_Checkout/onepage/shipping/address';
                } else if (orderedBlock.blockKey === 'block_shipping_method' && requestComponent()) {
                    requestComponent().template = 'Amasty_Checkout/onepage/shipping/methods';
                }

                return requestComponent;
            },

            getBlockIndex: function (index) {
                if (this.orderedBlocks[index].blockCode === 'sidebar') {
                    return '-summary';
                }
            },

            initObservable: function () {
                this._super().observe({
                    isAmazonLoggedIn: null
                });

                if (!quote.isVirtual()) {
                    quote.shippingAddress.subscribe(this.shippingAddressObserver.bind(this));
                    paymentValidatorRegistry.registerValidator(shippingValidator);
                }

                registry.get('checkout.steps.billing-step.payment', function (component) {
                    //initialize payment information
                    component.isVisible(true);
                    component.navigate();
                });

                return this;
            },

            shippingAddressObserver: function (address) {
                if (!address) {
                    return;
                }

                this.isAccountLoggedInAmazon();

                this.setShippingToBilling(address);
            },

            /**
             * fix default "My billing and shipping address are the same" checkbox behaviour
             *
             * @param {object|null} address
             */
            setShippingToBilling: function (address) {
                if (!address) {
                    return;
                }

                if (!address.canUseForBilling()) {
                    checkoutDataResolver.resolveBillingAddress();

                    return;
                }

                if (_.isNull(address.street) || _.isUndefined(address.street)) {
                    // fix: some payments (paypal) takes street.0 without checking
                    address.street = [];
                }

                if (addressFormState.isBillingSameAsShipping()) {
                    selectBillingAddress(address);
                }
            },

            /**
             * Used in templates
             *
             * @param {string} name
             * @returns {observable}
             */
            requestComponent: function (name) {
                var observable = ko.observable();

                registry.get(name, function (summary) {
                    observable(summary);
                }.bind(this));

                return observable;
            },

            /**
             * Set customer Amazon logged in status and hide billing address if customer logged in Amazon
             */
            isAccountLoggedInAmazon: function () {
                if (require.defined('Amazon_Payment/js/model/storage')) {
                    if (this.isAmazonLoggedIn()) {
                        $('.checkout-billing-address').hide();
                    } else {
                        require(['Amazon_Payment/js/model/storage'], function (amazonStorage) {
                            amazonStorage.isAmazonAccountLoggedIn.subscribe(function (isLoggedIn) {
                                this.isAmazonLoggedIn(isLoggedIn);
                            }, this);
                            this.isAmazonLoggedIn(amazonStorage.isAmazonAccountLoggedIn())
                        }.bind(this));
                    }
                }
            },

            /**
             * Main observables equality comparer replacement
             * @returns {void}
             */
            replaceEqualityComparer: function () {
                quote.shippingAddress.equalityComparer = isEqualIgnoreFunctions;
                quote.billingAddress.equalityComparer = isEqualIgnoreFunctions;
                quote.shippingMethod.equalityComparer = isEqualIgnoreFunctions;
                quote.paymentMethod.equalityComparer = isEqualIgnoreFunctions;
            },
        });
    }
);

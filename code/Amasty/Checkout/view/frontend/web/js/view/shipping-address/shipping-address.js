/**
 * @deprecated
 */
define([
        'ko',
        'underscore',
        'Magento_Ui/js/form/form',
        'Amasty_Checkout/js/model/address-form-state',
        'Magento_Customer/js/model/customer',
        'Magento_Customer/js/model/address-list',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/action/create-shipping-address',
        'Magento_Checkout/js/action/select-shipping-address',
        'Magento_Checkout/js/checkout-data',
        'Magento_Customer/js/customer-data',
        'mage/translate',
        'jquery'
    ],
    function (
        ko,
        _,
        Component,
        addressFormState,
        customer,
        addressList,
        quote,
        createShippingAddress,
        selectShippingAddress,
        checkoutData,
        customerData,
        $t
    ) {
        'use strict';

        var lastSelectedShippingAddress = null,
            newAddressOption = {
                /**
                 * Get new address label
                 * @returns {String}
                 */
                getAddressInline: function () {
                    return $t('New Address');
                },
                customerAddressId: null
            },
            countryData = customerData.get('directory-data'),
            addressOptions = addressList().filter(function (address) {
                return address.getType() == 'customer-address'; //eslint-disable-line eqeqeq
            });

        addressOptions.push(newAddressOption);

        return Component.extend({
            defaults: {
                template: 'Amasty_Checkout/shipping-address/shipping-address',

                modules: {
                    shippingAddressComponent: '${ $.parentName }'
                }
            },
            currentShippingAddress: quote.shippingAddress,
            addressOptions: addressOptions,
            customerHasAddresses: addressOptions.length > 1,

            /**
             * @return {exports.initObservable}
             */
            initObservable: function () {
                this._super()
                    .observe({
                        selectedAddress: null,
                        isAddressDetailsVisible: quote.shippingAddress() != null,
                        isAddressFormVisible: !customer.isLoggedIn() || addressOptions.length === 1,
                        saveInAddressBook: 1,
                        isAddressListVisible: null,
                        isNewAddressVisible: null,
                        isVisible: this.customerHasAddresses
                    });

                quote.shippingAddress.subscribe(function (newAddress) {
                    if (newAddress != null && newAddress.saveInAddressBook !== undefined) {
                        this.saveInAddressBook(newAddress.saveInAddressBook);
                        this.isNewAddressVisible(true);
                    } else {
                        this.saveInAddressBook(1);
                    }

                    // eslint-disable-next-line eqeqeq
                    if (!this.selectedAddress() || this.selectedAddress() != newAddressOption) {
                        this.isAddressDetailsVisible(true);
                        this.isAddressListVisible(false);
                    }
                }, this);

                this.isAddressFormVisible.subscribe(this.isShippingFormVisibleUpdate, this);
                this.isAddressListVisible.subscribe(this.isShippingFormVisibleUpdate, this);

                return this;
            },

            isShippingFormVisibleUpdate: function () {
                addressFormState.isShippingFormVisible(
                    this.customerHasAddresses && (this.isAddressFormVisible() || this.isAddressListVisible())
                );
            },

            /**
             * for html option text binding
             *
             * @param {Object} address
             * @return {string}
             */
            addressOptionsText: function (address) {
                return address.getAddressInline();
            },

            /**
             * Update address action
             */
            updateAddress: function () {
                var addressData, newShippingAddress;

                this.source.set('params.invalid', false);
                if (this.isAddressFormVisible() || this.isAddressListVisible()) {
                    // eslint-disable-next-line eqeqeq
                    if (this.selectedAddress() && this.selectedAddress() != newAddressOption) {
                        selectShippingAddress(this.selectedAddress());
                        this.isAddressFormVisible(false);
                        this.isAddressListVisible(false);
                        this.isNewAddressVisible(false);
                        this.isAddressDetailsVisible(true);
                        checkoutData.setSelectedShippingAddress(this.selectedAddress().getKey());
                    } else {
                        this.source.trigger(this.dataScopePrefix + '.data.validate');

                        if (this.source.get(this.dataScopePrefix + '.custom_attributes')) {
                            this.source.trigger(this.dataScopePrefix + '.custom_attributes.data.validate');
                        }

                        if (!this.source.get('params.invalid')) {
                            addressData = this.source.get(this.dataScopePrefix);

                            if (customer.isLoggedIn() && !this.customerHasAddresses) { //eslint-disable-line max-depth
                                this.saveInAddressBook(1);
                            }

                            addressData['save_in_address_book'] = this.saveInAddressBook() ? 1 : 0;
                            newShippingAddress = createShippingAddress(addressData);
                            selectShippingAddress(newShippingAddress);
                            this.isNewAddressVisible(true);
                            this.isAddressFormVisible(false);
                            this.isAddressListVisible(false);
                            this.isAddressDetailsVisible(true);
                            checkoutData.setSelectedShippingAddress(newShippingAddress.getKey());
                            checkoutData.setNewCustomerShippingAddress(addressData);
                        }
                    }
                }
            },

            /**
             * Edit address action
             */
            editAddress: function () {
                lastSelectedShippingAddress = quote.shippingAddress();

                this.isAddressListVisible(true);
                this.isAddressDetailsVisible(false);
                if (this.isNewAddressVisible()) {
                    this.isAddressFormVisible(true);
                }
            },

            /**
             * Cancel address edit action
             */
            cancelAddressEdit: function () {
                this.restoreShippingAddress();

                if (quote.shippingAddress()) {
                    this.isAddressDetailsVisible(true);
                }

                this.isAddressFormVisible(false);
                this.isAddressListVisible(false);

                // eslint-disable-next-line eqeqeq
                if (this.selectedAddress() && this.selectedAddress() == newAddressOption) {
                    this.isNewAddressVisible(true);
                }
            },

            /**
             * Restore shipping address
             */
            restoreShippingAddress: function () {
                if (lastSelectedShippingAddress != null) {
                    selectShippingAddress(lastSelectedShippingAddress);
                }
            },

            /**
             * @param {Object} address
             */
            onAddressChange: function (address) {
                this.isAddressFormVisible(address == newAddressOption); //eslint-disable-line eqeqeq
                this.shippingAddressComponent().isNewAddressAdded(true);
            },

            /**
             * @param {Number} countryId
             * @return {*}
             */
            getCountryName: function (countryId) {
                return !_.isUndefined(countryData()[countryId]) ? countryData()[countryId].name : '';
            },

            /**
             * Hide Multiple Shipping Address, if value Drop Down Menu in this option
             */
            hideMultiShippingAddress: function () {
                if (require.defined('Amazon_Payment/js/model/storage')) {
                    require(['Amazon_Payment/js/model/storage'], function (amazonStorage) {
                        // hide shipping form if customer logged in by Amazon
                        amazonStorage.isAmazonAccountLoggedIn.subscribe(function (isLoggedIn) {
                            this.isVisible(!isLoggedIn);
                        }, this);
                        if (amazonStorage.isAmazonAccountLoggedIn()) {
                            this.isVisible(false);
                        }
                    }.bind(this));
                }
            }
        });
    });

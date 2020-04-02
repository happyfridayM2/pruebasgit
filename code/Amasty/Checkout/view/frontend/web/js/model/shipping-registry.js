define([
    'ko',
    'underscore',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/shipping-rates-validation-rules',
    'Magento_Checkout/js/model/shipping-address/form-popup-state',
    'Amasty_Checkout/js/model/address-form-state',
    'Amasty_Checkout/js/model/payment/payment-loading',
    'Magento_Ui/js/lib/validation/validator'
], function (ko, _, quote, validationRules, formPopUpState, addressFormState, paymentLoader, validator) {
    'use strict';

    return {
        savedAddress: '',
        shippingMethod: '',
        shippingCarrier: '',

        /**
         * list of shipping address
         * it also can contains third party extension fields
         */
        addressComponents: [],

        /**
         * filtered addressComponents
         *  modules that are not used in shipping estimation (not in observable)
         */
        observedComponents: [],

        /**
         * Saved values of observable address.
         * Update on shipping address save
         */
        additionalAddressValues: {},

        /**
         * @api
         * Excluded components by name (index)
         * Additional excludes should be added before initObservers executed
         */
        excludedFieldsNames: [],

        /**
         * @api
         * Excluded collection components by name (index)
         * Additional excludes should be added before initObservers executed
         */
        excludedCollectionNames: ['billing-address-form'],
        isEstimationHaveError: ko.observable(false),
        isAddressChanged: ko.observable(false).extend({notify: 'always', rateLimit: 20}),
        validationTimeout: 0,
        checkDelay: 1000,

        /**
         * Register additional shipping fields observers
         * @param {Function} elems - obervable array of shipping elements
         */
        initObservers: function (elems) {
            if (_.isEmpty(this.addressComponents)) {
                this.excludedFieldsNames = _.union(this.excludedFieldsNames, validationRules.getObservableFields());
                this.filterElements(elems());

                _.each(this.addressComponents, function (element) {
                    if (this.excludedFieldsNames.indexOf(element.index) === -1) {
                        this.observedComponents.push(element);
                        this.additionalAddressValues[element.index] = null;
                    }

                    element.on('value', this.triggerValidation.bind(this, element));
                }, this);
            }
        },

        /**
         * Extract all fields wich can be observable from fields
         * @param {Array} elems
         */
        filterElements: function (elems) {
            if (!elems || !elems.length) {
                return;
            }
            _.each(elems, function (element) {
                if (this._isCollection(element)) {
                    try {
                        if (this._isCollectionValid(element)) {
                            this.filterElements(element.elems());
                        }
                    } catch (e) {
                        //continue
                    }
                    return;//continue
                }

                if (this._isModuleValid(element)) {
                    this.addressComponents.push(element);
                }
            }.bind(this));
        },

        /**
         * Is component are collection
         *
         * @param {Object} element
         * @returns {Boolean}
         * @private
         */
        _isCollection: function (element) {
            return typeof element.initChildCount === 'number';
        },

        /**
         * Is component collection is valid
         *
         * @param {Object} element
         * @returns {Boolean}
         * @private
         */
        _isCollectionValid: function (element) {
            return this.excludedCollectionNames.indexOf(element.index) === -1;
        },

        /**
         * Is component can be observable
         *
         * @param {Object} module
         * @returns {Boolean}
         * @private
         */
        _isModuleValid: function (module) {
            return ko.isObservable(module.error)
                && ko.isObservable(module.value);
        },

        /**
         * debounce validation
         */
        triggerValidation: function () {
            clearTimeout(this.validationTimeout);
            if (!formPopUpState.isVisible() && !addressFormState.isShippingFormVisible()) {
                paymentLoader(true);

                this.validationTimeout = setTimeout(this.validation.bind(this), this.checkDelay);
            }
        },

        validation: function () {
            var isError = false,
                valueChanged = false,
                result;

            _.find(this.observedComponents, function (element) {
                if (element.visible && !element.visible() || element.disabled && element.disabled()) {
                    return false;//continue
                }

                if (element.error()) {
                    isError = true;
                    return true;//break
                }

                if (_.isObject(element.validation)) {
                    result = validator(element.validation, element.value(), element.validationParams);
                    if (!result.passed) {
                        isError = true;
                        return true;//break
                    }
                }

                if (this.additionalAddressValues[element.index] !== element.value()) {
                    valueChanged = true;
                }

                return false;
            }, this);

            this.isEstimationHaveError(isError);

            if (!isError) {
                this.isAddressChanged(valueChanged);
            } else {
                paymentLoader(false);
            }
        },

        /**
         * Store saved values for tracking changes
         */
        registerAdditionAddressValues: function () {
            clearTimeout(this.validationTimeout);
            _.each(this.observedComponents, function (element) {
                this.additionalAddressValues[element.index] = element.value();
            }.bind(this));
        },

        /**
         * Set saved data
         *
         * @param {object} address
         */
        register: function (address) {
            if (!address) {
                address = quote.shippingAddress();
            }

            this.savedAddress = address;
            this.shippingMethod = quote.shippingMethod().method_code;
            this.shippingCarrier = quote.shippingMethod().carrier_code;
            this.registerAdditionAddressValues();
        },

        /**
         * Compare current Shipping Data with saved and determines is need to save it
         *
         * @returns {boolean}
         */
        isHaveUnsavedShipping: function () {
            var methodData = quote.shippingMethod();

            if (!methodData) {
                return false;
            }

            if (!this.savedAddress) {
                return true;
            }

            return this.isAddressChanged()
                || !this._compareObjectsData(quote.shippingAddress(), this.savedAddress)
                || this.shippingMethod !== methodData.method_code
                || this.shippingCarrier !== methodData.carrier_code;
        },

        /**
         * Is objects are equal
         *
         * @param {*} objA
         * @param {*} objB
         * @returns {boolean}
         * @private
         */
        _compareObjectsData: function (objA, objB) {
            // remove functions
            objA = _.pick(objA, function (value, key) {
                return !_.isFunction(value) || key !== 'save_in_address_book';
            });
            objB = _.pick(objB, function (value, key) {
                return !_.isFunction(value) || key !== 'save_in_address_book';
            });

            // objects for isEqual should not contain functions. Same for objects of their objects
            return _.isEqual(objA, objB);
        }
    };
});

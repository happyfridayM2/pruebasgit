define(
    [
        'jquery',
        'Magento_Catalog/js/price-utils',
        'Magento_Catalog/js/price-box',
        'magento-configurable.renderer'
    ],

    function ($, utils) {
        'use strict';

        $.widget('amasty_conf.ConfigurableRenderer', $.mage.configurable, {

            _create: function () {
                this.amasty_conf_config = window.amasty_conf_config;
                if (this.amasty_conf_config && this.amasty_conf_config.share.enable == '1') {
                    this._createShareBlock();
                }

                this._super();
            },

            /**
             * Populates an option's selectable choices.
             * @private
             * @param {*} element - Element associated with a configurable option.
             */
            _fillSelect: function (element) {
                var attributeId = element.id.replace(/[a-z]*/, ''),
                    options = this._getAttributeOptions(attributeId),
                    prevConfig,
                    index = 1,
                    allowedProducts,
                    i,
                    j,
                    isLastSetting = ($(".super-attribute-select").last()[0] == element),
                    attributeCode = this.options.spConfig.attributes[attributeId].code;

                this._clearSelect(element);
                element.options[0] = new Option('', '');
                element.options[0].innerHTML = this.options.spConfig.chooseText;
                prevConfig = false;

                if (element.prevSetting) {
                    prevConfig = element.prevSetting.options[element.prevSetting.selectedIndex];
                }

                if (options) {
                    for (i = 0; i < options.length; i++) {
                        allowedProducts = [];

                        /* eslint-disable max-depth */
                        if (prevConfig) {
                            for (j = 0; j < options[i].products.length; j++) {
                                // prevConfig.config can be undefined
                                if (prevConfig.config &&
                                    prevConfig.config.allowedProducts &&
                                    prevConfig.config.allowedProducts.indexOf(options[i].products[j]) > -1) {
                                    allowedProducts.push(options[i].products[j]);
                                }
                            }
                        } else {
                            allowedProducts = options[i].products.slice(0);
                        }

                        if (allowedProducts.length > 0) {
                            options[i].allowedProducts = allowedProducts;
                            element.options[index] = new Option(this._getOptionLabel(options[i]), options[i].id);

                            /* add price in dropdown */
                            if (isLastSetting) {
                                var productId = options[i].allowedProducts[0];
                                this._addPriceLabel(element.options[index], productId);
                            }

                            if (typeof options[i].price !== 'undefined') {
                                element.options[index].setAttribute('price', options[i].prices);
                            }

                            element.options[index].config = options[i];
                            index++;
                        }

                        /* eslint-enable max-depth */
                    }
                }
                if (!this.options.values[attributeId]
                    && this.options.spConfig.preselect
                    && this.options.spConfig.preselect.attributes[attributeCode]
                ) {
                    this.options.values[attributeId] = this.options.spConfig.preselect.attributes[attributeCode];
                }
            },

            _addPriceLabel: function (option, productId) {
                var showPrice = parseInt(this.options.spConfig.show_dropdown_prices);
                if (showPrice > 0) {
                    var price = this.options.spConfig.optionPrices[productId].finalPrice.amount,
                        priceConfig = $(this.options.priceHolderSelector).priceBox('option').priceConfig
                    if (price && priceConfig) {
                        var parentPrice = priceConfig.prices.finalPrice.amount,
                            priceFormat = (priceConfig && priceConfig.priceFormat) || {};
                        if (showPrice === 1) { // show price difference
                            price = price - parentPrice;
                        }

                        if (price > 0) {
                            var formatted = utils.formatPrice(price, priceFormat);
                            if (formatted.indexOf('-') === -1 && showPrice === 1) {
                                formatted = '+' + formatted;
                            }

                            option.text = option.text + '  ' + formatted;
                        }
                    }
                }
            },

            /**
             * Configure an option, initializing it's state and enabling related options, which
             * populates the related option's selection and resets child option selections.
             * @private
             * @param {*} element - The element associated with a configurable option.
             */
            _configureElement: function (element) {
                this.simpleProduct = this._getSimpleProductId(element);

                if (element.value) {
                    this.options.state[element.config.id] = element.value;

                    if (element.nextSetting) {
                        element.nextSetting.disabled = false;
                        this._fillSelect(element.nextSetting);
                        this._resetChildren(element.nextSetting);
                    } else {
                        if (!!document.documentMode) { //eslint-disable-line
                            this.inputSimpleProduct.val(element.options[element.selectedIndex].config.allowedProducts[0]);
                        } else {
                            this.inputSimpleProduct.val(element.selectedOptions[0].config.allowedProducts[0]);
                        }

                    }

                } else {
                    this._resetChildren(element);
                }

                this._reloadPrice();
                this._displayRegularPriceBlock(this.simpleProduct);
                this._displayTierPriceBlock(this.simpleProduct);
                var gallery = $('[data-gallery-role=gallery-placeholder]', '.column.main');
                if (gallery.data('gallery') || gallery.data('amasty_gallery')) {
                    this._changeProductImage();
                } else {
                    gallery.on('gallery:loaded', this._changeProductImage.bind(this));
                    gallery.on('amasty_gallery:loaded', this._changeProductImage.bind(this));
                }
                this._reloadProductInformation();

                if(this.amasty_conf_config && this.amasty_conf_config.share.enable == '1') {
                    this._addHashToUrl(element.config.id, element.value, this);
                }
            },

            _reloadProductInformation: function () {
                var $widget = this;

                if (!$widget.options.spConfig.product_information) {
                    return;
                }

                if (this.simpleProduct) {
                    var result = $widget.options.spConfig.product_information[this.simpleProduct];
                }
                var defaultResult = $widget.options.spConfig.product_information['default'];

                if (result) {
                    for (var component in defaultResult) {
                        if (!result[component]) {
                            result[component] = defaultResult[component];
                        }
                    }

                    for(var component in result) {
                        this._updateSimpleData(result[component]);
                    }
                } else {
                    for(var component in defaultResult) {
                        this._updateSimpleData(defaultResult[component]);
                    }
                }
            },

            _updateSimpleData: function (data) {
                if (data && data.selector && data.value) {
                    $(data.selector).html(data.value);
                }
            },

            _changeProductImage: function () {
                var amastyZoomEnabled = $('[data-role="amasty-gallery"]').length > 0;

                if (amastyZoomEnabled && !this.inProductList) {
                    this._reloadAmastyImageBlock();
                } else {
                    this._super();
                }
            },

            _reloadAmastyImageBlock: function () {
                var images = this.options.spConfig.images[this.simpleProduct];

                if (!images) {
                    images = this.options.mediaGalleryInitial;
                }

                var element = $('[data-role=amasty-gallery]').first();
                var zoomObject = element.data('zoom_object');
                if (zoomObject && images) {
                    zoomObject.reload(images);
                }
            },

            _createShareBlock: function () {
                var parent = $('.product-social-links');
                var link = jQuery('<a/>', {
                    class: 'action mailto friend amconf-share-link',
                    title: this.amasty_conf_config.share.title,
                    text: this.amasty_conf_config.share.title,
                    'data-amconf-js': 'share-link'
                }).appendTo(parent);

                link.on('click', function () {
                    $('.amconf-share-container').toggle();
                    $('.amconf-share-input').prop('value', window.location);
                });

                var container = jQuery('<div/>', {
                    class: 'amconf-share-container',
                    'data-amconf-js': 'share-container'
                }).appendTo(parent);

                var input = jQuery('<input/>', {
                    class: 'amconf-share-input',
                    type: 'text'
                }).appendTo(container);

                var button = jQuery('<button/>', {
                    class: 'amconf-share-button action primary',
                    html: '<span>' + this.amasty_conf_config.share.link + '</span>'
                }).appendTo(container);

                button.on('click', function () {
                    $('.amconf-share-input').select();
                    var status = document.execCommand('copy');
                    if(!status){
                        console.error("Can't copy text");
                    }
                });

                $(document).on('click', function (e) {
                    if (!$(e.target).closest('[data-amconf-js="share-container"]').length &&
                        !$(e.target).is('[data-amconf-js="share-link"]')) {
                        $('[data-amconf-js="share-container"]').hide();
                    }
                });
            },

            _addHashToUrl: function (attributeCode, optionId, $widget) {
                var addParamsToHash = 1,
                    isProductViewExist = $('body.catalog-product-view').size() > 0;

                if (addParamsToHash && isProductViewExist){
                    var hash = window.location.hash;
                    if (hash.indexOf(attributeCode + '=') >= 0) {
                        var replaceText = new RegExp(attributeCode + '=' + '.*');
                        if(optionId) {
                            hash = hash.replace(replaceText, attributeCode + '=' + optionId);
                        }
                        else{
                            hash = hash.replace(replaceText, "");
                        }
                    }
                    else {
                        if (hash.indexOf('#') >= 0) {
                            hash = hash + '&' + attributeCode + '=' + optionId;
                        }
                        else {
                            hash = hash + '#' + attributeCode + '=' + optionId;
                        }
                    }

                    window.location.replace(window.location.href.split('#')[0] + hash);
                    $('.amconf-share-input').prop('value', window.location);
                }
            }
        });

        return $.amasty_conf.ConfigurableRenderer;
    });

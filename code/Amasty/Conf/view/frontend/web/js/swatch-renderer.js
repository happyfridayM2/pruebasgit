define(
    [
        'jquery',
        'underscore',
        'Magento_Catalog/js/price-utils',
        'mage/translate',
        'Magento_Catalog/js/price-box',
        'magento-swatch.renderer',
        'Amasty_Conf/vendor/slick/slick.min'
    ],
function ($, _, utils) {
    'use strict';

    $.widget('amasty_conf.SwatchRenderer', $.mage.SwatchRenderer, {
        selectors: {
            'qty-block' : '.field.qty'
        },
        defaultContents: [],
        productBlock: null,
        ajaxCart: false,
        fullSubtotal: 0,
        showFullSubtotal: true,

        _init: function () {
            this.productBlock = this.inProductList ?
                this.element.parents('.product-item-info') :
                this.element.parents('.column.main');
            if (this.productBlock.length === 0) {
                this.productBlock = this.element.parents('#confirmBox');
            }
            if (this.element.parents('#confirmBox').length > 0) {
                this.ajaxCart = true;
            }
            if (_.isEmpty(this.options.jsonConfig.images)) {
                this.options.useAjax = true;
                // creates debounced variant of _LoadProductMedia()
                // to use it in events handlers instead of _LoadProductMedia()
                this._debouncedLoadProductMedia = _.debounce(this._LoadProductMedia.bind(this), 500);
            }
            this.options.tierPriceTemplate = $(this.options.tierPriceTemplateSelector).html();
            if (this.options.jsonConfig !== '' && this.options.jsonSwatchConfig !== '') {
                this.options.jsonConfig.mappedAttributes = _.clone(this.options.jsonConfig.attributes);
                this._sortAttributes();
                this._RenderControls();

                var isProductViewExist = $('body.catalog-product-view').length > 0;
                if (isProductViewExist) {
                    this._RenderPricesForControls();
                }
                if (isProductViewExist || this.ajaxCart) {
                    if (this.options.jsonConfig.matrix) {
                        this._observeAddToCart();
                        this._RenderProductMatrix();
                    }
                    if (this.options.jsonConfig.swatches_slider) {
                        this._generateSliderSwatches();
                    }
                }
                this._addOutOfStockLabels();

                //Compatibility with 2.2.0
                if (typeof this._setPreSelectedGallery === "function") {
                    this._setPreSelectedGallery();
                }

                if (this.options.jsonConfig.matrix) {
                    this._removeDefaultQty();
                }

                $(this.element).trigger('swatch.initialized');
            } else {
                console.log('SwatchRenderer: No input data received');
            }
        },

        isMobileAndTablet: function () {
            return /Android|webOS|iPhone|iPod|BlackBerry|BB|PlayBook|IEMobile|Windows Phone|Kindle|Silk|Opera Mini/i.test(navigator.userAgent);
        },

        _EventListener: function () {
            this.amasty_conf_config = window.amasty_conf_config;
            var $widget = this;

            $widget.element.on('click', '.' + this.options.classes.optionClass, function () {
                return $widget._AmOnClick($(this), $widget);
            });

            if(this.amasty_conf_config && this.amasty_conf_config.share.enable == '1') {
                this._createShareBlock();
            }

            $widget.element.on('change', '.' + this.options.classes.selectClass, function () {
                return $widget._AmOnChange($(this), $widget);
            });

            $widget.element.on('click', '.' + this.options.classes.moreButton, function (e) {
                e.preventDefault();

                return $widget._OnMoreClick($(this));
            });

            if (!this.isMobileAndTablet() && parseInt($widget.options.jsonConfig.change_mouseover)) {
                $widget.element.on('mouseover', '.' + this.options.classes.optionClass, function () {
                    return $widget.onMouseOver($(this), $widget);
                });
                $widget.element.on('mouseleave', '.' + this.options.classes.optionClass, function () {
                    return $widget.onMouseLeave($(this), $widget);
                });
            }
        },

        _createShareBlock: function () {
            var parent = this.productBlock.find('.product-social-links');
            var link = $('<a/>', {
                class: 'action mailto friend amconf-share-link',
                title: this.amasty_conf_config.share.title,
                text: this.amasty_conf_config.share.title,
                'data-amconf-js': 'share-link'
            }).appendTo(parent);

            link.on('click', function () {
                $('.amconf-share-container').toggle();
                $('.amconf-share-input').prop('value', window.location);
            });

            var container = $('<div/>', {
                class: 'amconf-share-container',
                'data-amconf-js': 'share-container'
            }).appendTo(parent);

            var input = $('<input/>', {
                class: 'amconf-share-input',
                type: 'text'
            }).appendTo(container);

            var button = $('<button/>', {
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
                if (!$(e.target).closest('[data-amconf-js="share-container"]').length
                    && !$(e.target).is('[data-amconf-js="share-link"]')
                ) {
                    $('[data-amconf-js="share-container"]').hide();
                }
            });
        },

        _AmOnClick: function ($this, $widget) {
            $widget._OnClick($this, $widget);
            var slickSlide = $this,
                possibleSlide = $this.parent();
            // fix for when price in title enabled
            if (possibleSlide.hasClass('slick-slide')) {
                slickSlide = possibleSlide;
            }
            if (slickSlide.hasClass('slick-slide') && $this.hasClass('selected')) {
                slickSlide.parent()
                    .find('[option-id="' + $this.attr('option-id') + '"]:not(.selected)')
                    .addClass('selected');
            }

            if (this.amasty_conf_config && this.amasty_conf_config.share.enable == '1') {
                $widget._addHashToUrl($this, $widget);
            }

            $widget._reloadProductInformation($this, $widget);

            var isProductViewExist = $('body.catalog-product-view').length > 0;

            if (isProductViewExist) {
                $widget._RenderPricesForControls();
            }
            if (isProductViewExist || this.ajaxCart) {
                this._saveLastRowContent();
                $widget._RenderProductMatrix();
                if (this.options.jsonConfig.swatches_slider) {
                     this._generateSliderSwatches();
                }
            }
            $widget._addOutOfStockLabels();
        },

        _AmOnChange: function ($this, $widget) {
            $widget._OnChange($this, $widget);
            $widget._reloadProductInformation($this, $widget);

            var isProductViewExist = $('body.catalog-product-view').length > 0;
            if (isProductViewExist) {
                $widget._RenderPricesForControls();
            }
            if (isProductViewExist || this.ajaxCart) {
                $widget._RenderProductMatrix();
            }
            
            if(this.amasty_conf_config && this.amasty_conf_config.share.enable == '1') {
                $widget._addHashToUrl($this, $widget);
            }
        },

        _addHashToUrl: function ($this, $widget) {
            var addParamsToHash = 1,
                isProductViewExist = this.productBlock.length > 0 && $('body.catalog-product-view').length > 0,
                attributeCode = $this.parents('.' + this.options.classes.attributeClass).attr('attribute-code'),
                optionId = $this.attr('option-id');
                
            if (!optionId) {
                optionId = $this.val();//for dropdown
            }    

            if (addParamsToHash && isProductViewExist && optionId) {
                var hash = window.location.hash,
                    attributeHash = attributeCode + '=' + optionId;
                if (hash.indexOf(attributeCode + '=') >= 0) {
                    hash = this._replaceHashParams(attributeHash, attributeCode);
                } else {
                    hash += hash.indexOf('#') >= 0 ? '&' : '#';
                    hash += attributeHash;
                }
                this._setHash(hash);
            }

            if(!isProductViewExist) {
                var parent = $widget.element.parents('.item');
                if (parent.length > 0) {
                    var productLinks = parent.find('a:not([href^="#"]):not([data-post*="action"]):not([href*="#reviews"])');
                    $.each(productLinks, function(i, link ) {
                        link = $(link);
                        var href = link.prop('href');
                        if (href.indexOf(attributeCode + '=') >= 0) {
                            var replaceText = new RegExp(attributeCode + '=' + '\\d+');
                            href = href.replace(replaceText, attributeCode + '=' + optionId)
                            link.prop('href', href);
                        }
                        else {
                            if (href.indexOf('#') >= 0) {
                                link.prop('href', href + '&' + attributeCode + '=' + optionId);
                            }
                            else {
                                link.prop('href', href + '#' + attributeCode + '=' + optionId);
                            }
                        }
                    });
                }
            }
        },

        _reloadProductInformation: function ($this, $widget) {
            var $widget = this,
                options = _.object(_.keys($widget.optionsMap), {});

            if(!$widget.options.jsonConfig.product_information) {
                return;
            }

            $widget.element.find('.' + $widget.options.classes.attributeClass + '[option-selected]').each(function () {
                var attributeId = $(this).attr('attribute-id');
                options[attributeId] = $(this).attr('option-selected');
            });

            var result = $widget.options.jsonConfig.product_information[_.findKey($widget.options.jsonConfig.index, options)],
                defaultResult = $widget.options.jsonConfig.product_information['default'];

            if (result) {
                for (var component in defaultResult) {
                    if (defaultResult.hasOwnProperty(component)) {
                        if (result[component] == null) {
                            result[component] = defaultResult[component];
                        }
                    }
                }
                for (var component in result) {
                    if (result.hasOwnProperty(component)) {
                        this._updateSimpleData(result[component]);
                    }
                }
            } else {
                for (var component in defaultResult) {
                    if (defaultResult.hasOwnProperty(component)) {
                        this._updateSimpleData(defaultResult[component]);
                    }
                }
            }
        },

        _updateSimpleData: function (data) {
            if (data && data.selector && data.value) {
                this.productBlock.find(data.selector).html(data.value);
            }
        },

        _RenderSwatchSelect: function (config, chooseText) {
            var $widget = this,
                html,
                attrConfig = config;

            if (this.options.jsonSwatchConfig.hasOwnProperty(attrConfig.id)) {
                return '';
            }

            html =
                '<select class="' + this.options.classes.selectClass + ' ' + attrConfig.code + '">' +
                '<option value="0" option-id="0">' + chooseText + '</option>';

            $widget.defaultContents[attrConfig.id] = [];
            $.each(attrConfig.options, function () {
                var label = this.label,
                    attr = ' value="' + this.id + '" option-id="' + this.id + '"';

                if (!this.hasOwnProperty('products') || this.products.length <= 0) {
                    attr += ' option-empty="true"';
                } else {
                    var showPrice = parseInt($widget.options.jsonConfig.show_dropdown_prices);
                    if (typeof $widget.defaultContents[attrConfig.id][this.id] === 'undefined') {
                        $widget.defaultContents[attrConfig.id][this.id] = [];
                    }
                    $widget.defaultContents[attrConfig.id][this.id]['label'] = label;
                    if (showPrice > 0 && this.products.length == 1) { // setting show price is enabled
                        var price = $widget.options.jsonConfig.optionPrices[this.products[0]].finalPrice.amount,
                            priceBoxSelector = '[data-role=priceBox][data-product-id="' + $widget.options.jsonConfig.productId + '"]',
                            priceConfig = $(priceBoxSelector).priceBox('option').priceConfig,
                            parentPrice = priceConfig.prices.finalPrice.amount,
                            priceFormat = (priceConfig && priceConfig.priceFormat) || {};
                        if (showPrice === 1) { // show price difference
                            price = price - parentPrice;
                        }
                        if (price) {
                            var formatted = utils.formatPrice(price, priceFormat);
                            if (formatted.indexOf('-') === -1 && showPrice === 1) {
                                formatted = '+' + formatted;
                            }
                            label += '  ' + formatted;
                            $widget.defaultContents[attrConfig.id][this.id]['label_price'] = label;
                        }
                    }
                }

                html += '<option ' + attr + '>' + label + '</option>';
            });

            html += '</select>';

            return html;
        },

        onMouseOver: function ($this, $widget) {
            var $parent = $this.parents('.' + $widget.options.classes.attributeClass);
            if ($this.attr('option-id') > 0) {
                $parent.attr('option-selected-old', $parent.attr('option-selected'));
                $parent.attr('option-selected', $this.attr('option-id'));
                $widget._loadMedia();
            }

        },

        onMouseLeave: function ($this, $widget) {
            var $parent = $this.parents('.' + $widget.options.classes.attributeClass),
                selectedOption = $parent.find('.' + $widget.options.classes.optionClass + '.selected');
            if (selectedOption.length > 0) {
                $parent.attr('option-selected', selectedOption.attr('option-id'));
                $widget._loadMedia();
            } else {
                $parent.attr('option-selected', $parent.attr('option-selected-old'));
            }
        },

        /**
         * Emulate mouse click on all swatches that should be selected
         * @private
         */
        _EmulateSelected: function () {
            var gallery = $('[data-gallery-role=gallery-placeholder]', '.column.main');
            if (this.amasty_conf_config) {
                if ((gallery.data('gallery') || gallery.data('amasty_gallery') || this.inProductList)
                    && !this.options.jsonConfig.preselected && this.productBlock.length > 0
                    // preselect for popup processed in Amasty_Cart
                    && this.productBlock.closest('#confirmBox').length == 0
                ) {
                    var selectedAttributes = this.getPreselectedAttributes();
                    $.each(selectedAttributes, $.proxy(function (attributeCode, optionId) {
                        var select = this.element.find('.' + this.options.classes.attributeClass +
                            '[attribute-code="' + attributeCode + '"] .swatch-select');
                        if (select.length > 0) {
                            select.val(optionId);
                            select.trigger('change');
                        } else {
                            this.element.find('.' + this.options.classes.attributeClass +
                                '[attribute-code="' + attributeCode + '"] [option-id="' + optionId + '"]')
                                .first().trigger('click');
                        }
                    }, this));
                    this.options.jsonConfig.preselected = true;
                } else {
                    if (!this.amasty_conf_config.bindGallery) {
                        gallery.on('gallery:loaded', this._onGalleryLoadedFRunEmulation.bind(this, gallery));
                        gallery.on('amasty_gallery:loaded', this._onGalleryLoadedFRunEmulation.bind(this, gallery));
                    }
                }
                this.amasty_conf_config.bindGallery = true;
            }
            this.options.jsonConfig.blockedImage = false;
        },

        /*fix issue when gallery data not loaded during option click*/
        _onGalleryLoadedFRunEmulation: function (element) {
            this._EmulateSelected();
        },
        
        getPreselectedAttributes: function() {
            var selectedAttributes = this._getSelectedAttributes();
            if (_.isEmpty(selectedAttributes) && this.options.jsonConfig.preselect) {
                selectedAttributes = this.options.jsonConfig.preselect.attributes;
            }
            
            return selectedAttributes;
        },

        /**
         * Load media gallery using ajax or json config.
         *
         * @private
         */
        _loadMedia: function () {
            if (!this.options.jsonConfig.blockedImage) {
                var amastyZoomEnabled = $('[data-role="amasty-gallery"]').length > 0;

                if (amastyZoomEnabled && !this.inProductList) {
                    this._reloadAmastyImageBlock();
                } else {
                    this._super();
                }
            }
        },
        
        /**
         * Compatibility with m2.1.5
         *
         * @private
         */
        _LoadProductMedia: function() {
            if (!this.options.jsonConfig.blockedImage) {
                var amastyZoomEnabled = $('[data-role="amasty-gallery"]').length > 0;

                if (amastyZoomEnabled && !this.inProductList) {
                    this._reloadAmastyImageBlock();
                } else {
                    this._super();
                }
            }
        },

        /**
         * Get chosen product. Compatibility with m2.1.5
         *
         * @returns int|null
         */
        getProduct: function () {
            var products = this._CalcProducts();

            return _.isArray(products) ? products[0] : null;
        },

        /**
         * Sorting images array. Compatibility with m2.2.5
         *
         * @private
         */
        _sortImages: function (images) {
            return _.sortBy(images, function (image) {
                return image.position;
            });
        },

        _reloadAmastyImageBlock: function () {
            var images = this.options.jsonConfig.images[this.getProduct()];

            if (!images) {
                images = this.options.mediaGalleryInitial;
            }

            var element = $('[data-role=amasty-gallery]').first();
            var zoomObject = element.data('zoom_object');
            if (zoomObject) {
                zoomObject.reload(this._sortImages(images), this.options.gallerySwitchStrategy);
            }
        },

        _addOutOfStockLabels: function () {
            var $widget = this;
            if(this.options.jsonConfig.show_out_of_stock != 1) {
                return;
            }

            var attributeJson = this.options.jsonConfig.attributes[this.options.jsonConfig.attributes.length-1];
            if (!attributeJson || !attributeJson.options) {
                return;
            }


            var options = _.object(_.keys($widget.optionsMap), {});
            $widget.element.find('.' + $widget.options.classes.attributeClass + '[option-selected]').each(function () {
                var attributeId = $(this).attr('attribute-id');
                options[attributeId] = $(this).attr('option-selected');
            });

            var productInformation = $widget.options.jsonConfig.product_information;
            $.each(attributeJson.options, function () {
                options[attributeJson.id] = this.id;
                var product = _.findKey($widget.options.jsonConfig.index, options),
                    option = $widget.element.find('.swatch-option[option-id="' + this.id + '"]');
                if (product && option.length) {
                    if (productInformation[product] && !productInformation[product].is_in_stock) {
                        option.addClass('out-of-stock')
                            .addClass('disabled')
                            .removeClass('selected')
                            .attr('disabled', 'disabled');
                    } else {
                        option.removeClass('out-of-stock')
                            .removeClass('disabled')
                            .removeAttrs('disabled');
                    }
                }
            });
        },

        _RenderPricesForControls: function () {
            var $widget = this;
            if (this.options.jsonConfig.show_prices != 1) {
                return;
            }

            var attributeJson = this.options.jsonConfig.attributes[this.options.jsonConfig.attributes.length-1];
            if (!attributeJson || !attributeJson.options) {
                return;
            }

            $('[attribute-id="' + attributeJson.id + '"] .swatch-option-price').remove();

            var options = _.object(_.keys($widget.optionsMap), {});
            $widget.element.find('.' + $widget.options.classes.attributeClass + '[option-selected]').each(function () {
                var attributeId = $(this).attr('attribute-id');
                options[attributeId] = $(this).attr('option-selected');
            });

            $.each(attributeJson.options, function () {
                options[attributeJson.id] = this.id;
                var product = _.findKey($widget.options.jsonConfig.index, options);
                if (product) {
                    var price = $widget.options.jsonConfig.optionPrices[product].finalPrice.amount;
                    if (price) {
                        var priceConfig = $('[data-role=priceBox][data-product-id="' + $widget.options.jsonConfig.productId + '"]')
                                .priceBox('option')
                                .priceConfig,
                            priceFormat = (priceConfig && priceConfig.priceFormat) || {},
                            formatted = utils.formatPrice(price, priceFormat),
                            option = $('.swatch-option[option-id="' + this.id + '"]');
                        if (option.length && formatted) {
                            if (option.parents('.swatch-option-container').length === 0) {
                                option.wrap("<div class='swatch-option-container'></div>");
                            }

                            option.after('<span class="swatch-option-price">' + formatted + '</span>');
                            option.css('float', 'none');
                            option.parent().css('float', 'left');
                        }
                    }
                }
            });
        },

        _Rebuild: function () {
            var $widget = this,
                controls = $widget.element.find('.' + $widget.options.classes.attributeClass + '[attribute-id]');

            if (controls.find('.swatch-option.selected').length > 1) {
                var selected = controls.filter('[option-selected]');
            } else {
                var selected = controls.filter('[option-selected]').not('.amconf-matrix-observed');
            }
            // Enable all options
            $widget._Rewind(controls);

            // done if nothing selected
            if (selected.length <= 0) {
                return;
            }

            // Disable not available options
            controls.each(function () {
                var $this = $(this),
                    id = $this.attr('attribute-id'),
                    products = $widget._CalcProducts(id);

                if (selected.length === 1 && selected.first().attr('attribute-id') === id) {
                    return;
                }

                $this.find('[option-id]').each(function () {
                    var $element = $(this),
                        option = $element.attr('option-id');

                    if (!$widget.optionsMap.hasOwnProperty(id) || !$widget.optionsMap[id].hasOwnProperty(option) ||
                        $element.hasClass('selected') ||
                        $element.is(':selected')) {
                        return;
                    }

                    if (_.intersection(products, $widget.optionsMap[id][option].products).length <= 0) {
                        $element.attr('disabled', true).addClass('disabled');
                        if (typeof $widget.defaultContents[id] !== 'undefined') {
                            $element[0].textContent = $widget.defaultContents[id][option]['label'];
                        }
                    } else if (_.intersection(products, $widget.optionsMap[id][option].products).length == 1
                        && typeof $widget.defaultContents[id] !== 'undefined'
                        && typeof $widget.defaultContents[id][option]['label_price'] !== 'undefined'
                    ) {
                        $element[0].textContent = $widget.defaultContents[id][option]['label_price'];
                    }
                });
            });
        },

        _RenderProductMatrix: function () {
            var $widget = this,
                optionProduct = {},
                isProductViewExist = this.ajaxCart || $('body.catalog-product-view').length > 0,
                useMatrix = isProductViewExist && this.options.jsonConfig.matrix,
                attributeHash = '';

            if(!useMatrix) {
                return;
            }

            var attributeJson = this.options.jsonConfig.attributes[this.options.jsonConfig.attributes.length-1];
            if (!attributeJson || !attributeJson.options) {
                return;
            }

            if ($widget.options.jsonConfig.swatches_slider && $('.amconf-matrix-table-wrap').length == 0) {
                $widget.restoreLastRowContent();
            }

            var options = _.object(_.keys($widget.optionsMap), {});
            $widget.element.find('.' + $widget.options.classes.attributeClass + '[option-selected]').each(function () {
                var attributeId = $(this).attr('attribute-id');
                options[attributeId] = $(this).attr('option-selected');
            });


            $.each(attributeJson.options, function (i) {
                options[attributeJson.id] = this.id;
                var product = _.findKey($widget.options.jsonConfig.index, options);
                if (product) {
                    optionProduct[i] = {'product' : product, 'id' : this.id };
                }
            });

            if (Object.keys(optionProduct).length) {
                this._replaceOptionToMatrix(optionProduct);
                this._hideDefaultQty();
            } else {
                var matrixElement = $('.amconf-matrix-observed');
                if (this.originalAttributeContent && matrixElement.length) {
                    if (matrixElement.attr('option-selected') != undefined) {
                        attributeHash = matrixElement.attr('attribute-code') + '=' + matrixElement.attr('option-selected');
                        var hash = this._replaceHashParams(attributeHash, matrixElement.attr('attribute-code'));
                        if (hash.lentgh > 0) {
                            this._setHash(hash);
                        }
                    } else {
                        matrixElement.html(this.originalAttributeContent);
                    }
                }
                this._showDefaultQty();
                $widget._Rebuild();
            }
        },

        _replaceOptionToMatrix: function (optionProduct) {
            var attributeJson = this.options.jsonConfig.attributes[this.options.jsonConfig.attributes.length-1],
                attributeContainer = this.productBlock.find('[attribute-code="' + attributeJson.code + '"]'),
                $widget = this;

            if (!attributeContainer.length) {
                return;
            }

            var newContent = this._generateMatrixContent(optionProduct, attributeContainer);
            attributeContainer.addClass('amconf-matrix-observed');
            attributeContainer.html('');
            attributeContainer.append(newContent);
            if (this.showFullSubtotal && this.options.jsonConfig.titles['subtotal']) {
                attributeContainer.append(this._generateSubtotalBlock());
            }

            attributeContainer.find('.amconf-matrix-arrow.-plus').on('click', $widget._plusQtyClick);
            attributeContainer.find('.amconf-matrix-arrow.-minus').on('click', $widget._minusQtyClick);
            attributeContainer.find('.amconf-matrix-input').on('change', $widget._changeOptionQty.bind(this));
            $('.amconf-matrix-input').trigger('change');
            $widget._Rebuild();
        },

        _saveLastRowContent: function () {
            var attributeJson = this.options.jsonConfig.attributes[this.options.jsonConfig.attributes.length-1],
                attributeContainer = $('[attribute-code="' + attributeJson.code + '"]');

            if (!attributeContainer.length) {
                return;
            }

            if (!attributeContainer.hasClass('amconf-matrix-observed')
                && attributeContainer.find('.slick-initialized').length == 0
            ) {
                this.originalAttributeContent = attributeContainer.html();
            }
        },

        restoreLastRowContent: function () {
            var attributeJson = this.options.jsonConfig.attributes[this.options.jsonConfig.attributes.length-1],
                attributeContainer = $('[attribute-code="' + attributeJson.code + '"]');

            if (!attributeContainer.length) {
                return;
            }

            attributeContainer.html(this.originalAttributeContent);
        },

        _generateSliderSwatches: function () {
            var self = this,
                itemsPerView = self.options.jsonConfig.swatches_slider_items_per_view;

            $.each(this.productBlock.find('.swatch-attribute-options'), function () {
                var $attrSet = $(this);

                if ($attrSet.find('.swatch-option').length > itemsPerView
                    && !$attrSet.hasClass('slick-initialized')
                ) {
                    if (!itemsPerView) {
                        var autoSlidesToShow = self.getAutoSlidesToShow($attrSet);

                        if (autoSlidesToShow) {
                            $attrSet.slick({
                                slidesToShow: autoSlidesToShow,
                                infinite: false
                            });
                        } else {
                            return false
                        }
                    } else {
                        $attrSet.slick({
                            slidesToShow : itemsPerView
                        });
                    }
                    $attrSet.find('.slick-cloned').each(function(key, elem) {
                        elem[$.expando] = null;
                        $(elem).unbind('hover').SwatchRendererTooltip();
                    });
                }
            });
        },

        getAutoSlidesToShow: function (itemsContainer) {
            var itemsContainerWidth = itemsContainer.width(),
                itemsWidth = 0,
                autoSlidesToShow;
            
            itemsContainer.children().each(function (index, item) {
                itemsWidth += $(item).outerWidth(true);
                if (itemsWidth >= itemsContainerWidth) {
                    autoSlidesToShow = index - 1;
                }
            });

            return autoSlidesToShow;
        },

        _generateMatrixContent: function (optionProduct, attributeContainer) {
            var $widget = this,
                attribute = this.options.jsonConfig.attributes[this.options.jsonConfig.attributes.length-1],
                attributeId = attributeContainer.attr('attribute-id'),
                table,
                tr,
                td;

            table = $('<table>', {
                'class': 'amconf-matrix-table-wrap'
            });

            tr = $('<tr>', {
                'class': 'amconf-matrix-title'
            }).appendTo(table);

            if (attributeContainer.find('.swatch-attribute-label').length) {
                $widget.options.jsonConfig.titles['attribute'] =
                    attributeContainer.find('.swatch-attribute-label').text();
            }

            $.each($widget.options.jsonConfig.titles, function (index, value) {
                $('<th class="amconf-cell">').html('<span class="amconf-text">' + value + '</span>').appendTo(tr);
            });

            $.each(optionProduct, function (i, data) {
                var option = data.id,
                    product = data.product,
                    productAvailability = $widget.options.jsonConfig.product_information[product].is_in_stock,
                    stockInformed = false;

                tr = $('<tr>', {
                    'class': 'amconf-matrix-row'
                }).appendTo(table);

                $.each($widget.options.jsonConfig.titles, function (index) {
                    td = $('<td>', {
                        'class': 'amconf-matrix-' + index + ' amconf-matrix-cell'
                    });

                    var value = '';
                    switch (index) {
                        case 'attribute':
                            var selector = '#option-label-color-' + attributeId + '-item-' + option,
                                element = attributeContainer.find(selector);
                            if (element.length) {
                                value = element.first().clone(true);
                                value.css({'width': 'auto'}).removeClass('slick-slide slick-current slick-active');
                            } else {
                                var controlLabelId = 'option-label-' + attribute.code + '-' + attribute.id,
                                    optionLabel = '',
                                    tmp = {};

                                tmp.id = attribute.id;
                                tmp.options = [];
                                $.each(attribute.options, function (key, opt) {
                                    if (opt.id === option) {
                                        tmp.options = [opt];
                                        optionLabel = opt.label
                                    }
                                });

                                value = $($widget._RenderSwatchOptions(tmp, controlLabelId));
                                if (!value.length) {
                                    value = $('<div>').text(optionLabel);
                                }
                            }
                            break;
                        case 'price':
                            var priceObject = $widget.options.jsonConfig.optionPrices[product],
                                price = priceObject.finalPrice.amount;
                            if (price) {
                                var oldPrice = priceObject.oldPrice.amount,
                                    priceConfig = $('[data-role=priceBox][data-product-id="' + $widget.options.jsonConfig.productId + '"]')
                                        .priceBox('option')
                                        .priceConfig,
                                    priceFormat = (priceConfig && priceConfig.priceFormat) || {},
                                    priceDiv = $('<div>').text(utils.formatPrice(price, priceFormat)),
                                    resultDiv = $('<div>');


                                resultDiv.append(priceDiv);
                                if (price !== oldPrice) {
                                    resultDiv.append($('<div>', {
                                        'class': 'amconf-matrix-old-price'
                                    }).text(utils.formatPrice(oldPrice, priceFormat)));
                                }

                                value = resultDiv;
                            } else {
                                value = $('<div>');
                            }
                            break;
                        case 'qty':
                            if (!productAvailability) {
                                value = $.mage.__('Out of stock');
                                stockInformed = true;
                                break
                            }

                            if ($widget.options.jsonConfig.product_information
                                && productAvailability
                            ) {
                                value = $widget._getInputBlockByOption(attributeId, option, product);
                            }

                            break;
                        case 'subtotal':
                            if (!productAvailability) {
                                value = $.mage.__('Out of stock');
                                stockInformed = true;
                                break
                            }

                            var price = $widget.options.jsonConfig.optionPrices[product].finalPrice.amount;
                            if (price) {
                                var priceConfig = $('[data-role=priceBox][data-product-id="' + $widget.options.jsonConfig.productId + '"]')
                                        .priceBox('option')
                                        .priceConfig,
                                    priceFormat = (priceConfig && priceConfig.priceFormat) || {};
                                value = $('<div>', {
                                    'class': 'amconf-matrix-subtotal',
                                    'data-price': price,
                                    'data-full-price': $widget._getDefaultQty(option) * price
                                }).text(utils.formatPrice(0, priceFormat));
                            } else {
                                value = $('<div>');
                            }
                            break;
                        case 'available':
                            if (!productAvailability) {
                                value = $.mage.__('Out of stock');
                                stockInformed = true;
                                break
                            }

                            if ($widget.options.jsonConfig.product_information
                                && $widget.options.jsonConfig.product_information[product].qty
                            ) {
                                value = $widget.options.jsonConfig.product_information[product].qty
                            }
                            break;
                        case 'sku':
                            value = $widget.options.jsonConfig.product_information[product].sku_value;
                            break;
                    }

                    if (stockInformed) {
                        td.append(value).prop('colspan', 3);
                        td.appendTo(tr);
                        return false
                    } else {
                        td.append(value);
                        td.appendTo(tr);
                    }
                });
            });

            return table;
        },

        _generateSubtotalBlock: function () {
            return $('<div>', {
                'class': 'amconf-matrix-full-subtotal',
                'text': this.options.jsonConfig.titles['subtotal'] + ': '
            }).append(
                $('<span>', {
                    'text': this._convertPrice(this.fullSubtotal)
                })
            );
        },

        _convertPrice: function (value) {
            var priceConfig = $('[data-role=priceBox][data-product-id="' + this.options.jsonConfig.productId + '"]')
                    .priceBox('option')
                    .priceConfig,
                priceFormat = (priceConfig && priceConfig.priceFormat) || {};

            return utils.formatPrice(value, priceFormat);
        },

        _getInputBlockByOption: function (attribute, id, product) {
            attribute = attribute.replace(/[^\d]/gi, '');
            var div = $('<div>', {
                'class': 'amconf-matrix-qty'
            });

            var span = $('<span>', {
                'class': 'amconf-matrix-arrow -minus'
            });
            div.append(span);

            var input = $('<input>', {
                'class': 'amconf-matrix-input',
                'name' : 'configurable-option[' + attribute + '][' + id + ']',
                'type': 'number',
                'min': '0',
                'step': 1,
                'value': this._getDefaultQty(id)
            });

            if (this.options.jsonConfig.product_information
                && this.options.jsonConfig.product_information[product].qty
                && !this.options.jsonConfig.product_information[product].preorder
            ) {
                input.attr('max', this.options.jsonConfig.product_information[product].qty);
            }
            div.append(input);

            span = $('<span>', {
                'class': 'amconf-matrix-arrow -plus'
            });
            div.append(span);

            return div;
        },

        _changeOptionQty: function (e) {
            var element = e.currentTarget;
            if (element.value < 0) {
                element.value = 0;
            }
            this._saveQty(element);

            this.fullSubtotal -= this._getTempSubtotal($(e.target));
            this._addSubtotalPrice($(e.target));
            this.fullSubtotal += this._getTempSubtotal($(e.target));

            $('.amconf-matrix-full-subtotal span').text(this._convertPrice(this.fullSubtotal));
        },

        _addSubtotalPrice: function (qtyElement) {
            try {
                var subtotal = qtyElement.parents('tr').find('.amconf-matrix-subtotal:not(".amconf-matrix-cell")').first(),
                    $widget = this;
            } catch(ex) {
                subtotal = null;
            }

            if (subtotal.length && qtyElement) {
                var qty = parseInt(qtyElement.val()),
                    price = subtotal.data('price'),
                    priceConfig,
                    priceFormat,
                    subtotalValue,
                    priceValue;

                if (price) {
                    priceConfig = $('[data-role=priceBox][data-product-id="' + $widget.options.jsonConfig.productId + '"]').priceBox('option').priceConfig,
                    priceFormat = (priceConfig && priceConfig.priceFormat) || {};

                    subtotalValue = price * qty;
                    subtotal.data('full-price', subtotalValue);
                    priceValue = utils.formatPrice(subtotalValue, priceFormat);

                    if (priceValue.indexOf('NaN') < 0) {
                        subtotal.text(priceValue);
                    } else {
                        subtotal.text('-');
                    }
                }
            }
        },

        _getTempSubtotal: function (qtyElement) {
            var tempSubtotal = qtyElement.parents('tr').find('.amconf-matrix-subtotal:not(".amconf-matrix-cell")')
                .first().data('full-price');

            return tempSubtotal;
        },

        _minusQtyClick: function (e) {
            try {
                var qtyElement = $(e.target).parent().find('.amconf-matrix-input').first();
            } catch(ex) {
                qtyElement = null;
            }

            if (qtyElement) {
                var qty = parseInt(qtyElement.val()),
                    decrement = 1;

                if (qty >= decrement) {
                    qty -= decrement;
                    qtyElement.val(qty);
                    qtyElement.trigger('change');
                }
            }
        },

        _plusQtyClick: function (e) {
            try {
                var qtyElement = $(e.target).parent().find('.amconf-matrix-input').first();
            } catch(ex) {
                qtyElement = null;
            }
            if (qtyElement) {
                var qty = parseInt(qtyElement.val()),
                    increment = 1,
                    availableQty = qtyElement.attr('max');

                qty += increment;
                if (!availableQty || availableQty >= qty) {
                    qtyElement.val(qty);
                    qtyElement.trigger('change');
                }
            }
        },

        _clearProductMatrixInputs: function() {
            $('[name="amconfigurable-option[]"]').remove();
            $('.amconf-matrix-input').val(0).trigger('change');
            this.fullSubtotal = 0;
            $('.amconf-matrix-full-subtotal span').text(this._convertPrice(this.fullSubtotal));
        },

        _observeAddToCart: function () {
            var $widget = this;
            $(document).on('ajax:addToCart', function () {
                $widget._clearProductMatrixInputs();
            });
        },

        _showDefaultQty: function () {
            var qtyElement = $(this.selectors['qty-block']);
            qtyElement.show();
        },

        _hideDefaultQty: function () {
            var qtyElement = $(this.selectors['qty-block']);
            qtyElement.hide();
        },

        _removeDefaultQty: function () {
            var qtyElement = $(this.selectors['qty-block']);
            qtyElement.remove();
        },

        /**
         * Determine product id and related data
         *
         * @returns {{productId: *, isInProductView: bool}}
         * @private
         */
        _determineProductData: function () {
            // Check if product is in a list of products.
            var productId,
                isInProductView = false;

            productId = this.element.parents('.product-item-details')
                .find('.price-box.price-final_price').attr('data-product-id');

            if (!productId) {
                // Check individual product.
                //in default magento function there is an invalid selector productId = $('[name=product]').val();
                productId = this.element.parents('.product-item-details, .product-info-main')
                    .find('[name=product]').val();
                isInProductView = productId > 0;
            }

            return {
                productId: productId,
                isInProductView: isInProductView
            };
        },

        _saveQty: function (e) {
            var id = 'amconfigurable-option-',
                value,
                elementParams = e.name.match(/\d+/g),
                elementAttr = elementParams[0],
                elementId = elementParams[1],
                attributeArray = this._getOptionSelected();
            attributeArray.forEach(function (attributeId, optionSelected, attributeArray) {
                if (elementAttr != optionSelected) {
                    id += attributeId + '-';
                }
            });
            id += elementId;
            value = this._getInputValue(e);
            this._createInput(id, value);
        },

        _createInput: function (id, value) {
            var input = $('<input>', {
                'id': id,
                'name' : 'amconfigurable-option[]',
                'type': 'hidden',
                'value': value
            });

            if (this.productBlock.find('#' + id).length == 0) {
                this.productBlock.find('.amconf-matrix-observed').after(input);
            } else {
                this.productBlock.find('#' + id).val(value);
            }
        },

        _getInputValue: function (e) {
            var value = {},
                elementParams = e.name.match(/\d+/g),
                elementAttr = elementParams[0],
                elementId = elementParams[1],
                attributeArray = this._getOptionSelected();
            attributeArray.forEach(function (attributeId, optionSelected, attributeArray) {
                value[optionSelected] = attributeId;
            });
            value['qty'] = e.value;
            value[elementAttr] = elementId;

            return JSON.stringify(value);
        },

        _getDefaultQty: function (attrId) {
            var id = this._generateId(),
                value,
                qty = 0;
            id += '-' + attrId;
            if (this.productBlock.find('input[id^=' + id + ']').length == 1) {
                value = JSON.parse(this.productBlock.find('input[id^=' + id + ']')[0].value);
                qty = value['qty'];
            }

            return qty;
        },

        _generateId: function () {
            var selecteOptionsId = 'amconfigurable-option',
                attributeArray = this._getOptionSelected(),
                lastAttribute = $('.swatch-attribute').last().attr('attribute-id');
            attributeArray.forEach(function (attributeId, optionSelected, attributeArray) {
                if (optionSelected != lastAttribute) {
                    selecteOptionsId += '-' + attributeId;
                }
            });

            return selecteOptionsId;
        },

        _getOptionSelected: function () {
            var value = [];
            this.productBlock.find('.swatch-attribute').each( function (key, option) {
                var selectedOption = option.getAttribute('option-selected'),
                    attribute = option.getAttribute('attribute-id');
                if (selectedOption && attribute) {
                    value[attribute] = selectedOption;
                }
            });

            return value;
        },
        
        _replaceHashParams: function (attributeHash, attributeCode) {
            var hash = window.location.hash;

            hash = hash.indexOf(attributeHash) != -1
                ? hash.replace(new RegExp("(&" + attributeHash + ")|(" + attributeHash + "&?)"), "")
                : hash.replace(new RegExp(attributeCode + '=' + '[0-9]+'), attributeHash);

            return hash;
        },
        
        _setHash: function (hash) {
            window.location.replace(window.location.href.split('#')[0] + hash);
            $('.amconf-share-input').prop('value', window.location);
        }
    });

    return $.amasty_conf.SwatchRenderer;
});

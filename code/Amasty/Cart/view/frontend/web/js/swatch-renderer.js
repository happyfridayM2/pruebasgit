define([
    'jquery'
], function ($) {
    'use strict';

    return function (widget) {
        $.widget('mage.SwatchRenderer', widget, {
            _RenderFormInput: function (config) {
                var formInput = this._super(config),
                    useMatrix = this.ajaxCart
                        || $('body.catalog-product-view').length > 0
                        && this.options.jsonConfig.matrix;

                if (this.inProductList
                    && (
                        this.element.closest('#confirmBox').length == 0
                        || (useMatrix
                        && config.id == this.options.jsonConfig.attributes[this.options.jsonConfig.attributes.length - 1].id)
                    )
                ) {
                    formInput = $(formInput).attr('aria-required', 'false')[0].outerHTML;
                    formInput = formInput.replace(/(required:\s*)true/, '$1false');
                }

                return formInput;
            },

            _UpdatePrice: function () {
                this._super();
                if (!$('.amcart-minipage-wrap .swatch-attribute:not([option-selected])').length) {
                    $('.am-price .normal-price .price-label').hide();
                }

                return null;
            }
        });

        return $.mage.SwatchRenderer;
    }
});

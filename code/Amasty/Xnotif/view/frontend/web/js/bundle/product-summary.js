define(
    [
        'jquery',
        'mage/template',
        'magento-bundle.product-summary'
    ],
    function ($, mageTemplate ) {
        'use strict';

        $.widget('amasty_xnotif.productSummary', $.mage.productSummary, {
            _renderSummaryBox: function (event, data) {
                this._super(event, data);
                this._checkAddToCartButton();
            },

            _renderOptionRow: function (key, optionIndex) {
                var template;

                template = this.element
                    .closest(this.options.summaryContainer)
                    .find(this.options.templates.optionBlock)
                    .html();

                var item = this.cache.currentElement.options[this.cache.currentKey].selections[optionIndex];

                template = mageTemplate($.trim(template), {
                    data: {
                        _quantity_: item.qty,
                        _label_: item.name
                    }
                });
                /*amasty functionality for showing stock alert*/
                template += this._getSubscribtionHtml(item.optionId);

                this.cache.summaryContainer
                    .find(this.options.optionSelector)
                    .append(template);
            },

            _getSubscribtionHtml: function (optionId) {
                var html = '';
                var config = window.amxnotif_json_config;

                if (config[optionId]
                   && config[optionId].is_salable == 0
                ) {
                    html += window.amxnotif_json_config[optionId].alert;
                    html = html.replace('stock link-stock-alert', 'stockalert link-stock-alert');//show for logged it
                }

                return html;
            },

            _checkAddToCartButton: function () {
                var status = $('form.amxnotif-block').length;
                status = status? true: false;
                $('#product-addtocart-button').attr('disabled', status);
            }

        });
    }
);

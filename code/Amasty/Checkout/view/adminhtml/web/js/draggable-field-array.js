define([
    'jquery',
    'Magento_Theme/js/sortable'
], function ($) {
    'use strict';

    $.widget('mage.amastyDraggableFieldArray', {
        options: {
            rowsContainer: '[data-role="row-container"]',
            orderInput: '[data-amcheckout-sortable="true"] [data-role="sort-order"]',
            checkoutDesignSelect: '#amasty_checkout_design_layout_checkout_design',
            checkoutLayoutSelect: '#amasty_checkout_design_layout_layout_modern',
            orderSummarySortableBlock: '[data-amcheckout-js="block_order_summary"]'
        },

        /**
         * Initialize widget
         */
        _create: function() {
            var rowsContainer = this.element.find(this.options.rowsContainer),
                useWebsiteCheckbox = rowsContainer.parents('td').siblings('.use-default').find('input[type="checkbox"]');

            this.orderSummaryByDesign();
            this.initSortable();

            if(useWebsiteCheckbox.length) {
                useWebsiteCheckbox.on('change', this.toggleSortable.bind(this));
                this.toggleSortable('change', useWebsiteCheckbox);
            }

            $(this.options.checkoutDesignSelect + ', ' + this.options.checkoutLayoutSelect).on('change', function () {
                this.orderSummaryByDesign();
                this.initSortable();
            }.bind(this));
        },

        initSortable: function () {
            var rowsContainer = this.element.find(this.options.rowsContainer);

            if (rowsContainer.data('sortable')) {
                rowsContainer.sortable('destroy');
            }

            rowsContainer.sortable({
                tolerance: 'pointer',
                items: '[data-amcheckout-sortable="true"]',
                axis: 'y',
                update: this.recollectBlocksOrder.bind(this, rowsContainer)
            });

            this.recollectBlocksOrder(rowsContainer);
        },

        toggleSortable: function (event, input) {
            var checkbox = (input) ? input : $(event.target),
                sortableElement = checkbox.parents('td').siblings('.value').find(this.options.rowsContainer),
                inherit = $('#block_management_inherit');

            sortableElement.sortable({
                disabled: checkbox.prop('checked')
            });

            inherit.val(+checkbox.prop('checked'));
            sortableElement.find('input').prop('disabled', checkbox.prop('checked'))
        },

        orderSummaryByDesign: function () {
            var designSelect = $(this.options.checkoutDesignSelect),
                layoutSelect = $(this.options.checkoutLayoutSelect),
                orderSummary = $(this.options.orderSummarySortableBlock),
                isModern2Columns = designSelect.val() == '1' && layoutSelect.val() == '2columns';

            orderSummary
                .toggleClass('-unsortable', isModern2Columns)
                .attr('data-amcheckout-sortable', !isModern2Columns)
                .find('[data-role="sort-order"]').val('');
            this.initSortable();
        },

        recollectBlocksOrder: function (sortable) {
            sortable.find(this.options.orderInput).each(function (index, element) {
                $(element).val(index);
            });
        }
    });

    return $.mage.amastyDraggableFieldArray;
});

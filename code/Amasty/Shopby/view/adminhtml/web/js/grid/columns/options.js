define([
    'jquery',
    'mage/translate',
    'Magento_Ui/js/grid/columns/column'
], function ($, $t, Column) {
    'use strict';

    // @codingStandardsIgnoreLine
    function parseOptions(nodes) {
        var value;
        var index = 0;
        nodes = _.map(nodes, function (node) {
            value = node.value;
            if (typeof node.checked == 'undefined') {
                node.checked = 0;
                node.sort_order = ++index;
            }
            return node;
        });

        return nodes;
    };

    return Column.extend({
        defaults: {
            bodyTmpl: 'Amasty_Shopby/grid/cells/options',
            fieldClass: {
                'data-grid-options-td': true
            },

        },
        initConfig: function (config) {
            this.params = [];
            var options = config.options,
                result = parseOptions(options);
            this.params = result;
            _.extend(config, {options: result });

            this._super();

            return this;
        },

        initObservable: function () {

            this._super();
            return this;
        },

        getList: function (record) {

            this.options = [];
            this.code = 0;
            var self = this;
            var data = $.parseJSON(record[this.index]);
            if (data.type == 'option') {
                _.each(this.params, function (option) {
                    var newOption = _.clone(option);
                    _.each(data.items, function (value, index) {
                        if (value.code == newOption.code && value.id == newOption.id) {
                            newOption.checked = 1;
                            newOption.type_group = data.type;
                            newOption.sort_order = value.sort_order;
                            self.options.push(newOption);
                        }
                    });
                });
            } else {
                _.each(data.items, function (value, index) {
                    var newOption = _.clone(value);
                        newOption.checked = 1;
                        newOption.type_group = data.type;
                        newOption.label = value.value;
                        newOption.sort_order = value.sort_order;
                        self.options.push(newOption);
                });
            }
            this.options = _.sortBy(this.options,'sort_order');

            return this.options;
        }
    });
});

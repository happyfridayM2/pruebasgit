define([
    'jquery',
    'underscore',
    'mageUtils',
    'uiLayout',
    'mage/translate',
    'ko',
    'Magento_Ui/js/form/element/ui-select'
], function ($, _, utils, layout, $t, ko, Select) {
    'use strict';

    return Select.extend({
        defaults: {
            sortion: {
                distance: 8,
                tolerance: 'pointer',
                cancel: 'select, input',
                axis: 'y'
            },
        },
        initialize: function () {
            var self = this;
            this._super();
            this.type = null;
            this.sortion.update = this.updateSort;
            var params = this.options();
            var data = $.parseJSON(this.value());
            this.type = data.type;
            var array = [];
            if (data.items) {
                if (this.type == 'option') {
                    _.each(params, function (option) {
                        var newOption = _.clone(option);
                        newOption.type_group = self.type;
                        var index = 0;
                        if (newOption.code == data.code) {
                            _.each(data.items, function (value) {
                                if (value.code == newOption.code && value.id == newOption.id) {
                                    newOption.checked = 1;
                                    //newOption.sort_order = value.sort_order;
                                    if (index >= value.sort_order) {
                                        index += (value.sort_order - index);
                                    }
                                }
                            });
                            newOption.sort_order = ++index;
                            array.push(newOption);
                        }
                    });

                } else {
                    _.each(data.items, function (option) {
                        var newOption = _.clone(option);
                        newOption.checked = 1;
                        newOption.type_group = self.type;
                        array.push(newOption);

                    });
                }
            } else {
                var index = 0;
                this.type = 'option';
                _.each(params, function (option) {
                    var newOption = _.clone(option);
                    if (newOption.code == data.code) {
                        newOption.type_group = 'option';
                        newOption.sort_order = ++index;
                        array.push(newOption);
                    }
                });
            }
            this.changeOptions(array);
            return this;
        },

        isChecked: function (value) {
            return value ? true : false;
        },

        isType: function (value) {
            return (value > 0) ? true : false;
        },
        initConfig: function (config) {
            this._super();
        },
        toggleOptionSelected: function (data) {
            var array = [];
            _.each(this.options(), function (option) {
                var newOption = _.clone(option);
                if (newOption.id == data.id) {
                    newOption.checked = (data.checked) ? 0 : 1;
                }
                array.push(newOption);
            });
            this.changeOptions(array);
            $('[data-role=options-container]').sortable(this.sortion);
            return this;
        },

        changeOptions: function (array) {
            array = _.sortBy(array, 'sort_order');
            this.options(array);
            this.value(this.options());

        },
        toggleListVisible: function () {
            this._super();
            var array = this.updateList();
            this.changeOptions(array);
            $('[data-role=options-container]').sortable(this.sortion);
            return this;
        },
        onFocusOut: function () {
            this._super();
            var array = this.updateList();
            this.changeOptions(array);
            $('[data-role=options-container]').sortable(this.sortion);
            return this;
        },
        updateSort: function (event, ui) {
            $('[data-role=options-container] [data-role=options]').each(function (index, element) {
                $(element).val(index + 1);
            });
        },
        updateList: function () {
            var array = _.clone(this.options());
            if (this.type == 'option') {
                $('[data-role=options-container] [data-role=options]').each(function (index, element) {
                    _.each(array, function (option) {
                        if (option.id == $(element).attr("data-id")) {
                            option.sort_order = $(element).val();
                        }
                    });
                });
            } else {
                $('.list-value').each(function (index, element) {
                    _.each(array, function (option) {
                        if (option.id == $(element).attr("data-id")) {
                            option.value = $(element).val();
                        }
                    });
                });
            }

            return array;
        },
        onUpdate: function () {
            this._super();
        },
    });

});

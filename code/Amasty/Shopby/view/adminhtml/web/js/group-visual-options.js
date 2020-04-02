define(
    [
        'jquery',
        'mage/template',
        'mage/translate',
        'underscore',
        'prototype',
        'form',
        'validation'
    ], function (jQuery, mageTemplate, $t,_) {
        'use strict';
        var attributeOption = {
            table: $('dispatch_options-table'),
            itemCount: 0,
            options: null,
            selected: null,
            selectedAttribute: null,
            selectType: null,
            config: {
                attributesData: null,
                isSortable: 1,
                isReadOnly: 1
            },
            rendered: 0,
            templateSelect: mageTemplate('#row-template'),
            templatePrice: mageTemplate('#value-template'),
            init: function (options, values) {
                this.options = options;
                this.selected = values.length ? jQuery.parseJSON(values) : '';

                if (attributeOption.rendered) {
                    return false;
                }

                this.createData('#group_attribute_id');
            },
            add: function (data, render) {
                var element;
                var template;

                if (this.selectType == 'price') {
                   template = this.templatePrice;
                } else {
                    template = this.templateSelect;
                }

                element = template({
                    data: data
                });
                this.itemCount++;
                this.elements += element;

                if (render) {
                    this.render();
                }
            },

            selectItem: function (element) {
                jQuery(
                    'select[name="attribute_options['
                    + element['id'] + '][is_active][value]"] [value='
                    + element['is_active'] + ']'
                ).attr("selected", "selected");
            },

            render: function () {
                if (this.selectType == 'price') {
                    Element.insert($$('[data-role=values-container]')[0], this.elements);
                } else {
                    Element.insert($$('[data-role=options-container]')[0], this.elements);
                }

                this.elements = '';
            },

            renderWithDelay: function (data) {
                var self = this;

                _.each(data, function (element, index) {
                    if (typeof element != 'undefined') {
                        element['checked'] = "";
                        if (element['is_active']) {
                            element['checked'] = 'checked="checked"';
                        }
                        self.add(element);
                    }
                });
                this.render();

                _.each(data, function (element, index) {
                    if (typeof element != 'undefined') {
                        self.selectItem(element);
                    }
                });

                if (this.config.isSortable) {
                    jQuery(function ($) {
                        $('[data-role=options-container]').sortable({
                            distance: 8,
                            tolerance: 'pointer',
                            cancel: 'select, input',
                            axis: 'y',
                            update: function () {
                                $('[data-role=options-container] [data-role=options]').each(function (index, element) {
                                    $(element).val(index + 1);
                                });
                            }
                        });
                    });
                }
            },

            createData: function(element)
            {
                var attribute = jQuery(element).val();
                this.selectedAttribute = attribute;
                this.scope(attribute);
                this.renderWithDelay(this.config.attributesData);

                if (this.config.attributesData[0].swatch) {
                    jQuery('.swatch_option').addClass('swatch_option-cursor');
                }

                this.viewTable();
                jQuery(element).on('change', this.changeOptions.bind(this));
            },

            scope:function(attribute) {
                var data = this.options[attribute].options;
                this.selectType = this.options[attribute].type;
                this.config.attributesData = [];

                if (this.selectType == 'price') {
                    var sortData = this.scopePrice();
                } else {
                    var sortData = _.sortBy(this.scopeSelect(data), 'sort_order');
                }

                this.config.attributesData = sortData;
            },

            changeOptions: function(el)
            {
                var attribute = jQuery(el.target).val();
                this.selected = [];
                this.scope(attribute);
                _.each($$('[data-role=options-container]')[0].childElements(),function(value, index) {
                  value.remove();
                });
                _.each($$('[data-role=values-container]')[0].childElements(),function(value, index) {
                    value.remove();
                });
                this.viewTable();

                this.renderWithDelay(this.config.attributesData);

                if (this.config.attributesData[0].swatch) {
                    jQuery('.swatch_option').addClass('swatch_option-cursor');
                }
            },

            getSelected: function(option_id)
            {
                var selected = {'find': false};
                _.each(this.selected, function(value, index) {
                    if (value.option_id == option_id) {
                        selected = value;
                        selected['find'] = true;
                    }
                });

                return selected;
            },
            scopeSelect: function(data) {
                var newData = [];
                var self = this;
                _.each(data, function(value, index) {
                    if (value.value.length > 0) {
                        var swatch = value.swatch;
                        var swatchValue;

                        if (value.type == 1) {
                            swatchValue = 'style=background:'+swatch;
                        }

                        if (value.type == 2) {
                            swatchValue = 'style=background-image:url('+swatch +');background-size:cover';
                        }

                        var select = self.getSelected(value.id);
                        newData[value.value] = {'id': value.id, 'sort_order': (select.find) ? select.sort_order : index, 'is_active': (select.find) ? 1 : 0, 'value': value.label, swatch:swatchValue}
                    }
                });

                return newData;
            },

            scopePrice: function() {
               
                var data  = [];

                if ((typeof  this.selected == 'object') && (this.selected.length)) {
                    data = this.selected;
                    _.each(data, function (value, key) {
                        if (!_.findKey(value, "id")) {
                            value.id = key;
                        }
                        if (value.sort_order == 1) {
                            value.text = $t('From');
                        } else if (value.sort_order == 2) {
                            value.text = $t('To');
                        }
                        value.is_active = 1;
                    })

                } else {
                    data.push({'sort_order': 1, id: 1, text: $t('From'), 'is_active': 0, 'value': ''});
                    data.push({'sort_order': 2, id: 2, text: $t('To'), 'is_active': 0, 'value': ''});
                }

                return data;
            },
            viewTable: function()
            {
                if (this.selectType == 'price') {
                    $$('[data-index=dispatch_options_select]')[0].hide();
                    $$('[data-index=dispatch_values_select]')[0].show();
                } else {
                    $$('[data-index=dispatch_options_select]')[0].show();
                    $$('[data-index=dispatch_values_select]')[0].hide();
                }
            }
        };

       return attributeOption;
    });

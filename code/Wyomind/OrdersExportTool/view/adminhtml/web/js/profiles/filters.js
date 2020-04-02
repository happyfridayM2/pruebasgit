define(["jquery"], function ($) {
    'use strict';
    return {
        waitFor: function (elt, callback) {
            var initializer = null;
            initializer = setInterval(function () {
                if ($(elt).length > 0) {
                    setTimeout(callback, 500);
                    clearInterval(initializer);
                }
            }, 200);
        },
        updateStates: function () {
            var values = new Array();
            $('.state').each(function (i) {
                if ($(this).prop('checked')) {
                    values.push($(this).attr('identifier'));
                }
            });
            $('#states').val(values.join());
            this.updateUnSelectLinksStates();
        },
        loadStates: function () {
            this.waitFor("#states", function () {
                var values = $('#states').val();
                values = values.split(',');
                values.each(function (v) {
                    $('#state_' + v).prop('checked', true);
                    $('#state_' + v).parent().addClass('selected');
                });
            });
        },
        updateCustomerGroups: function () {
            var values = new Array();
            $('.customer_group').each(function (i) {
                if ($(this).prop('checked')) {
                    values.push($(this).attr('identifier'));
                }
            });
            $('#customer_groups').val(values.join());
            this.updateUnSelectLinksCustomerGroups();
        },
        loadCustomerGroups: function () {
            this.waitFor("#customer_groups", function () {
                var values = $('#customer_groups').val();
                values = values.split(',');
                values.each(function (v) {
                    $('#customer_group_' + v).prop('checked', true);
                    $('#customer_group_' + v).parent().addClass('selected');
                });
            });
        },
        isAllStatesSelected: function () {
            var all = true;
            $(document).find('.state').each(function () {
                if ($(this).prop('checked') === false)
                    all = false;
            });
            return all;
        },
        isAllCustomerGroupsSelected: function () {
            var all = true;
            $(document).find('.customer_group').each(function () {
                if ($(this).prop('checked') === false)
                    all = false;
            });
            return all;
        },
        updateUnSelectLinks: function () {
            this.updateUnSelectLinksStates();
            this.updateUnSelectLinksCustomerGroups();
        },
        updateUnSelectLinksStates: function () {
            if (this.isAllStatesSelected()) {
                $('#states-selector').find('.select-all').removeClass('visible');
                $('#states-selector').find('.unselect-all').addClass('visible');
            } else {
                $('#states-selector').find('.select-all').addClass('visible');
                $('#states-selector').find('.unselect-all').removeClass('visible');
            }
        },
        updateUnSelectLinksCustomerGroups: function () {
            if (this.isAllCustomerGroupsSelected()) {
                $('#customer-groups-selector').find('.select-all').removeClass('visible');
                $('#customer-groups-selector').find('.unselect-all').addClass('visible');
            } else {
                $('#customer-groups-selector').find('.select-all').addClass('visible');
                $('#customer-groups-selector').find('.unselect-all').removeClass('visible');
            }
        },
        selectAll: function (elt) {
            var fieldset = elt.parents('.fieldset')[0];
            $(fieldset).find('input[type=checkbox]').each(function () {
                $(this).prop('checked', true);
                $(this).parent().addClass('selected');
            });
            this.updateStates();
            this.updateCustomerGroups();
            elt.removeClass('visible');
            $(fieldset).find('.unselect-all').addClass('visible');
        },
        /**
         * Click on unselect all link
         * @param {type} elt
         * @returns {undefined}
         */
        unselectAll: function (elt) {
            var fieldset = elt.parents('.fieldset')[0];
            $(fieldset).find('input[type=checkbox]').each(function () {
                $(this).prop('checked', false);
                $(this).parent().removeClass('selected');
            });
            this.updateStates();
            this.updateCustomerGroups();
            elt.removeClass('visible');
            $(fieldset).find('.select-all').addClass('visible');
        },
        loadAdvancedFilters: function () {
            this.waitFor("#attributes", function () {
                if ($('#attributes').val() == "") {
                    $('#attributes').val("[]");
                }
                var filters = $.parseJSON($('#attributes').val());
                if (filters === null) {
                    filters = new Array();
                    $('#attributes').val(JSON.stringify(filters));
                }
                var counter = 0;
                while (filters[counter]) {
                    var filter = filters[counter];

                    $('#attribute_' + counter).prop('checked', filter.checked);
                    $('#name_attribute_' + counter).val(filter.code);
                    $('#value_attribute_' + counter).val(filter.value);
                    $('#condition_attribute_' + counter).val(filter.condition);
                    // this.updateRow(counter, filter.code);
                    $('#name_attribute_' + counter).prop('disabled', !filter.checked);
                    $('#condition_attribute_' + counter).prop('disabled', !filter.checked);
                    $('#value_attribute_' + counter).prop('disabled', !filter.checked);
                    $('#pre_value_attribute_' + counter).prop('disabled', !filter.checked);
                    $('#pre_value_attribute_' + counter).val(filter.value);
                    counter++;
                }
            });
        },
        updateAdvancedFilters: function () {
            var newval = {};
            var counter = 0;
            $('.advanced_filters').each(function () {
                var checkbox = $(this).find('#attribute_' + counter).prop('checked');
                // is the row activated
                if (checkbox) {
                    $('#name_attribute_' + counter).prop('disabled', false);
                    $('#condition_attribute_' + counter).prop('disabled', false);
                    $('#value_attribute_' + counter).prop('disabled', false);
                } else {
                    $('#name_attribute_' + counter).prop('disabled', true);
                    $('#condition_attribute_' + counter).prop('disabled', true);
                    $('#value_attribute_' + counter).prop('disabled', true);
                }

                var name = $(this).find('#name_attribute_' + counter).val();
                var condition = $(this).find('#condition_attribute_' + counter).val();
                var value = $(this).find('#value_attribute_' + counter).val();
                var val = {checked: checkbox, code: name, condition: condition, value: value};
                newval[counter] = val;
                counter++;
            });
            $('#attributes').val(JSON.stringify(newval));
        },
        updateRow: function (id, attribute_code) {
            $('#name_attribute_' + id).parent().removeClass('multiple-value').addClass('one-value').removeClass('dddw');
            if ($('#condition_attribute_' + id).val() === "null" || $('#condition_attribute_' + id).val() === "notnull") {
                $('#value_attribute_' + id).css('display', 'none');
            } else {
                $('#value_attribute_' + id).css('display', 'inline');
            }
        }
    };
});
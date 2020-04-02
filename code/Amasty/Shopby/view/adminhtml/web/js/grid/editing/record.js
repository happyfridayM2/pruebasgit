
define([
    'underscore',
    'mageUtils',
    'uiLayout',
    'Magento_Ui/js/grid/editing/record'
], function (_, utils, layout, Record) {
    'use strict';

    return Record.extend({
        defaults : {
            templates: {
                fields: {
                    options: {
                        component: 'Amasty_Shopby/js/form/element/ui-select',
                        template: 'Amasty_Shopby/form/element/options',
                        options: '${ JSON.stringify($.$data.column.params) }'
                    }
                }
            }
        },
    });
});

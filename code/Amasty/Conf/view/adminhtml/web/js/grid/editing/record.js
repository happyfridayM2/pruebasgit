
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
                                 component: 'Amasty_Conf/js/form/element/ui-select',
                                 template: 'Amasty_Conf/form/element/options',
                                 options: '${ JSON.stringify($.$data.column.params) }'
                    }
                }
            }
        },
    });
});

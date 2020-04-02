define([
    'jquery',
    'underscore',
    'mageUtils',
    'uiLayout',
    'mage/translate',
    'Magento_Ui/js/grid/editing/editor'
], function ($, _, utils, layout, $t, Editor) {
    'use strict';

    return Editor.extend({
        defaults: {
            templates: {
                record: {
                    component: 'Amasty_Shopby/js/grid/editing/record'
                },
            },
        }
    });
});

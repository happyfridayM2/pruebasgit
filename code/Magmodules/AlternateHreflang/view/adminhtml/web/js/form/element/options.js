define([
    'underscore',
    'uiRegistry',
    'Magento_Ui/js/form/element/select'
], function (_, uiRegistry, select) {
    'use strict';
    return select.extend({
        onUpdate: function (value) {
            var newCategory = uiRegistry.get('index = alternate_category_new');
            if (value == 'alternate_category_new') {
                newCategory.show();
            } else {
                newCategory.hide();
            }
            return this._super();
        }
    });
});
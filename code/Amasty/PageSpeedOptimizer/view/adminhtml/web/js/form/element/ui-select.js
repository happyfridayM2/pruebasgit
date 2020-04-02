define([
    'Magento_Ui/js/form/element/ui-select',
    'underscore',
    'jquery'
], function (UiSelect, _, $) {
    function flattenCollection(array, separator, created) {
        var i = 0,
            length,
            childCollection;

        array = _.compact(array);
        length = array.length;
        created = created || [];

        for (i; i < length; i++) {
            created.push(array[i]);

            if (array[i].hasOwnProperty(separator)) {
                childCollection = array[i][separator];
                delete array[i][separator];
                flattenCollection.call(this, childCollection, separator, created);
            }
        }

        return created;
    }

    return UiSelect.extend({
        initialize: function () {
            var ajaxData = {isAjax: true};

            this._super();

            if (!_.isUndefined(this.source.data.image_setting_id)) {
                ajaxData.image_setting_id = this.source.data.image_setting_id;
            }

            this.disabled(true);
            $.ajax({
                url: this.foldersUrl,
                data: ajaxData,
                method: 'post',
                global: false,
                dataType: 'json',
                success: function (data) {
                    if (!_.isEmpty(data)) {
                        this.disabled(false);
                        this.options(data);
                        this.cacheOptions.plain = flattenCollection(JSON.parse(JSON.stringify(data)), 'optgroup');
                        if (!_.isEmpty(this.value())) {
                            this.value(this.value());
                        }
                    }
                }.bind(this)
            });

            return this;
        },
        toggleOptionSelected: function (data) {
            if (_.isUndefined(data.disabled) || data.disabled == false) {
                this._super();
            }
        }
    });
});

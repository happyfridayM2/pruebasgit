define([
    'jquery',
    'underscore',
    'Magento_Ui/js/form/element/ui-select'
], function ($, _, UiSelect) {
    'use strict';

    var isTouchDevice = typeof document.ontouchstart !== 'undefined';

    /**
     * Processing options list
     *
     * @param {Array} array - Property array
     * @param {String} separator - Level separator
     * @param {Array} created - list to add new options
     *
     * @return {Array} Plain options list
     */
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

    /**
     * Set levels to options list
     *
     * @param {Array} array - Property array
     * @param {String} separator - Level separator
     * @param {Number} level - Starting level
     * @param {String} path - path to root
     *
     * @returns {Array} Array with levels
     */
    function setProperty(array, separator, level, path) {
        var i = 0,
            length,
            nextLevel,
            nextPath;

        array = _.compact(array);
        length = array.length;
        level = level || 0;
        path = path || '';

        for (i; i < length; i++) {
            if (array[i]) {
                _.extend(array[i], {
                    level: level,
                    path: path
                });
            }

            if (array[i].hasOwnProperty(separator)) {
                nextLevel = level + 1;
                nextPath = path ? path + '.' + array[i].label : array[i].label;
                setProperty.call(this, array[i][separator], separator, nextLevel, nextPath);
            }
        }

        return array;
    }

    /**
     * Preprocessing options list
     *
     * @param {Array} nodes - Options list
     *
     * @return {Object} Object with property - options(options list)
     *      and cache options with plain and tree list
     */
    function parseOptions(nodes) {
        var caption,
            value,
            cacheNodes,
            copyNodes;

        nodes = setProperty(nodes, 'optgroup');
        copyNodes = JSON.parse(JSON.stringify(nodes));
        cacheNodes = flattenCollection(copyNodes, 'optgroup');

        nodes = _.map(nodes, function (node) {
            value = node.value;

            if (value == null || value === '') {
                if (_.isUndefined(caption)) {
                    caption = node.label;
                }
            } else {
                return node;
            }
        });

        return {
            options: _.compact(nodes),
            cacheOptions: {
                plain: _.compact(cacheNodes),
                tree: _.compact(nodes)
            }
        };
    }

    return UiSelect.extend({

        defaults: {
            elementTmpl: 'ui/grid/filters/elements/ui-select',
            imports: {
                type: "${ $.provider }:${ $.parentScope }.type"
            },
            listens: {
                type: "onTypeChanged"
            },
            modules: {
                linkTitle: "${ $.parentName }.title"
            },
            recordDataCache: {}
        },

        initObservable: function () {
            this._super();
            this.observe([
                'label',
                'filterPlaceholder'
            ]);

            return this;
        },

        onUpdate: function() {
            this._super();
            if (this.getSelected().length == 1 && this.linkTitle() && !this.linkTitle().value()) {
                this.linkTitle().value(this.getSelected()[0].label);
            }
        },

        /**
         * Get path to current option
         *
         * @param {Object} data - option data
         * @returns {String} path
         */
        getPath: function (data) {
            var pathParts,
                createdPath = '';

            if (this.renderPath && data.path) {
                pathParts = data.path.split('.');
                _.each(pathParts, function (curData) {
                    createdPath = createdPath ? createdPath + ' / ' + curData : curData;
                });

                return createdPath;
            }
        },

        onTypeChanged: function(type) {
            if (!type) return;
            var self = this;
            var value = this.value();
            if (!this.recordDataCache[type]) {
                var result = {
                    options: {},
                    cacheOptions: {
                        plain: [],
                        tree: []
                    }
                };
                switch(type) {
                    case 'page':
                        result = parseOptions(this.pages);
                        this.label('Page');
                        this.filterPlaceholder('Search page by name');
                    break;

                    case 'category':
                        result = parseOptions(this.categories);
                        this.label('Category');
                        this.filterPlaceholder('Search category by name');
                    break;

                    case 'product':
                        var value = window.mgzlinkbuilder.id;
                        if (value) {
                            this.value('');
                            $.ajax({
                                url: this.productUrl,
                                type: 'POST',
                                data: {
                                    key: value
                                },
                                success: function(response) {
                                    result = parseOptions(response);
                                    self.options(result.options);
                                    self.cacheOptions.plain = result.cacheOptions.plain;
                                    self.cacheOptions.tree  = result.cacheOptions.tree;
                                    self.setCaption();
                                    self.value(value);
                                }
                            });
                        }
                        this.label('Product');
                        this.filterPlaceholder('Search product by name');
                    break;
                }
                this.options(result.options);
                this.cacheOptions.plain = result.cacheOptions.plain;
                this.cacheOptions.tree  = result.cacheOptions.tree;
                this.setCaption();
            } else {
                this.options(this.recordDataCache[type]);
            }
        },

        filterOptionsList: function() {
            if (this.type == 'product') {
                var self  = this;
                var value = this.filterInputValue().trim().toLowerCase();
                if (value.length >= 1) {
                    $.ajax({
                        url: this.productUrl,
                        type: 'POST',
                        data: {
                            key: value
                        },
                        success: function(response) {
                            var result = parseOptions(response);
                            self.options(result.options);
                            self.cacheOptions.plain = result.cacheOptions.plain;
                            self.cacheOptions.tree  = result.cacheOptions.tree;
                        }
                    });
                }
            } else {
                this._super();
            }
        }
    })
});
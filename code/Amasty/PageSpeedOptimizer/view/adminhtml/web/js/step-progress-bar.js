define([
    'uiElement',
    'jquery',
    'mage/translate'
], function (Element, $) {
    return Element.extend({
        defaults: {
            filesCount: 0,
            part: 1,
            parts: 0,
            filesPerRequest: 10,
            startUrl: '',
            processUrl: '',
            timeout: null,
            finishUrl: '',
            links: [],
            failedLinks: [],
            selectedStores: [],
            addParam: '',
            isDone: false,
            manualLinks: '',
            isPastProcess: false,
            storeSelect: false,
            indexedStores: {},
            failed: false,
            cloudConfig: '',
            manualPast: false,
            websitesContainer: null,
            isGeneration: false,
            inProgress: false,
            percentage: 0,
            dotCount: 12,
            storesToSelect: [],
            result: null,
            currentFiles: 0
        },
        initialize: function () {
            this._super();

            $(document).on('click', '.amoptimizer-super-bundle', function () {
                $(this).replaceWith('');
            });

            window.addEventListener("message", this.iframeEvent.bind(this), false);
            this.websitesContainer = $('#websites-container');

            return this;
        },
        iframeEvent: function (ev) {
            switch (ev.data) {
                case 'Done!':
                    window.clearTimeout(this.timeout);
                    this.websitesContainer.html('');
                    this.result.resolve();
                    break;
                case 'Working!':
                    window.clearTimeout(this.timeout);
                    this.timeout = window.setTimeout(function () {
                        this.websitesContainer.html('');
                        this.result.reject();
                    }.bind(this), 10000);
                    break;
                case 'Started!':
                    this.timeout = window.setTimeout(function () {
                        this.websitesContainer.html('');
                        this.result.reject();
                    }.bind(this), 10000);
                    break;
                case 'Error!':
                    window.clearTimeout(this.timeout);
                    this.websitesContainer.html('');
                    this.result.reject();
                    break;
            }
        },
        initObservable: function () {
            this._super().observe([
                'inProgress',
                'isGeneration',
                'isDone',
                'cloudConfig',
                'manualPast',
                'storeSelect',
                'filesCount',
                'failed',
                'filesPerRequest',
                'part',
                'isPastProcess',
                'percentage',
                'selectedStores',
                'manualLinks',
                'dotCount',
                'currentFiles'
            ]);

            return this;
        },

        start: function () {
            if (this.inProgress() || this.isGeneration()) {
                return;
            }

            this.isGeneration(true);
            this.isDone(false);
            this.failed(false);
            this.manualLinks('');
            this.failedLinks = [];
            this.cloudConfig = '';
            this.manualPast(false);
            this.isPastProcess(false);
            $.ajax({
                url: this.startUrl,
                data: {'isCloud': $('#amoptimizer_settings_javascript_is_cloud').val()},
                type: 'GET',
                success: function (data) {
                    if (typeof data.links === 'undefined') {
                        alert($.mage.__('Something went wrong. Please reload the page.'));

                        return;
                    }
                    this.addParam = data.add_param;
                    var self = this;
                    if (data.force_proceed) {
                        this.storeSelect(false);

                        _.each(data.links, function (locales) {
                            _.each(locales, function (locale) {
                                _.each(locale, function (store) {
                                    self.links = _.union(self.links, store.urls);
                                });
                            });
                        });
                        this.startOptimization();

                        return;
                    }

                    this.storesToSelect = [];
                    this.selectedStores([]);
                    this.indexedStores = {};
                    _.each(data.links, function (locales, theme) {
                        this.storesToSelect.push(
                            {
                                'title': theme,
                                'data': _.map(locales, function (stores, locale) {
                                    if (!_.isUndefined(stores[0])) {
                                        self.selectedStores().push(stores[0].store_id);
                                    }
                                    self.indexedStores = _.extend(self.indexedStores, _.indexBy(stores, 'store_id'));

                                    return {
                                        'title' : locale,
                                        'data': stores
                                    }
                                })
                            }
                        );
                    }.bind(this));

                    this.storeSelect(true);
                }.bind(this)
            });
        },
        prepareStoreLinks: function () {
            if (!this.selectedStores().length) {
                alert($.mage.__('Select at least one store.'));

                return;
            }
            _.each(this.selectedStores(), function (storeId) {
                this.links = _.union(this.links, this.indexedStores[storeId].urls);
            }.bind(this));

            this.startOptimization();
        },
        startOptimization: function () {
            this.currentFiles(0);
            this.percentage(this.getPercentage());
            this.filesCount(this.links.length);
            this.filesPerRequest(1);
            this.parts = this.links.length;
            this.part(0);
            this.inProgress(true);
            this.isGeneration(false);
            this.optimizeFiles();
        },
        getPercentage: function () {
            if (this.filesCount() === 0) {
                return 0;
            }

            return Math.ceil(this.currentFiles() / this.filesCount() * 100);
        },

        getFilePerRequest: function () {
            var currentFiles = this.part()*this.filesPerRequest();

            if (currentFiles > this.filesCount()) {
                return this.filesCount();
            }

            return currentFiles;
        },

        optimizeFiles: function () {
            this.processPage(this.links[0])
                .done(function () {
                    this.links.shift();
                }.bind(this))
                .fail(function () {
                    this.failedLinks.push(this.links.shift());
                }.bind(this))
                .always(function () {
                    this.part(this.part() + 1);
                    this.currentFiles(this.getFilePerRequest());
                    this.percentage(this.getPercentage());

                    if (this.part() < this.parts) {
                        this.optimizeFiles();
                    } else {
                        if (this.failedLinks.length === 0) {
                            this.pastProcess();
                        } else {
                            this.links = this.failedLinks;
                            this.inProgress(true);
                            this.failed(true);
                            this.isDone(false);
                        }
                    }
                }.bind(this));
        },
        processPage: function (url) {
            this.result = $.Deferred();
            if (this.timeout !== null) {
                clearTimeout(this.timeout);
            }

            this.websitesContainer.html('<iframe src="' + url + '" width="1500" height="1500" onload="window.postMessage(\'Started!\')"></iframe>');

            return this.result.promise();
        },
        pastProcess: function () {
            this.failedLinks = [];
            this.failed(false);
            if (!this.manualPast()) {
                this.inProgress(false);
                this.isDone(false);
                this.isPastProcess(true);

                return;
            }

            this.finish();
        },
        finish: function () {
            if (this.finishUrl !== '') {
                $.ajax({
                    url: this.finishUrl,
                    type: 'GET',
                    success: function (data) {
                        if (!_.isUndefined(data.result)) {
                            this.cloudConfig = data.result;
                        }

                        this.isPastProcess(false);
                        this.inProgress(false);
                        this.isDone(true);
                    }.bind(this)
                });
            } else {
                this.isPastProcess(false);
                this.inProgress(false);
                this.isDone(true);
            }
        },
        pastProcessLinks: function () {
            if (this.manualLinks() !== '') {
                var links = this.manualLinks().split(/\n|\r\n/);
                if (links.length && this.addParam) {
                    this.links = [];
                    _.each(links, function (link) {
                        if (link.indexOf('#') !== -1) {
                            this.links.push(link.substr(0, link.indexOf('#')) + this.addParam + link.substr(link.indexOf('#')));
                        } else {
                            this.links.push(link + this.addParam);
                        }
                    }.bind(this));
                    if (this.links.length) {
                        this.isDone(false);
                        this.failed(false);
                        this.isPastProcess(false);
                        this.currentFiles(0);
                        this.percentage(this.getPercentage());
                        this.filesCount(this.links.length);
                        this.filesPerRequest(1);
                        this.parts = this.links.length;
                        this.part(0);
                        this.inProgress(true);
                        this.isGeneration(false);
                        this.manualPast(true);
                        this.optimizeFiles();

                        return;
                    }
                }
            }

            this.finish();
        },
        removeLink: function (s, ev) {
            $(ev.target).parent().replaceWith('');
        },
        removeGetParam: function (url) {
            return url.substr(0, url.indexOf('?'));
        }
    });
});

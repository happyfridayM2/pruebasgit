define([
    'uiElement',
    'jquery',
    'uiRegistry'
], function (Element, $, registry) {
    return Element.extend({
        defaults: {
            filesCount: 0,
            part: 1,
            parts: 0,
            filesPerRequest: 10,
            forceStart: false,
            startUrl: '',
            processUrl: '',
            isDone: false,
            isGeneration: false,
            inProgress: false,
            percentage: 0,
            dotCount: 12,
            currentFiles: 0
        },

        initObservable: function () {
            this._super().observe([
                'inProgress',
                'isGeneration',
                'isDone',
                'filesCount',
                'filesPerRequest',
                'part',
                'percentage',
                'dotCount',
                'currentFiles'
            ]);

            if (this.forceStart) {
                registry.async("amoptimizer_image_form.amoptimizer_image_form.modal")(function (modal) {
                    modal.openModal();
                    this.start();
                }.bind(this));
            }

            return this;
        },

        start: function () {
            if (this.inProgress() || this.isGeneration()) return;

            this.isGeneration(true);
            this.isDone(false);

            $.ajax({
                url: this.startUrl,
                type: 'GET',
                success: function (data) {
                    this.filesCount(parseInt(data.filesCount));
                    this.filesPerRequest(parseInt(data.filesPerRequest));
                    this.parts = Math.round(this.filesCount() / this.filesPerRequest()) + 1;
                    this.part(0);
                    this.inProgress(true);
                    this.isGeneration(false);
                    this.optimizeFiles();
                }.bind(this)
            });
        },

        getPercentage: function () {
            if (this.filesCount() === 0) return 0;

            return Math.ceil(this.currentFiles() / this.filesCount() * 100);
        },

        getFilePerRequest: function () {
            var currentFiles = this.part()*this.filesPerRequest();

            if (currentFiles > this.filesCount()) return this.filesCount();

            return currentFiles;
        },

        optimizeFiles: function () {
            $.ajax({
                url: this.processUrl,
                data: {
                    limit: this.filesPerRequest
                },
                type: 'GET',
                success: function () {
                    this.part(this.part() + 1);
                    this.currentFiles(this.getFilePerRequest());
                    this.percentage(this.getPercentage());

                    if (this.part() < this.parts) {
                        this.optimizeFiles();
                    } else {
                        this.inProgress(false);
                        this.isDone(true);
                    }
                }.bind(this)
            });
        }
    });
});

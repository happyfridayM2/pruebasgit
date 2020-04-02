define([
    'uiCollection'
], function (Collection) {
    'use strict';

    return Collection.extend({

        /**
         * Initialize adapter handlers.
         *
         * @returns {Object}
         */
        initAdapter: function () {
            // adapter.on({
            //     'reset': this.reset.bind(this),
            //     'save': this.save.bind(this, true, {}),
            //     'saveAndContinue': this.save.bind(this, false, {})
            // }, this.selectorPrefix, this.eventPrefix);

            return this;
        }
    });
});
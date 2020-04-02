define([
    'jquery'
], function ($) {
    'use strict';
    return function (config) {
        var tag = 'script',
            layer = 'dataLayer',
            containerId = config.gtmAccountId;

        $(window).on('google-tag', function () {
            if (containerId) {
                window[layer] = window[layer] || [];
                window[layer].push({
                    'gtm.start': new Date().getTime(), event: 'gtm.js'
                });
                var f = document.getElementsByTagName(tag)[0],
                    j = document.createElement(tag), dl = layer != 'dataLayer' ? '&l=' + layer : '';
                j.async = true;
                j.src =
                    'https://www.googletagmanager.com/gtm.js?id=' + containerId + dl;
                f.parentNode.insertBefore(j, f);
                f.parentNode.removeChild(f.parentNode.querySelectorAll('script[src="' + j.src + '"]')[0]);
            }
        })
    }
});

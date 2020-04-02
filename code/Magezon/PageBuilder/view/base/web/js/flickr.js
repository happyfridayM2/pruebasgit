define([
    'jquery',
    'Magezon_PageBuilder/js/flickr-jquery',
    'Magezon_PageBuilder/vendor/imagesloaded/imagesloaded.pkgd.min',
    'Magezon_PageBuilder/vendor/blueimp/js/blueimp-gallery',
    'Magezon_PageBuilder/vendor/blueimp/js/blueimp-gallery-fullscreen',
    'Magezon_PageBuilder/vendor/blueimp/js/blueimp-gallery-indicator',
    'Magezon_PageBuilder/vendor/blueimp/js/blueimp-gallery-video',
    'Magezon_PageBuilder/vendor/blueimp/js/blueimp-gallery-vimeo',
    'Magezon_PageBuilder/vendor/blueimp/js/blueimp-gallery-youtube'
], function ($, flickrGallery, imagesloaded, blueimp) {
    'use strict';

    $.widget('magezon.flickrGallery', {
        _create: function () {
            this.element.flickr({
                apiKey: this.options.apiKey,
                photosetId: this.options.photosetId,
                loadingSpeed: 38,
                photosLimit: this.options.photosLimit ? this.options.photosLimit : 200,
                colClass: this.options.colClass,
                showPhotoTitle: this.options.showPhotoTitle
            });

            this.element.click(function(event) {
                event = event || window.event;
                var target = event.target || event.srcElement,
                    link = target.src ? target.parentNode : target,
                    options = { index: link, event: event},
                    links = this.getElementsByTagName('a');
                blueimp(links, options);
            });
        }
    });

    return $.magezon.flickrGallery;
});
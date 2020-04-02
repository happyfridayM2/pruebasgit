define([
    "jquery",
    "jquery/ui",
    "Amasty_Conf/vendor/elevatezoom/jquery.elevatezoom.min",
    "Amasty_Conf/vendor/fancybox/jquery.fancybox.min",
    "Amasty_Conf/vendor/slick/slick.min",
    'uiClass'
], function ($, ui, elevatezoom, fancybox, slick, Class) {

    return Class.extend({

        defaults: {
            settings: {},
            config: {},
            startConfig: {},
            mainImageSelector: '#amasty-main-image',
            galleryImagesSelector: '[data-gallery-role="amasty-gallery-images"]',
            galleryContainerSelector: '[data-gallery-role="amasty-gallery-container"]',
            mainImageContainerSelector: '[data-gallery-role="amasty-main-container"]',
            loadingSelector: '[data-gallery-role="gallery-loading"]',
            lensDefaultSettings: {
                zoomType: 'lens',
                lensShape: 'round',
                lensSize: '200',
                borderSize: '1',
                containLensZoom: true
            },
            slidesPerViewMobile: 3,
            sliderHeight: '115px',
            sliderMarginBottom: '30px'
        },

        /**
         * Initializes gallery.
         * @param {Object} config - Gallery configuration.
         * @param {String} element - String selector of gallery DOM element.
         */
        initialize: function (config, element) {
            this.insertPolyfills();
            var self = this,
                gallery = $('[data-gallery-role=gallery-placeholder]', '.column.main');
            this._super();
            this.config = config;
            this.config.origData = this.config.data.slice(0);
            this.config.modifiedData = this.config.origData.slice(0);
            $.each(this.config.modifiedData, function(index, item) {
                this.config.modifiedData[index] = Object.assign({}, item);
                this.config.modifiedData[index]['isMain'] = false;
            }.bind(this));

            this.element = element;
            this.galleryImages = $(this.galleryImagesSelector);

            if (this.isMobileAndTablet()) {
                this.config.options.zoom['zoomWindowPosition'] = 6;//th best position on the bottom of image
                this.config.options.zoom['zoomWindowWidth'] = 270;//the best choise for mobile
                this.config.options.zoom['zoomWindowHeight'] = 270;
            }

            this.generateProductImages();
            this.load();
            $(element).data('zoom_object', this);
            gallery.data('amasty_gallery', true);
            gallery.trigger('amasty_gallery:loaded');
        },

        reload: function (images, gallerySwitchStrategy) {
            var initialImages = [];
            if (!images || _.isEmpty(images[0])) {
                initialImages = this.config.origData;
            } else if (gallerySwitchStrategy == 'prepend') {
                initialImages = this.config.modifiedData;
            }
            this.config.data = $.merge($.merge([], images), initialImages);
            this.showPreloading();
            this.destroyImages();

            this.generateProductImages();
            this.load();
        },

        destroyImages: function () {
            $(this.mainImageContainerSelector).children(':not(.amlabel-position-wrapper)').remove();
            if (this.isMobileAndTablet()) {
                try{
                    this.galleryImages.slick('unslick');
                } catch(e) {
                    //sometimes for slow connections using 'unslick' option could break
                    // js script on a page. It shouldn't work before all content loaded
                }
            } else if (this.config.options.general.carousel) {
                $('#amasty-gallery-container').css({
                    height : this.sliderHeight,
                    'margin-bottom' : this.sliderMarginBottom
                });
            }
        },

        load: function () {
            var element = $(this.mainImageSelector);
            if (element) {
                var generalSettings = this.config.options.general;

                if (generalSettings.zoom || generalSettings.lightbox || this.config.options.zoom.image_change) {
                    this.loadZoom(element);
                }

                if (generalSettings.lightbox) {
                    this.loadLightbox(element);
                }
            } else {
                console.log('There are something wrong. The are not main product image')
            }
        },

        loadZoom: function (element) {
            var self = this,
                generalSettings = this.config.options.general;
            if (this.isMobileAndTablet() && this.config.options.carousel.main_image_swipe) {
                this.config.options.zoom.zoomType = null;
                element.swipeleft(function () {
                    self.swipeMainImage($(this), 'next');
                });

                element.swiperight(function () {
                    self.swipeMainImage($(this), 'prev');
                });
            }

            if (self.isMobileAndTablet() && this.config.options.zoom.zoomType != 'lens' && this.config.options.zoom.zoomType != null) {
                Object.assign(this.config.options.zoom, this.lensDefaultSettings)
            }

            $('.zoomContainer').remove();
            element.elevateZoom(this.config.options.zoom);

            if (self.isMobileAndTablet() && generalSettings.lightbox) {
                $('body').addClass('am-nozoom');
            }

            this.resolveZindex();
        },

        swipeMainImage: function (element, type) {
            var button = $('.slick-' + type),
                swiped = false,
                newImage = null,
                imageData = element.attr('src'),
                smallImage = $('.amasty-gallery-thumb-link[data-image="' + imageData + '"]:not(.slick-cloned)'),
                eventType = this.config.options.zoom.image_change;

            if (button.length) {
                button.trigger('click');
                swiped = true;
                $('.slick-current').trigger(eventType, swiped);
            } else {
                if (smallImage.length) {
                    if (type === 'prev') {
                        newImage = smallImage.prev();
                    } else {
                        newImage = smallImage.next();
                    }
                    swiped = true;
                    newImage.trigger(eventType, swiped);
                }
            }
        },

        isMobileAndTablet: function () {
            return /Android|webOS|iPhone|iPod|BlackBerry|BB|PlayBook|IEMobile|Windows Phone|Kindle|Silk|Opera Mini/i.test(navigator.userAgent);
        },

        loadLightbox: function (element) {
            var self = this,
                galleryObject = element.data('elevateZoom');
            element.parent().addClass('am-custor-pointer');
            element.parent().unbind("click").bind("click", function(e) {
                $.fancybox.open(galleryObject.getGalleryList(), self.config.options.lightbox);
                return false;
            });

            if(this.config.options.general.thumbnail_lignhtbox) {
                var slickStartTransform,
                    slickEndTransform;

                $(this.galleryContainerSelector + ' a').addClass('cursor-pointer').on('mousedown', function(e) {
                    slickStartTransform = $('#amasty-gallery-images .slick-track').css('transform');
                }).on('mouseup', function(e, swiped) {
                    var currentZoomImage = galleryObject.zoomImage;
                    slickEndTransform = $('#amasty-gallery-images .slick-track').css('transform');
                    galleryObject.zoomImage = $(this).data('zoom-image');
                    if (slickStartTransform == slickEndTransform && !swiped) {
                        $.fancybox.open(galleryObject.getGalleryList(), self.config.options.lightbox);
                    }
                    galleryObject.zoomImage = currentZoomImage;
                });
            }
        },

        resolveZindex: function () {
            var observer = new MutationObserver(subscriber),
                config = {
                    attributes: false,
                    characterData: false,
                    childList: true,
                    subtree: true,
                    attributeOldValue: false,
                    characterDataOldValue: false
                };

            function subscriber(mutations) {
                mutations.forEach(function (mutation) {
                    if (mutation.target.className === 'zoomContainer'
                          && mutation.nextSibling === null
                          && mutation.previousSibling === null) {

                        $(mutation.target).hover(
                            function() {
                                $(this).css('z-index', 998);
                            }, function() {
                                $(this).css('z-index', 'inherit');
                            }
                        );

                        observer.disconnect();
                    }
                });
            }

            observer.observe(document.body, config);
        },

        loadCarousel: function () {
            if (!this.config.options.general.carousel || this.galleryImages.hasClass('slick-slider')) {
                return;
            }

            var imageCount = this.galleryImages.find('a').length,
                firstImage = this.galleryImages.find('a img').first();

            if (imageCount <= 1) {
                return;
            }

            // check if images are loaded
            if ($(firstImage).height() > 0) {
                var config = this.config.options.carousel;

                if (!config.slidesToScroll) {
                    config.slidesToScroll = 1;
                }

                if (this.isMobileAndTablet() && config.slidesToShow > this.slidesPerViewMobile) {
                    config.slidesToShow = this.slidesPerViewMobile;
                }

                config.speed = 300;
                if ($('body').css('direction') === 'rtl') {
                    config.rtl = true;
                }

                this.galleryImages.removeClass('slick-initialized');
                this.galleryImages.slick(config);
            } else {
                $(firstImage).on('load', this.loadCarousel.bind(this));
            }
        },

        hidePreloading: function () {
            var preloader = $(this.loadingSelector);
            if (preloader.length) {
                preloader.hide();
            }
        },

        showPreloading: function () {
            var preloader = $(this.loadingSelector);
            if (preloader.length) {
                preloader.show();
            }
        },

        generateMainImage: function (imageObject) {
            var mainImageContainer = $(this.mainImageContainerSelector);
            if (mainImageContainer.length
                && (imageObject.type === 'image'
                || (imageObject.type === undefined && imageObject.img))
            ) {
                var element = $('<img>',{
                    id:  'amasty-main-image',
                    'data-zoom-image': imageObject.full,
                    class: 'amasty-main-image',
                    title: imageObject.caption,
                    alt: imageObject.caption,
                    src: imageObject.img
                });

                mainImageContainer.append(element);
                if (typeof(this.config.options.zoom.medium_size.width) !== "undefined") {
                    mainImageContainer.css({
                        width: this.config.options.zoom.medium_size.width
                    });
                }
            }
        },

        generateProductImages: function () {
            var self = this,
                galleryImagesContainer = this.galleryImages,
                mainImageGenerated = false;
            if (this.config.data.length && galleryImagesContainer.length) {
                if(this.isMobileAndTablet()
                && 'vertical' in this.config.options.carousel
                && 'verticalSwiping' in this.config.options.carousel
                ) {
                    this.config.options.general.carousel_position = 'under';
                    delete this.config.options.carousel.vertical;
                    delete this.config.options.carousel.verticalSwiping;
                }
                $('#amasty-gallery').addClass('position-' + this.config.options.general.carousel_position);

                if ($(this.galleryImagesSelector + ' .slick-slide').length != 0) {
                    $(this.galleryImagesSelector + ' .slick-track').html('');
                } else {
                    self.galleryImages.html('');
                }

                if (this.config.data.length > 1) {
                    $.each(this.config.data, function (key, imageObject) {
                        if (imageObject.thumb) {
                            var element = $('<img>', {
                                class: 'amasty-gallery-image',
                                title: imageObject.caption,
                                alt: imageObject.caption,
                                width: self.config.options.zoom.small_size.width,
                                src: imageObject.thumb
                            });

                            element.load(function () {
                                if (!$(this).hasClass('gallery-slick-observed')) {
                                    self.galleryImages.find('a img').addClass('gallery-slick-observed');
                                }
                            });

                            var link = $('<a>', {
                                class: 'amasty-gallery-thumb-link',
                                'data-image': imageObject.img,
                                'data-zoom-image': imageObject.full,
                                title: imageObject.caption,
                                rel: 'amasty-gallery-group',
                                css: {
                                    'position': 'relative'
                                }
                            });

                            if (imageObject.type === 'video'
                                && imageObject.videoUrl
                            ) {
                                link.attr('data-video-url', imageObject.videoUrl);
                                link.addClass('video-thumb-icon');
                            }

                            link.append(element);

                            self.loadCarousel();

                            if (self.galleryImages.hasClass('slick-slider')) {
                                self.galleryImages.slick('slickAdd', link);
                            } else {
                                galleryImagesContainer.append(link);
                            }

                            if (imageObject.isMain) {
                                mainImageGenerated = true;
                                self.generateMainImage(imageObject);
                            }
                        }
                    });
                }

                if (!mainImageGenerated) {
                    $.each(this.config.data, function(index ,imageObject) {
                        if (imageObject.img) {
                            self.generateMainImage(this);
                            return false;
                        }
                    });
                }

                if (this.config.options.zoom.image_change) {
                    $(this.galleryContainerSelector + ' a').addClass('cursor-pointer')
                }

                if ($(this.mainImageSelector).height() != 0) {
                    $(this.mainImageContainerSelector).css('min-height', ($(this.mainImageContainerSelector).height()));
                }
            } else {
                console.log('There are no images for this product or selector is wrong.');
            }

            this.hidePreloading();
        },

        insertPolyfills: function () {
            if (typeof Object.assign != 'function') {
                // Must be writable: true, enumerable: false, configurable: true
                Object.defineProperty(Object, "assign", {
                    value: function assign(target, varArgs) { // .length of function is 2
                        'use strict';
                        if (target == null) { // TypeError if undefined or null
                            throw new TypeError('Cannot convert undefined or null to object');
                        }

                        var to = Object(target);

                        for (var index = 1; index < arguments.length; index++) {
                            var nextSource = arguments[index];

                            if (nextSource != null) { // Skip over if undefined or null
                                for (var nextKey in nextSource) {
                                    // Avoid bugs when hasOwnProperty is shadowed
                                    if (Object.prototype.hasOwnProperty.call(nextSource, nextKey)) {
                                        to[nextKey] = nextSource[nextKey];
                                    }
                                }
                            }
                        }
                        return to;
                    },
                    writable: true,
                    configurable: true
                });
            }
        }
    })
});

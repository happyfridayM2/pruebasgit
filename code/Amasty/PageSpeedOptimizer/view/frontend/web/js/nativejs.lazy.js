//DO NOT EDIT
(function() {
    var addEventListener =  window.addEventListener || function(n,f) { window.attachEvent('on'+n, f); },
        removeEventListener = window.removeEventListener || function(n,f,b) { window.detachEvent('on'+n, f); };

    var lazyLoader = {
        cache: [],
        mobileScreenSize: 500,
        observersAdded: false,

        addObservers: function() {
            if (!this.observersAdded) {
                addEventListener('scroll', lazyLoader.throttledLoad);
                addEventListener('resize', lazyLoader.throttledLoad);
                this.observersAdded = true;
            }
        },

        removeObservers: function() {
            removeEventListener('scroll', lazyLoader.throttledLoad, false);
            removeEventListener('resize', lazyLoader.throttledLoad, false);
            this.observersAdded = false;
        },

        throttleTimer: new Date().getTime(),

        throttledLoad: function() {
            var now = new Date().getTime();
            if ((now - lazyLoader.throttleTimer) >= 200) {
                lazyLoader.throttleTimer = now;
                lazyLoader.loadVisibleImages();
            }
        },

        loadVisibleImages: function() {
            var scrollY = window.pageYOffset || document.documentElement.scrollTop;
            var pageHeight = window.innerHeight || document.documentElement.clientHeight;
            var range = {
                min: scrollY - 200,
                max: scrollY + pageHeight + 200
            };

            var i = 0;
            while (i < lazyLoader.cache.length) {
                var image = lazyLoader.cache[i];
                var imagePosition = getOffsetTop(image);
                var imageHeight = image.height || 0;

                if ((imagePosition >= range.min - imageHeight) && (imagePosition <= range.max)) {
                    var mobileSrc = image.getAttribute('data-src-mobile');

                    image.onload = function() {
                        this.className = this.className.replace(/(^|\s+)lazy-load(\s+|$)/, '$1lazy-loaded$2');
                    };

                    if (mobileSrc && screen.width <= lazyLoader.mobileScreenSize) {
                        image.src = mobileSrc;
                    }
                    else {
                        image.src = image.getAttribute('data-amsrc');
                    }

                    image.removeAttribute('data-amsrc');
                    image.removeAttribute('data-src-mobile');

                    lazyLoader.cache.splice(i, 1);
                    continue;
                }

                i++;
            }

            if (lazyLoader.cache.length === 0) {
                lazyLoader.removeObservers();
            }
        },

        init: function() {
            // Patch IE7- (querySelectorAll)
            if (!document.querySelectorAll) {
                document.querySelectorAll = function(selector) {
                    var doc = document,
                        head = doc.documentElement.firstChild,
                        styleTag = doc.createElement('STYLE');
                    head.appendChild(styleTag);
                    doc.__qsaels = [];
                    styleTag.styleSheet.cssText = selector + "{x:expression(document.__qsaels.push(this))}";
                    window.scrollBy(0, 0);
                    return doc.__qsaels;
                }
            }

            var imageNodes = document.querySelectorAll('img[data-amsrc]');

            for (var i = 0; i < imageNodes.length; i++) {
                var imageNode = imageNodes[i];

                // Add a placeholder if one doesn't exist
                //imageNode.src = imageNode.src || lazyLoader.tinyGif;
                if (lazyLoader.cache.indexOf(imageNode) == -1) {
                    lazyLoader.cache.push(imageNode);
                }
            }

            lazyLoader.addObservers();
            lazyLoader.loadVisibleImages();
        }
    };

    // For IE7 compatibility
    // Adapted from http://www.quirksmode.org/js/findpos.html
    function getOffsetTop(el) {
        var val = 0;
        if (el.offsetParent) {
            do {
                val += el.offsetTop;
            } while (el = el.offsetParent);
            return val;
        }
    }

    lazyLoader.init();
    window.amlazycallback= lazyLoader.init;
})();

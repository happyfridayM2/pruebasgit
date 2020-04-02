define([
    'jquery',
    'jquery/ui',
    'Amasty_Base/js/http_build_query',
    'uiRegistry'
], function ($, ui, httpBuildQuery, uiRegistry) {

    $.widget('mage.amScrollScript', {
        options: {},
        type: 'auto',
        is_loading: 0,
        next_data_url: "",
        prev_data_url: "",
        next_data_cache: "",
        flag_next_cache: 0,
        prev_data_cache: "",
        flag_prev_cache: 0,
        pagesCount: 1,
        pagesLoaded: [],
        currentPage: 1,
        last_scroll: 0,
        disabled: 0,
        additionalHeight: null,
        totalAmountSelector: '.toolbar-amount',
        totalNumberSelector: '.toolbar-number',
        toolbarSelector: '.toolbar.toolbar-products',
        amPageCountSelector: '#am-page-count',
        footerSelector: '.page-footer',
        cahShowAfter: false,
        canShowBefore: false,
        pagesBeforeButton: 0,
        isReinitialized: false,
        afterButton: null,
        beforeButton: null,
        insertedPages: 0,

        _create: function (options) {
            var self = this;
            $(document).on("amscroll_refresh", function () {
                self.initialize();//run jQuery(document).trigger('amscroll_refresh');
            });

            this.initialize();
        },

        _externalAfterAjax: function (productContainer) {
            this.insertedPages++;
            try {
                if ($('head').html().indexOf('Infortis') > -1) {
                    $(document).trigger('last-swatch-found');
                }

                if (productContainer) {
                    if (productContainer.find("img.lazy").length) {
                        productContainer.find("img.lazy").unveil(25, function () {
                            var self = $(this);
                            setTimeout(function () {
                                self.removeClass('lazy');
                            }, 0);
                        });
                    }

                    var lazyImg = productContainer.find("img.porto-lazyload:not(.porto-lazyload-loaded)");
                    if (lazyImg.length && typeof $.fn.lazyload == 'function') {
                        lazyImg.lazyload({effect:"fadeIn"});
                    }
                }

                if (this.type === 'combined' && this.insertedPages >= this.pagesBeforeButton) {
                    this.changeType();
                }
            } catch (e) {
                console.warn(e);
            }
        },

        initialize: function () {
            const self = this;
            this.next_data_cache = "";
            this.pagesLoaded = [];
            this._initPagesCount();
            this.disabled = 1;

            var isValidConfiguration = this._validate();
            if (!isValidConfiguration) {
                $('.amscroll-navbar').remove(); //remove old nav bar
                return;
            }
            this.disabled = 0;
            this.type = this.options['actionMode'];

            this.pagesBeforeButton = this.options['pages_before_button'];

            this._preloadPages();
            this._hideToolbars();

            $(window).scroll(function () {
                self._initPaginator();
            });
            setTimeout(function () {
                self._initPaginator();
            }, 7000);

            this._initProgressBar();

            if (this.type == 'button') {
                this._generateButton('before', false);
                this._generateButton('after', false);
            }

            this._pagePositionAfterStepBack();
        },

        /**
         * Endpoint to execute ajax request
         *
         * @param responseUrl
         * @param successCallback
         */
        doAjax: function(responseUrl, successCallback) {
            return $.getJSON(responseUrl, successCallback);
        },

        _validate: function () {
            if (!this.options['product_container']
                || $(this.options['product_container']).length === 0
            ) {
                console.log('Please specify "Products Group" Dom selector in module settings.');
                return false;
            }

            if (this.pagesCount <= 1) {
                return false;
            }

            return true;
        },

        _initPagesCount: function () {
            this.pagesLoaded = [];
            var amPager = $(this.amPageCountSelector);
            if (amPager && amPager.length) {
                this.pagesCount = parseInt(amPager.html());
                return;
            }

            var parent = $(this.totalAmountSelector).first();
            if (parent) {
                var childs = parent.find(this.totalNumberSelector);
                if (parent && childs.length >= 3) {
                    var limit = $('#limiter').val();
                    if ($(childs[2]).text() > 0 && limit) {
                        var allProducts = $(childs[2]).text();
                        var result = Math.ceil(
                            parseInt(allProducts) / parseInt(limit)
                        );
                        if (result > 1) {
                            this.pagesCount = result;
                            return;
                        }
                    }
                }
            }
            this.pagesCount = 1;
        },

        changeType: function () {
            if (!this.isReinitialized) {
                this.type = 'button';
                this._generateButton('before', false);
                this._generateButton('after', this.currentPage + 1);
                this.isReinitialized = true;
            }
        },

        _preloadPages: function () {
            this.currentPage = this._getCurrentPage();
            var productContainer = $(this.options['product_container']);
            if (productContainer.length > 1 && productContainer.first().parents('.widget').length) {
                productContainer = $(productContainer[1]);
            }
            productContainer.attr('amscroll-page', this.currentPage);
            productContainer.addClass('amscroll-page');
            if (this.options['pageNumbers'] == '1') {
                var pageNumEl = this._generatePageTitle(this.currentPage);
                if (pageNumEl) {
                    productContainer.before(pageNumEl);
                }
            }
            this.pagesLoaded.push(this.currentPage);
            this._preloadPageAfter(this.currentPage);
            this._preloadPageBefore(this.currentPage);

        },

        _getCurrentPage: function () {
            var currentPage = parseInt(this.options['current_page']);
            if (currentPage > this.pagesCount) {
                currentPage = this.pagesCount;
            }

            return currentPage;
        },

        _preloadPageAfter: function (page) {
            var nextPage = page + 1;
            if (nextPage && nextPage <= this.pagesCount) {
                this.next_data_url = this._generateUrl(nextPage, 1);
                this.pagesLoaded.push(nextPage);
                var self = this;
                self.flag_next_cache = 1;

                this.doAjax(this.next_data_url, function (data) {
                    if (data.categoryProducts) {
                        self.flag_next_cache = 0;
                        self.next_data_cache = data;
                    } else {
                        self._stop();
                    }
                }).fail(function () {
                    self._stop();
                });

                this.next_data_url = '';
            }
        },

        _preloadPageBefore: function (page) {
            var prevPage = page - 1;
            if (prevPage && prevPage >= 1) {
                this.prev_data_url = this._generateUrl(prevPage, 1);
                this.pagesLoaded.unshift(prevPage);
                var self = this;
                self.flag_prev_cache = 1;

                this.doAjax(this.prev_data_url, function (data) {
                    if (data.categoryProducts) {
                        self.flag_prev_cache = 0;
                        self.prev_data_cache = data;
                    } else {
                        self._stop();
                    }
                }).fail(function () {
                    self._stop();
                });

                this.prev_data_url = '';
            }
        },

        _stop: function () {
            this.disabled = 1;
            this._showToolbars();
            $('.amscroll-loading').hide();
        },

        _getAdditionalBlockHeight: function () {
            if (this.additionalHeight === null) {
                var height = 0;

                var blockAfterProducts = $(".main .products ~ .block-static-block");
                if (blockAfterProducts.length) {
                    height += blockAfterProducts.height();
                }

                if ($(this.footerSelector).length > 0) {
                    height += $(this.footerSelector).height();
                }

                //for custom theme
                var pageBottom = $('.page-bottom');
                if (pageBottom.length > 0) {
                    height += pageBottom.first().height();
                }

                this.additionalHeight = height;
            }

            return this.additionalHeight;
        },

        _initPaginator: function () {
            if (this.disabled) {
                return;
            }

            var scroll_pos = $(window).scrollTop(),
                diff = $(document).height() - $(window).height();
            diff -= this._getAdditionalBlockHeight();
            diff = 0.9 * diff;

            if (scroll_pos >= diff) {
                if (this.is_loading == 0) {
                    this._loadFollowing();
                }
            }

            if (scroll_pos <= this._getTopContainersHeight()) {
                if (this.is_loading == 0) {
                    this._loadPrevious();
                }
            }

            /*find current page and change url and scroll-bar*/
            this._calculateCurrentScrollPage(scroll_pos);

            var self = this;
            $(document).ready(function () {
                // if we have enough room, load the next batch
                var productContainer = $(self.options['product_container']);
                if ($(window).height() > productContainer.height()) {
                    if ("" != self.next_data_url) {
                        self._loadFollowing();
                    }
                }
            });
        },

        _calculateCurrentScrollPage: function (scroll_pos) {
            var self = this;
            if (Math.abs(scroll_pos - self.last_scroll) > $(window).height() * 0.1) {
                self.last_scroll = scroll_pos;
                this._updateUrlAndCurrentPage();
            }
        },

        _updateUrlAndCurrentPage: function () {
            var self = this;
            $(self.options['product_container']).each(function (index) {
                if (self._mostlyVisible(this, index)) {
                    var page = $(this).attr('amscroll-page');
                    page = parseInt(page);
                    if (page && page != self.currentPage) {
                        var newUrl = self._generateUrl(page, 0);
                        if (!window.history.state || newUrl !== window.history.state.url) {
                            window.history.replaceState({url: newUrl}, '', newUrl);
                        }
                        if (page) {
                            self.currentPage = page;
                            $("#amscroll-navbar-current").html(page);
                        }
                    }

                    return false;
                }
            });
        },

        _loadFollowing: function () {
            var self = this;
            if (this.flag_next_cache && this.type != 'button') {
                this._createLoading('after');
            }
            if (this.next_data_url != "" || this.next_data_cache) {
                if (this.type != 'button') {
                    this._createLoading('after');
                }
                if (this.next_data_cache) {
                    this.showFollowing(this.next_data_cache);
                } else {
                    if (!this.flag_next_cache) {
                        this.is_loading = 1; // note: this will break when the server doesn't respond
                        this.doAjax(this.next_data_url, function (data) {
                            self.showFollowing(data);
                        });
                    }
                }
            }
        },

        showFollowing: function (data) {
            if (data.categoryProducts) {
                if (this.type == 'button') {
                    if (this.cahShowAfter) {
                        this.is_loading = 0;
                        this.cahShowAfter = false;
                    } else {
                        return;
                    }
                }
                this.next_data_url = '';
                this.next_data_cache = false;
                this._insertNewProductBlock(data, 'after');
                this._afterShowFollowing();
            }
        },

        _afterShowFollowing: function () {
            var nextPage = $(this.pagesLoaded).get(-1) + 1;//last + 1
            if (nextPage && nextPage <= this.pagesCount && $.inArray(nextPage, this.pagesLoaded) == -1) {
                this.next_data_url = this._generateUrl(nextPage, 1);
                this.pagesLoaded.push(nextPage);
                var self = this;
                this.flag_next_cache = 1;
                this.doAjax(this.next_data_url, function (preview_data) {
                    self.flag_next_cache = 0;
                    self.next_data_cache = preview_data;
                    $(window).scroll();
                });
            }

            this.is_loading = 0;
        },

        _loadPrevious: function () {
            var self = this;
            if (this.flag_prev_cache && this.type != 'button') {
                this._createLoading('before');
            }
            if (this.prev_data_url != "" || this.prev_data_cache) {
                if (this.type != 'button') {
                    this._createLoading('before');
                }
                if (this.prev_data_cache) {
                    this.showPrevious(this.prev_data_cache);
                } else {
                    if (!this.flag_prev_cache) {
                        this.is_loading = 1; // note: this will break when the server doesn't respond
                        this.doAjax(this.prev_data_url, function (data) {
                            self.showPrevious(data);
                        });
                    }
                }
            }
        },

        showPrevious: function (data) {
            if (data.categoryProducts) {
                if (this.type == 'button') {
                    if (this.canShowBefore) {
                        this.is_loading = 0;
                        this.canShowBefore = false;
                    } else {
                        return;
                    }
                }
                this.prev_data_cache = false;
                this.prev_data_url = '';
                this._insertNewProductBlock(data, 'before');
                this._afterShowPrevious();
            }
        },

        _afterShowPrevious: function () {
            var prevPage = $(this.pagesLoaded).get(0) - 1;
            if (prevPage && prevPage >= 1 && $.inArray(prevPage, this.pagesLoaded) == -1) {
                this.prev_data_url = this._generateUrl(prevPage, 1);
                this.pagesLoaded.unshift(prevPage);
                var self = this;
                this.flag_prev_cache = 1;

                this.doAjax(this.prev_data_url, function (preview_data) {
                    self.flag_prev_cache = 0;
                    self.prev_data_cache = preview_data;
                    $(window).scroll();
                });
            }
            this.is_loading = 0;
        },

        _createLoading: function (position) {
            var loading = $('<div/>', {
                class: 'amscroll-loading',
                style: 'background-image: url(' + this.options['loadingImage'] + ');',
                text: ' '
            });

            if ('after' === position) {
                if ($('.amscroll-page:last ~ .amscroll-loading').length == 0) {//check duplicating preloader
                    $(this.options['product_container']).last().after(loading);
                }
            } else {
                if ($('.amscroll-loading:not(.amscroll-page:last ~ .amscroll-loading)').length == 0) {//check duplicating preloader
                    var element = $('.amscroll-page-num, ' + this.options['product_container']).first();
                    element.before(loading);
                }
            }

            $('.amscroll-loading + .amscroll-loading').remove();
        },

        _generateButton: function (position, page) {
            page = page ? page : this.currentPage;
            if ((this.pagesLoaded.indexOf(1) !== -1 && position === 'before')
                || (page >= this.pagesCount && position === 'after')
            ) {
                if (this.options.actionMode == 'button'){
                    if (!(page == 2 && position === 'before')) {
                        return;
                    }
                } else {
                    return;
                }
            }

            var buttonElement = $('<div/>', {
                    class: 'amscroll-load-button',
                    style: this.options['loadNextStyle'],
                    text: this.options['loading' + position + 'TextButton']
                }),
                self = this;
            buttonElement.attr('amscroll_type', position);
            if (position == 'after') {
                if (this.afterButton) {
                    this.afterButton.remove();
                }
                this.afterButton = buttonElement;
                this._insertBlockInTheEnd(buttonElement);
            } else {
                if (this.beforeButton) {
                    this.beforeButton.remove();
                }
                this.beforeButton = buttonElement;
                this._insertBlockInTheBegin(buttonElement);
            }

            $('.amscroll-load-button[amscroll_type="' + position + '"]').on('click', function (item) {
                self.buttonClick(item);
            });
        },

        buttonClick: function (event) {
            var element = $(event.target),
                type = element.attr('amscroll_type');
            element.remove();
            this._createLoading(type);
            if (type == 'after') {
                this.cahShowAfter = true;
                this._loadFollowing();
            } else {
                this.canShowBefore = true;
                this._loadPrevious();
            }

            this._updateUrlAndCurrentPage();
        },

        _insertNewProductBlock: function (data, position) {
            var html = data.categoryProducts,
                tmp = $('<div/>').append(html),
                productContainer = tmp.find(this.options['product_container']);

            tmp.find(this.toolbarSelector).remove();
            tmp.find('.amasty-catalog-topnav').remove();//remove navigation top block
            productContainer.addClass('amscroll-pages').attr('amscroll-page', data.currentPage);

            if (this.options['pageNumbers'] == '1') {
                var pageNumEl = this._generatePageTitle(data.currentPage);
                if (pageNumEl) {
                    productContainer.before(pageNumEl);
                }
            }

            html = tmp.html();

            if ('after' == position) {
                $('.amscroll-page:last ~ .amscroll-loading').remove();
                this._insertBlockInTheEnd(html);
            } else {
                var element = this._insertBlockInTheBegin(html),
                    item_height = element.height();

                if (this.type != 'button') {
                    window.scrollTo(0, $(window).scrollTop() + item_height);
                }
            }

            this._addObserverToProductLink($('.amscroll-pages[amscroll-page="' + data.currentPage + '"]'));
            if (this.type == 'button') {
                this._generateButton(position, data.currentPage);
            }

            this.updateMultipleWishlist();
        },

        updateMultipleWishlist: function () {
            var wishLists = uiRegistry.get('multipleWishlist');
            if (wishLists) {
                $('body').off('click', '[data-post-new-wishlist]');
                uiRegistry.get('multipleWishlist').initialize();
            }
        },

        _addObserverToProductLink: function (productContainer) {
            var self = this;
            this._externalAfterAjax(productContainer);
            productContainer.find('.item a').on("click", function (event) {
                try {
                    var parent = $(this).parents('.amscroll-pages').first();
                    var page = parent ? parent.attr('amscroll-page') : null;
                    if (page) {
                        var newUrl = self._generateUrl(page, 0);
                        if (!window.history.state || newUrl !== window.history.state.url) {
                            window.history.replaceState({url: newUrl}, '', newUrl);
                        }
                    }
                } catch (e) {
                }
            });

            productContainer.first().trigger('contentUpdated');
            if ($.fn.applyBindings != undefined) {
                productContainer.first().applyBindings();
            }
        },

        _generateUrl: function (page, addScroll) {
            var url = window.location.href;
            var params = this._getQueryParams(window.location.search);
            if (!params || !Object.keys(params).length) {
                if (page) {
                    var paramString = '?p=' + page;
                    if (addScroll) {
                        paramString += '&is_scroll=1';
                    }

                    if (url.indexOf('#') > 0) {
                        url = url.replace('#', paramString + '#')
                    } else {
                        url += paramString;
                    }
                }
            } else {
                url = url.replace(window.location.search, '');
                if (page && parseInt(page) > 1) {
                    params['p'] = page;
                } else if (params['p']) {
                    delete params['p'];
                }

                if (addScroll) {
                    params['is_scroll'] = 1;
                }

                if (Object.keys(params).length) {
                    params = httpBuildQuery(params);
                    if (url.indexOf('#') > 0) {
                        url = url.replace('#', '?' + params + '#')
                    } else {
                        url += '?' + params;
                    }
                }
            }

            return url;
        },

        _getQueryParams: function (url) {
            url = url.split('+').join(' ');
            var params = {},
                tokens,
                re = /[?&]?([^=]+)=([^&]*)/g;

            while (tokens = re.exec(url)) {
                params[decodeURIComponent(tokens[1])] = decodeURIComponent(tokens[2]);
            }

            return params;
        },

        _hideToolbars: function () {
            $(this.totalAmountSelector).hide();
            $('.products ~ ' + this.toolbarSelector).hide();
        },

        _showToolbars: function () {
            $(this.totalAmountSelector).show();
            $('.products ~ ' + this.toolbarSelector).show();
        },

        _generatePageTitle: function (page) {
            if ($('#amscroll-page-num' + page).length) {
                return false;
            }

            pageNumEl = $('<div/>', {
                class: 'amscroll-page-num',
                id: 'amscroll-page-num' + page,
                text: this.options['pageNumberContent'] + page
            });

            return pageNumEl;
        },

        _mostlyVisible: function (element, index) {
            element = $(element);
            var visible = element.is(":visible"),
                scroll_pos = $(window).scrollTop(),
                window_height = $(window).height(),
                el_top = element.offset().top,
                el_height = element.height(),
                el_bottom = el_top + el_height;

            return ((el_bottom - el_height * 0.25 > scroll_pos)
                && (el_top < (scroll_pos + 0.5 * window_height))
                && visible) || (index == 0 && scroll_pos < el_top);
        },

        _getTopContainersHeight: function () {
            var result = $(".page-header").height() + $(".nav-sections").height();
            if ($(".main .block-static-block ~ .products ").length) {
                result += $(".main .block-static-block").height();
            }
            result = 0.9 * result;

            return result;
        },

        _initProgressBar: function () {
            if (this.options['progressbar'] && this.options['progressbar']['enabled'] == '1') {
                $('.amscroll-navbar').remove();
                var progressbar = $('<div/>', {
                    class: 'amscroll-navbar',
                    id: 'amscroll-navbar',
                    style: this.options['progressbar']['styles']
                });

                var text = this.options['progressbarText'];
                text = text.replace('%1', '<span id="amscroll-navbar-current">' + this.currentPage + '</span>');
                text = text.replace('%2', this.pagesCount);
                progressbar.append('<span class="amscroll-navbar-text">' + text + '</span>');

                var linkElement = $('<a/>', {
                    class: 'amscroll-navbar-link'
                });

                linkElement.click(function () {
                    $('body, html').animate({
                        scrollTop: 0
                    }, 300);
                });
                progressbar.append(linkElement);

                $("body").append(progressbar);
            }
        },

        _insertBlockInTheEnd: function (block) {
            $(this.options['product_container']).last().after(block);
        },

        _insertBlockInTheBegin: function (block) {
            var element = $('.amscroll-page-num, ' + this.options['product_container']).first();
            $('.amscroll-loading:not(.amscroll-page:last ~ .amscroll-loading)').remove();
            element.before(block);

            return element;
        },

        // Fix an issue with scroll position after step back to product listing
        _pagePositionAfterStepBack: function () {
            history.scrollRestoration = 'auto';

            $('body').on('click', '.amscroll-pages .product-item a.product', function () {
                sessionStorage.setItem('am-product', $(this).attr("href"));
            });

            if (sessionStorage.getItem('am-product')) {
                var item = $('a[href="' + sessionStorage.getItem('am-product') + '"].product').parent();
                if (item.length) {
                    history.scrollRestoration = 'manual';
                    $('html, body').animate({
                        scrollTop: (item.offset().top)
                    }, 0);
                }
                sessionStorage.removeItem('am-product');
            }
        }
    });

    return $.mage.amScrollScript;
});

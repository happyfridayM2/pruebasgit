define([
    "underscore",
    "jquery",
    "amShopbyFilterAbstract",
    "mage/translate"
], function (_, $) {
    'use strict';

    $.widget('mage.amShopbyApplyFilters', {
        showButtonClick: false,
        showButtonContainer: '.am_shopby_apply_filters',
        showButton: 'am-show-button',
        oneColumnFilterWrapper: '#narrow-by-list',
        isMobile: window.innerWidth < 768,

        _create: function () {
            var self = this;
            $(function () {
                var element = $(self.element[0]),
                    navigation = element.closest(self.options.navigationSelector),
                    isMobile = $.mage.amShopbyApplyFilters.prototype.isMobile;

                $('body').append(element.closest($.mage.amShopbyApplyFilters.prototype.showButtonContainer));

                if (!isMobile) {
                    $('.amasty-catalog-topnav .filter-options-content .item,' +
                        ' .amasty-catalog-topnav .am-filter-items-attr_price,' +
                        '.amasty-catalog-topnav .am-filter-items-attr_decimal,' +
                        '.amasty-catalog-topnav .am-fromto-widget').addClass('am-top-filters');
                    self.applyShowButtonForSwatch();
                }

                element.on('click', function (e) {
                    var valid = true,
                        cachedValues = $.mage.amShopbyAjax.prototype
                            ? $.mage.amShopbyAjax.prototype.cached[$.mage.amShopbyAjax.prototype.cacheKey]
                            : null,
                        cachedKey = $.mage.amShopbyAjax.prototype.response;

                    navigation.find('form').each(function () {
                        valid = valid && $(this).valid();
                    });

                    var response = cachedValues ? cachedValues : cachedKey;

                    $.mage.amShopbyFilterAbstract.prototype.options.isCategorySingleSelect
                        = self.options.isCategorySingleSelect;

                    if (!response && $.mage.amShopbyAjax.prototype.startAjax) {
                        $.mage.amShopbyApplyFilters.prototype.showButtonClick = true;
                        $("#amasty-shopby-overlay").show();
                        self.removeShowButton();
                    }

                    if (valid && self.options.ajaxEnabled && response) {
                        self.removeShowButton();
                        window.history.pushState({url: response.url}, '', response.url);
                        $(document).trigger('amshopby:reload_html', {response: response});
                        $.mage.amShopbyAjax.prototype.response = false;
                        $.mage.amShopbyApplyFilters.prototype.showButtonClick = false;
                    }

                    window.onpopstate = function () {
                        location.reload();
                    };

                    if (valid && self.options.ajaxEnabled != 1) {
                        var forms = $('form[data-amshopby-filter]'),
                            data = $.mage.amShopbyFilterAbstract.prototype.normalizeData(forms.serializeArray()),
                            baseUrl = self.options.clearUrl;

                        if (typeof data.clearUrl !== 'undefined') {
                            baseUrl = data.clearUrl;
                            delete data.clearUrl;
                        }
                        var params = $.param(data),
                            url = baseUrl +
                            (baseUrl.indexOf('?') === -1 ? '?' : '&') +
                            params;
                        document.location.href = url;
                    }
                    this.blur();
                    return true;
                });

            });
        },

        renderShowButton: function (e, element) {
            var button = $('.' + $.mage.amShopbyApplyFilters.prototype.showButton),
                buttonHeight = button.outerHeight();

            if ($.mage.amShopbyApplyFilters.prototype.isMobile) {
                $('#narrow-by-list .filter-options-item:last-child').css({
                    "padding-bottom": buttonHeight,
                    "margin-bottom": "15px"
                });
                $($.mage.amShopbyApplyFilters.prototype.showButtonContainer).addClass('visible');
                $('.' + $.mage.amShopbyApplyFilters.prototype.showButton + ' > .am-items').html('').addClass('-loading');

                return;
            }

            var sideBar = $('.sidebar-main .filter-options'),
                leftPosition = sideBar.length ? sideBar : $('[data-am-js="shopby-container"]'),
                priceElement = '.am-filter-items-attr_price',
                orientation,
                elementType,
                posTop,
                posLeft,
                oneColumn = $('body').hasClass('page-layout-1column'),
                rightSidebar = $('body').hasClass('page-layout-2columns-right'),
                marginWidth = 30, // margin for button:before
                marginHeight = 10, // margin height
                $element = $(element),
                oneColumnWrapper = $($.mage.amShopbyApplyFilters.prototype.oneColumnFilterWrapper),
                topFiltersWrapper = $('.amasty-catalog-topnav'),
                self = this,
                elementPosition = element.offset ? element.offset() : [];

            $(self.showButtonContainer).css('width', 'inherit');
            // get orientation
            if ($element.parents('.amasty-catalog-topnav').length || oneColumn) {
                button.removeClass().addClass($.mage.amShopbyApplyFilters.prototype.showButton + ' -horizontal');
                orientation = 0;
            } else {
                if (rightSidebar) {
                    button.removeClass().addClass($.mage.amShopbyApplyFilters.prototype.showButton + ' -vertical-right');
                } else {
                    button.removeClass().addClass($.mage.amShopbyApplyFilters.prototype.showButton + ' -vertical');
                }
                orientation = 1;
            }

            //get position
            if (orientation) {
                elementPosition['top'] = elementPosition ? elementPosition['top'] : 0;
                posTop = (e.pageY ? e.pageY : elementPosition['top']) - buttonHeight / 2;
                rightSidebar ?
                    posLeft = leftPosition.offset().left - button.outerWidth() - marginWidth :
                    posLeft = leftPosition.offset().left + leftPosition.outerWidth() + marginWidth;
            } else {
                if (oneColumn) {
                    oneColumnWrapper.length ?
                        posTop = oneColumnWrapper.offset().top - buttonHeight - marginHeight :
                        console.warn('Improved Layered Navigation: You do not have default selector for filters in one-column design.');
                } else {
                    posTop = topFiltersWrapper.offset().top - buttonHeight - marginHeight;
                }

                elementPosition['left'] = elementPosition ? elementPosition['left'] : 0;
                posLeft = (e.pageX ? e.pageX : elementPosition['left']) - button.outerWidth() / 2;
            }

            elementType = self.getShowButtonType($element);

            switch (elementType) {
                case 'dropdown':
                    if (orientation) {
                        posTop = $element.offset().top - buttonHeight / 2;
                    } else {
                        posLeft = $element.offset().left - marginHeight;
                    }
                    break;
                case 'flyout':
                    if (orientation) {
                        rightSidebar ?
                            posLeft = $element.parents('.item').offset().left - button.outerWidth() - marginWidth :
                            posLeft = $element.parents('.item').offset().left
                                + $element.parents('.item').outerWidth() + marginWidth;
                    }
                    break;
                case 'price':
                    if (orientation) {
                        posTop = $(priceElement).not('.am-top-filters').offset().top - buttonHeight / 2 + marginHeight;
                    } else {
                        posLeft = $(priceElement).offset().left - marginHeight;
                    }
                    break;
                case 'decimal':
                    if (orientation) {
                        posTop = $element.offset().top - buttonHeight / 2 + marginHeight;
                    } else {
                        posLeft = $element.offset().left - marginHeight;
                    }
                    break;
                case 'price-widget':
                    if (orientation) {
                        posTop = $element.offset().top - buttonHeight / 2 + marginHeight;
                    } else {
                        posLeft = $element.offset().left - marginHeight;
                    }
                    break;
            }

            self.setShowButton(posTop, posLeft, leftPosition);
        },

        getShowButtonType: function (element) {
            var elementType;

            if (element.is('select') || element.find('select').length) {
                elementType = 'dropdown';
            } else if (element.parents('.amshopby-fly-out-view').length) {
                elementType = 'flyout';
            } else if (element.parents('.am-filter-items-attr_price').length
                || element.is('[data-am-js="fromto-widget"]')
            ) {

                var elementParent = element.parents('.am-filter-items-attr_price')[0];

                element.is('[data-am-js="fromto-widget"]') ? elementType = 'price-widget' : elementType = 'price';

                if (elementParent && $(elementParent).has('[data-am-js="ranges"]').length) {
                    elementType = 'price-ranges';
                }

            } else if (element.is('[data-am-js="slider-container"]')) {
                elementType = 'decimal';
            }

            return elementType;
        },

        setShowButton: function (top, left, leftPosition) {
            var self = this;

            $('.' + $.mage.amShopbyApplyFilters.prototype.showButton + ' > .am-items').html('').addClass('-loading');

            $($.mage.amShopbyApplyFilters.prototype.showButtonContainer).removeClass('-fixed').css({
                "top": top,
                "left": left,
                "visibility": "visible",
                "display": "block"
            });

            self.changePositionOnScroll(top, left, leftPosition);
        },

        changePositionOnScroll: function (buttonTop, buttonLeft, leftPosition) {
            var self = this,
                windowHeight = $(window).height(),
                buttonContainer = $(self.showButtonContainer),
                buttonContainerHeight = buttonContainer.outerHeight(),
                filterLeft = leftPosition.offset().left,
                filterWidth = leftPosition.width();

            $(window).off('scroll').on('scroll', function () {
                var scrollTop = $(window).scrollTop(),
                    scrollBottom = scrollTop + windowHeight;

                if ((scrollBottom - buttonTop) <= buttonContainerHeight) {
                    buttonContainer
                        .addClass('-fixed')
                        .css({
                            'top': windowHeight - buttonContainerHeight,
                            'left': filterLeft,
                            'width': filterWidth
                        });
                } else if (buttonTop - scrollTop <= 0) {
                    buttonContainer
                        .addClass('-fixed')
                        .css({
                            'top': '5px',
                            'left': filterLeft,
                            'width': filterWidth
                        });
                } else if (buttonContainer.hasClass('-fixed')) {
                    buttonContainer
                        .css({
                            'top': buttonTop,
                            'left': buttonLeft,
                            'width': 'inherit'
                        })
                        .removeClass('-fixed');
                }
            });

            $(window).trigger('scroll');
        },

        removeShowButton: function () {
            $($.mage.amShopbyApplyFilters.prototype.showButtonContainer).remove();
        },

        showButtonCounter: function (count) {
            var items = $('.' + $.mage.amShopbyApplyFilters.prototype.showButton + ' > .am-items'),
                button = $('.' + $.mage.amShopbyApplyFilters.prototype.showButton + ' > .am-button');

            items.removeClass('-loading');

            count = parseInt(count);
            if (count > 1) {
                items.html(count + ' ' + $.mage.__('Items'));
                button.prop('disabled', false);
            } else if (count === 1) {
                items.html(count + ' ' + $.mage.__('Item'));
                button.prop('disabled', false);
            } else {
                items.html(count + ' ' + $.mage.__('Items'));
                button.prop('disabled', true);
            }
        },

        applyShowButtonForSwatch: function () {
            var self = this;

            $('.filter-options-content .swatch-option').on('click', function (e) {
                var element = jQuery(e.target);
                self.renderShowButton(e, element);
            });
        }
    });
});

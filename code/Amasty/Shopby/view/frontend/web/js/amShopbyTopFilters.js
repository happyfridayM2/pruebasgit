/**
 * @author    Amasty Team
 * @copyright Copyright (c) Amasty Ltd. ( http://www.amasty.com/ )
 * @package   Amasty_Shopby
 */

define([
    'jquery'
], function ($, mediaCheck) {
    'use strict';

    return {
        moveTopFiltersToSidebar: function () {
            if ($('.sidebar.sidebar-main #narrow-by-list').length === 0) {
                var blockClass = $('#layered-filter-block').length ? '#layered-filter-block' : '.block.filter',
                    $element = $(".catalog-topnav " + blockClass).clone();
                $(".catalog-topnav .filter-actions").hide();
                $element.find('.filter-title:before').css('display', 'none');
                $element
                    .addClass('amshopby-all-top-filters-append-left')
                    .attr('data-mage-init', '{"collapsible":{"openedState": "active", "collapsible": true, "active": false, "collateral": { "openedState": "filter-active", "element": "body" } }}');
                $element.find('#narrow-by-list')
                    .attr('data-mage-init', '{"accordion":{"openedState": "active", "collapsible": true, "active": false, "multipleCollapsible": false}}');
                $element.find('.block-actions.filter-actions').remove();
                $('.sidebar.sidebar-main').first().append($element);
                $('.sidebar.sidebar-main').first().trigger('contentUpdated');
                return;
            }

            $(".catalog-topnav #narrow-by-list .filter-options-item").each(function () {
                var isPresent = false,
                    classes = $(this).find('.items, .swatch-attribute').first().attr('class');
                if (classes) {
                    var listClasses = classes.split(" "),
                        currentClass = '';
                    for (var i = 0; i < listClasses.length; i++) {
                        if (listClasses[i].indexOf('am-filter-items-') !== -1) {
                            currentClass = listClasses[i];
                            break;
                        }
                    }
                    if (currentClass != '' && $('.sidebar.sidebar-main #narrow-by-list .' + currentClass).length > 0) {
                        isPresent = true;
                    }
                }

                if (isPresent) {
                    return;
                }

                $('.sidebar.sidebar-main #narrow-by-list').first().append(
                    $(this).clone().addClass('amshopby-filter-top')
                );
            });
            $(".sidebar.sidebar-main #narrow-by-list")
                .attr('data-mage-init', '{"accordion":{"openedState": "active", "collapsible": true, "active": false, "multipleCollapsible": true}}');
            $('.sidebar.sidebar-main .block.filter').first().trigger('contentUpdated');
        },

        removeTopFiltersFromSidebar: function () {
            if ($(".catalog-topnav").length === 0) {
                return;
            }

            $('.sidebar.sidebar-main #narrow-by-list .amshopby-filter-top').remove();
            $('.sidebar.sidebar-main .amshopby-all-top-filters-append-left').remove();
        }
    };
});

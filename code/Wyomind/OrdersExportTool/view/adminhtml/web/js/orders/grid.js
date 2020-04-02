/**
 * Copyright © 2019 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */
var OrdersExportTool = {
    _delete: function (order, profile, url) {
        if (confirm('Are you sure ?')) {
            data = {order: order, profile: profile};
            jQuery.ajax({
                url: url,
                type: 'POST',
                showLoader: true,
                data: data,
                success: function (data) {
                    jQuery('#orderexported-' + order + '-' + profile).find('a')[0].remove();
                    jQuery('#orderexported-' + order + '-' + profile).css({textDecoration: 'line-through'})
                }
            })
        }
    },
    _updateExportTo: function (item_id, value, url) {
        data = {item_id: item_id, value: value};
        jQuery.ajax({
            url: url,
            data: data,
            type: 'post',
            onFailure: function () {
                alert("Error : can't update the item.")
            }
        })
    }
};
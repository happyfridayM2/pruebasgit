require([
    'jquery'
], function ($) {
    'use strict';

    $('body').on('catalogCategoryAddToCartRedirect', function (event, data) {
        $(data.form).find('[name*="super"]').each(function (index, item) {
            var $item = $(item);

            //fix bug with adding not selected attributes to url
            if ($item.attr('data-attr-name') != undefined) {
                data.redirectParameters.push($item.attr('data-attr-name') + '=' + $item.val());
            }
        });
    });
});

define([
    'jquery',
    'Magento_Ui/js/modal/modal',
    'mage/template',
    'Amasty_Cart/js/amcart'
], function ($, modal, mageTemplate, amcart) {
    $.widget('mage.dataPostAjax', {
        options: {
            formTemplate: '<form action="<%- data.action %>" method="post">' +
            '<% _.each(data.data, function(value, index) { %>' +
            '<input name="<%- index %>" value="<%- value %>">' +
            '<% }) %></form>',
            postTrigger: "[]",
            formKeyInputSelector: 'input[name="form_key"]'
        },

        _create: function () {
            this._bind();
        },

        _bind: function () {
            var events = {};

            $.each(JSON.parse(this.options.postTrigger), function (index, value) {
                events['click ' + value] = '_postDataAction';
            });

            this._on(events);
        },

        _postDataAction: function (event) {
            event.preventDefault();
            var params = $(event.currentTarget).data('post-ajax'),
                formKey = $(this.options.formKeyInputSelector).val();
            if (formKey) {
                params.data['form_key'] = formKey;
            }
            params.data['is_ajax'] = 1;

            $('#confirmOverlay, #confirmBox').remove();
            if (params.action.indexOf('wishlist/index/cart') !== -1) {
                this.ajaxSubmit(params);
            } else {
                this.simpleAjaxSubmit(params);
            }
        },

        simpleAjaxSubmit: function (params) {
            $.ajax({
                url: params.action,
                data: params.data,
                type: 'post',
                dataType: 'json',
                showLoader: true,

                success: function (response) {
                    $('body, html').animate({
                        scrollTop: 0
                    }, 300);
                }
            });
        },

        ajaxSubmit: function (params) {
            var $form = $(mageTemplate(this.options.formTemplate, {
                data: params
            }));
            $form.amCart(window.amasty_cart_options);
            $form.submit();
        }
    });

    return $.mage.dataPostAjax;
});

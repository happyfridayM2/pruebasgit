define([
    "jquery",
    "Magento_Ui/js/modal/modal",
    "Magento_Customer/js/customer-data"
], function ($, modal, customerData) {

    $.widget('mage.amCartInfo', {
        options: {
            section: 'cart'
        },
        customerData: customerData,
        productIds: [],
        productInfo: '.product-item-info',
        productImage: '.product-image-photo',
        cartInfo: '.am-cart-info',

        _create: function () {
            this.productIds[this.options.section] = [];
            this.displayAddedQty();
        },

        displayAddedQty: function () {
            var self = this;
            this.element.on('contentUpdated', function () {
                self.updateCartInfo();
            });
        },

        updateCartInfo: function() {
            var self = this;
            var items = customerData.get(this.options.section)().items;
            var productsInCart = [];
            for (var i = 0; i < items.length; i++) {
                var productId = items[i].product_id,
                    product = this.getProduct(productId);
                if (product.length == 0) {
                    continue;
                }
                if (productsInCart[productId]) {
                    productsInCart[productId] = productsInCart[productId] + items[i].qty;
                } else {
                    productsInCart[productId] = items[i].qty;
                }
                if (typeof this.productIds[this.options.section][productId] === 'undefined') {
                    this.productIds[this.options.section][productId] = items[i].qty;
                    if (this.getHoverBlock(product).length == 0) {
                        this.addHover(product, this.productIds[this.options.section][productId]);
                    }
                    this.addSectionBlock(product, this.productIds[this.options.section][productId]);
                } else if (this.productIds[this.options.section][productId] != productsInCart[productId]) {
                    this.productIds[this.options.section][productId] = productsInCart[productId];
                    this.updateQty(product, this.productIds[this.options.section][productId]);
                }
            }

            this.productIds[this.options.section].forEach(function (element, index, object) {
                if (!productsInCart[index]) {
                    object.splice(index, 1);
                    self.removeHover(self.getProduct(index));
                }
            })
        },

        getProduct: function (productId) {
            var selector = '[data-product-id="' + productId + '"], ' +
                '[id="product-price-' + productId + '"], ' +
                '[name="product"][value="' + productId + '"]';
            var product = $(selector);

            return product.first();
        },

        addHover: function (product, qty) {
            var productInfo = product.closest(this.productInfo);
            if (productInfo.length > 0) {
                var productImage = productInfo.find('.product-image-photo');
                if (productImage.length > 0) {
                    var hoverBlock = $('<div></div>')
                            .css('display', 'none')
                            .attr('class', 'am-cart-info');
                    productImage.parent().append(hoverBlock);
                    productInfo.on('mouseover', function () {
                        if (productImage.parent().find(this.cartInfo).length > 0) {
                            productImage.addClass('mask');
                            $(productImage.parent()).find(this.cartInfo).show();
                        }
                    }.bind(this));
                    productInfo.on('mouseleave', function () {
                        if (productImage.parent().find(this.cartInfo).length > 0) {
                            productImage.removeClass('mask');
                            $(productImage.parent()).find(this.cartInfo).hide();
                        }
                    }.bind(this));
                }
            }
        },

        updateQty: function (product, qty) {
            var productInfo = product.closest(this.productInfo);
            if (productInfo.length > 0) {
                var productQty = productInfo.find('[data-amcart-section="' + this.options.section + '"] .qty');
                if (productQty.length > 0) {
                    productQty.html(qty);
                }
            }
        },

        removeHover: function (product) {
            var productInfo = product.closest(this.productInfo);
            if (productInfo.length > 0) {
                var productImage = productInfo.find(this.productImage);
                if (productImage.length > 0) {
                    var cartInfo = productImage.parent().find(this.cartInfo);
                    if (cartInfo.length > 0) {
                        cartInfo.remove();
                    }
                }
            }
        },

        addSectionBlock: function (product, qty) {
            var cartInfo = $('<div></div>')
                    .attr('data-amcart-section', this.options.section),
                qtyDiv = $('<div></div>')
                    .attr('class', 'qty')
                    .html(qty),
                messageDiv = $('<div></div>')
                    .html(this.options['infoMessage']);
            cartInfo.append(qtyDiv);
            cartInfo.append(messageDiv);
            this.getHoverBlock(product).append(cartInfo);
        },

        getHoverBlock: function (product) {
            return product.closest(this.productInfo).find(this.cartInfo);
        }
    });

    return $.mage.amCartInfo;
});

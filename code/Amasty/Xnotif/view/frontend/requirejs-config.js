/**
 *
 * @author    Amasty Team
 * @copyright Copyright (c) 2016 Amasty (http://www.amasty.com)
 * @package   Amasty_Xnotif
 */

var config = {
    map: {
        '*': {
            amnotification                      : 'Amasty_Xnotif/js/amnotification',
            'productSummary'                    : 'Amasty_Xnotif/js/bundle/product-summary',
            'magento-bundle.product-summary'    : 'Magento_Bundle/js/product-summary'
        }
    },
    deps: [
        'Magento_ConfigurableProduct/js/configurable'
    ],
    config: {
        mixins: {
            'mage/validation': {
                'Amasty_Xnotif/js/validation-mixin': true
            }
        }
    }
};

/*jshint browser:true jquery:true*/
/*global alert*/
var config = {
    map: {
        "*": {
            amastyDraggableFieldArray:   'Amasty_Checkout/js/draggable-field-array',
            amastySectionsRate:   'Amasty_Checkout/js/reports/sections-rate',
            amCharts: 'Amasty_Checkout/vendor/amcharts/amcharts',
            amChartsSerial: 'Amasty_Checkout/vendor/amcharts/serial'
        }
    },
    shim: {
        'Amasty_Checkout/vendor/amcharts/serial': ['Amasty_Checkout/vendor/amcharts/amcharts']
    }
};

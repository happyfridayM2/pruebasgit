/*
 * Copyright © 2019 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
*/


define([
    'Magento_Ui/js/form/element/textarea'], function (AbstractField) {
    'use strict';

    return AbstractField.extend({


        /**
         * @inheritdoc
         */
        setInitialValue: function () {
            if (typeof codemirror == "undefined") {
                var codemirror = new Array;
            }
            if (typeof parent == "undefined") {
                var parent = new Array;
            }
            var elt = "#" + this.uid
            parent[elt.id] = this;
            var initializer = null;
            initializer = setInterval(function () {

                if (jQuery(elt).length > 0) {

                    codemirror[elt.id] = CodeMirror.fromTextArea(jQuery(elt)[0], {
                        matchBrackets: true,
                        mode: "application/x-httpd-php",
                        indentUnit: 2,
                        indentWithTabs: false,
                        lineWrapping: true,
                        lineNumbers: true,
                        styleActiveLine: true,
                        autoRefresh: true
                    })
                    clearInterval(initializer);
                    codemirror[elt.id].on("blur", function (editor) {
                        jQuery(elt).val(editor.getValue());
                        parent[elt.id].value(editor.getValue())
                    });
                }
            }, 100);
            return this
                ._super();
        },


    });
});


/**
 * Copyright © 2019 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */
window.onload = function () {
    require(["jquery", "mage/mage"], function ($) {
        $(function () {
            CodeMirror = CodeMirror.fromTextArea(document.getElementById('script'), {
                matchBrackets: true,
                mode: "text/x-php",
                indentUnit: 2,
                indentWithTabs: false,
                lineWrapping: true,
                lineNumbers: false,
                styleActiveLine: true,
                autoRefresh: true
            });
        })
    })
};
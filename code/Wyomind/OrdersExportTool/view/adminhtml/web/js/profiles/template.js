/*
 * Copyright © 2019 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

define(['jquery',
    "Magento_Ui/js/modal/confirm",

    "Wyomind_Core/js/codemirror5/lib/codemirror",
    "Wyomind_Core/js/codemirror5/addon/selection/active-line",
    "Wyomind_Core/js/codemirror5/addon/edit/matchbrackets",
    "Wyomind_Core/js/codemirror5/mode/htmlmixed/htmlmixed",
    "Wyomind_Core/js/codemirror5/mode/xml/xml",
    "Wyomind_Core/js/codemirror5/mode/javascript/javascript",
    "Wyomind_Core/js/codemirror5/mode/css/css",
    "Wyomind_Core/js/codemirror5/mode/clike/clike",
    "Wyomind_Core/js/codemirror5/mode/php/php",
    "Wyomind_Core/js/codemirror5/addon/display/autorefresh",
], function ($, confirm, CodeMirror) {
    'use strict';
    return {
        current_type: "1",
        current_format: "1",
        CodeMirrorTxt: null,

        CodeMirrorBodyPattern: null,
        CodeMirrorHeaderPattern: null,
        CodeMirrorFooterPattern: null,

        codeMirror: function (element, type = "application/x-httpd-php") {
            return CodeMirror.fromTextArea(element, {
                matchBrackets: true,
                mode: type,
                indentUnit: 2,
                indentWithTabs: false,
                lineWrapping: true,
                lineNumbers: true,
                styleActiveLine: true,
                autoRefresh: true
            });

        },
        updateType: function (automatic) {

            var manual = false;
            if (automatic) {
                if (this.isXML(this.current_type) != this.isXML() || this.current_format !== this.getFormat()) {
                    confirm({
                        title: "",
                        content: "Changing file type/format will clear all your settings. Do you want to continue ?",
                        actions: {
                            confirm: function () {
                                manual = true;
                                this.updateTypeContinue(manual);

                            }.bind(this),
                            cancel: function () {
                                manual = false;
                                $('#type').val(this.current_type);
                                $('#format').val(this.current_format);
                                this.updateTypeContinue(manual);
                            }.bind(this)
                        }
                    });
                }
            } else {
                this.updateTypeContinue(manual);
            }
        },
        updateTypeContinue: function (manual) {
            var displayForXml = new Array("header", "body", "footer", "enclose_data");
            var displayForCsv = new Array("extra_header", "format", "include_header", "extra_footer", "separator", "protector", "escaper", "extra_footer");
            var displayForAdvancedCsv = new Array("extra_header", "format", "extra_footer", "body");
            var toEmpty = new Array("header", "body", "footer", "extra_header", "extra_footer");

            this.current_type = this.getType();
            this.current_format = this.getFormat();

            if (manual) { // manual change only
                // empty all text field
                toEmpty.each(function (id) {
                    $('#' + id).val("");
                });

                if (this.isXML() || this.isAdvanced()) {
                    $("#fields").remove();
                }
            }

            if (!this.isXML()) { // others
                displayForXml.each(function (id) {
                    $('#' + id).parent().parent().css({display: 'none'});
                    $('#' + id).removeClass("required-entry");
                });
                if (!this.isAdvanced()) {

                    displayForCsv.each(function (id) {
                        $('#' + id).parent().parent().css({display: 'block'});
                    });
                    this.displayTxtTemplate();
                }
                else {
                    displayForCsv.each(function (id) {
                        $('#' + id).parent().parent().css({display: 'none'});
                    });
                    displayForAdvancedCsv.each(function (id) {
                        $('#' + id).parent().parent().css({display: 'block'});
                    });

                }

            } else { // XML
                displayForXml.each(function (id) {
                    $('#' + id).parent().parent().css({display: 'block'});
                    $('#' + id).addClass("required-entry");
                });
                displayForCsv.each(function (id) {
                    $('#' + id).parent().parent().css({display: 'none'});
                });
            }

            if (manual) {
                this.CodeMirrorBodyPattern.setValue('');
                this.CodeMirrorHeaderPattern.setValue('');
                this.CodeMirrorFooterPattern.setValue('');
                this.CodeMirrorBodyPattern.refresh();
                this.CodeMirrorHeaderPattern.refresh();
                this.CodeMirrorFooterPattern.refresh();
            }
        },
        getType: function () {
            return $('#type').val();
        },
        getFormat: function () {
            return $('#format').val();
        },
        isXML: function (type) {
            if (typeof type === "undefined") {
                return $('#type').val() === "1";
            } else {
                return type === "1";
            }
        },
        isAdvanced: function (type) {
            if (typeof type === "undefined") {
                return $('#format').val() === "2";
            } else {
                return type === "2";
            }
        },
        displayTxtTemplate: function () {

            if ($("#fields").length === 0) {
                var content = "<br><br><div id='fields'>";
                content += "<b style='margin-left:28px'>Header</b>";
                content += "<b style='padding-left: 40%; margin-left: -48px;'>Field Pattern</b>";


                content += "<ul class=' fields-list' id='fields-list'></ul>";
                content += "<button type='button' class='add-field' onclick='require([\"oet_template\"], function (template) {template.addField(\"\",\"\",true); });'>Insert a new row</button><br/><br/><br/>";
                content += "<div class='overlay-txtTemplate'>\n\
                            <div class='container-txtTemplate'> \n\
                            <textarea id='codemirror-txtTemplate'>&nbsp;</textarea>\n\
                            <button type='button' class='validate' onclick='require([\"oet_template\"], function (template) {template.popup_validate(); });'>Validate</button>\n\
                            <button type='button' class='cancel' onclick='require([\"oet_template\"], function (template) {template.popup_close(); });'>Cancel</button>\n\
                            </div>\n\
                            </div>";
                content += "</div>";
                $(content).insertAfter("#extra_header-note");
                $("#footer").val("");
                if (this.isAdvanced()) {
                    $("#fields").addClass("advanced");
                }
                this.CodeMirrorTxt = this.codeMirror(document.getElementById('codemirror-txtTemplate'));


                $('.CodeMirror').resizable({
                    resize: function (editor) {
                        editor.setSize($(this).width(), $(this).height());
                    }
                });

                $("#fields-list").sortable({
                    revert: true,
                    axis: "y",

                    forcePlaceholderSize: true,

                    stop: function () {
                        this.fieldsToJson();
                    }.bind(this)
                });

                this.jsonToFields();
            } else {
                if (this.isAdvanced()) {
                    $("#fields").addClass("advanced");
                } else {
                    $("#fields").removeClass("advanced");
                }
            }
        },
        addField: function (header, body, refresh, elt) {

            var count = $("LI.txt-fields").length;
            var content = "<li class='txt-fields'>";
            content += "    <textarea class='txt-field header-txt-field input-text' >" + header.replace(/"/g, "&quot;") + "</textarea>";
            content += "    <textarea class='txt-field body-txt-field input-text' id='advanced-txt-field-" + count + "' >" + body.replace(/"/g, "&quot;") + "</textarea>";
            content += "    <button class='txt-field remove-field'>X</button>";
            content += "    <button class='txt-field add-field'>+</button>";
            content += "</li>";

            if (typeof elt == "undefined") {
                $("#fields-list").append(content);
            }
            if (refresh) {

                $(elt).parents('li').after(content);

            }

            if (this.isAdvanced()) {

                this.codeMirrorAdvanced.push(this.codeMirror(document.getElementById("advanced-txt-field-" + count)));
            }


        },
        removeField: function (elt) {

            $(elt).parents('li').remove();
            this.fieldsToJson();
        },
        fieldsToJson: function () {
            var data = new Object;
            data.header = new Array;
            var c = 0;
            $('.header-txt-field').each(function () {
                data.header[c] = $(this).val();
                c++;
            });
            data.body = new Array;
            c = 0;
            $('.body-txt-field').each(function () {
                data.body[c] = $(this).val();
                c++;
            });
            var pattern = '{"body":' + JSON.stringify(data.body) + "}";
            var header = '{"header":' + JSON.stringify(data.header) + "}";
            $("#body").val(pattern);
            $("#header").val(header);
            this.CodeMirrorBodyPattern.setValue(pattern);
            this.CodeMirrorHeaderPattern.setValue(header);
            this.CodeMirrorBodyPattern.refresh();
            this.CodeMirrorHeaderPattern.refresh();
        },
        jsonToFields: function () {
            var data = new Object;

            var header = [];
            if ($('#header').val() !== '') {
                try {
                    header = $.parseJSON($('#header').val()).header;
                } catch (e) {
                    header = [];
                }
            }

            var body = [];
            if ($('#body').val() !== '') {
                try {
                    body = $.parseJSON($('#body').val()).body;
                } catch (e) {
                    body = [];
                }
            }

            data.header = header;
            data.body = body;

            var i = 0;
            data.body.each(function () {
                this.addField(data.header[i], data.body[i], false);
                i++;
            }.bind(this));
        },
        popup_current: null,
        popup_close: function () {
            $(".overlay-txtTemplate").css({"display": "none"});
        },
        popup_validate: function () {
            $(this.popup_current).val(this.CodeMirrorTxt.getValue());
            this.current = null;
            this.popup_close();
            this.fieldsToJson();
        },
        popup_open: function (content, field) {
            $(".overlay-txtTemplate").css({"display": "block"});
            this.CodeMirrorTxt.refresh();
            this.CodeMirrorTxt.setValue(content);
            this.popup_current = field;
            this.CodeMirrorTxt.focus();
            $(".container-txtTemplate").draggable();

        },

    };
});
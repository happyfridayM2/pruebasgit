/**
 * Copyright © 2018 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */
define(["jquery"], function ($) {
    "use strict";
    return {
        currentAjaxRequest: null,
        patternTextarea: null, // codemirror
        previewArea: null, // codemirror
        libraryLoaded: false,
        expandedSize: {},
        hideAllArea: function () {
            $(".blackbox .area").hide();
        },
        showLoader: function () {
            $(".blackbox .area.loader").show();
        },
        hideLoader: function () {
            $(".blackbox .area.loader").hide();
        },
        showPreview: function () {
            $(".blackbox .area.preview").show();
        },
        showError: function (msg) {
            this.expand();
            $(".blackbox .area.error").show();
            $(".blackbox .error .msg").html(msg);
        },
        showLibrary: function (msg) {
            $(".blackbox .area.library").show();
            if (msg !== "") {
                $(".blackbox .area.library").html(msg);
            }
        },
        setActiveButton: function (btn) {
            $(".blackbox .button.active").removeClass("active");
            $(".blackbox .button." + btn).addClass("active");
        },
        refreshPreview: function () {
            this.expand();
            this.setActiveButton("preview");
            this.hideAllArea();
            this.showLoader();
            if (this.currentAjaxRequest !== null) {
                this.currentAjaxRequest.abort();
            }
            
            var previewUrl = $("span.preview").data("url");
            
            this.currentAjaxRequest = $.ajax({
                url: previewUrl,
                type: "POST",
                showLoader: false,
                data: {
                    sku: $("#preview-product-sku").val(),
                    name: $("input[name='template_name']").val(),
                    pattern: this.patternTextarea.getValue()
                },
                success: function (data) {
                    if (typeof data.pattern !== "undefined") {
                        this.hideLoader();
                        this.showPreview();
                        this.previewArea.setValue(data.pattern);
                        this.previewArea.refresh();
                    } else if (typeof data.error !== "undefined") {
                        this.hideLoader();
                        this.showError(data.error);
                    } else {
                        this.hideLoader();
                        this.showError(data);
                    }
                }.bind(this)
            });
        },
        refreshLibrary: function () {
            this.setActiveButton("library");
            this.hideAllArea();
            this.showLoader();
            if (this.currentAjaxRequest !== null) {
                this.currentAjaxRequest.abort();
            }
            
            if (this.libraryLoaded) {
                this.expand();
                this.hideLoader();
                this.showLibrary("");
                return;
            }
            
            var libraryUrl = $("span.library").data("url");
            
            this.currentAjaxRequest = $.ajax({
                url: libraryUrl,
                type: "GET",
                showLoader: false,
                data: {},
                success: function (data) {
                    if (typeof data !== "undefined") {
                        this.hideLoader();
                        this.showLibrary(data);
                        this.libraryLoaded = true;
                    }
                }.bind(this)
            });
        },
        setCookie: function (c_name, value, exdays) {
            var exdate = new Date();
            exdate.setDate(exdate.getDate() + exdays);
            var c_value = escape(value) + ((exdays === null) ? "" : "; expires=" + exdate.toUTCString());
            document.cookie = c_name + "=" + c_value + "; path=/;";
        },
        getCookie: function (c_name) {
            var c_value = document.cookie;
            var c_start = c_value.indexOf(" " + c_name + "=");
            if (c_start === -1) {
                c_start = c_value.indexOf(c_name + "=");
            }
            if (c_start === -1) {
                c_value = null;
            } else {
                c_start = c_value.indexOf("=", c_start) + 1;
                var c_end = c_value.indexOf(";", c_start);
                if (c_end === -1) {
                    c_end = c_value.length;
                }
                c_value = unescape(c_value.substring(c_start, c_end));
            }
            return c_value;
        },
        savePosition: function (position) {
            var top = position.top;
            var left = position.left;
            if (top < 0) {
                top = 0;
            }
            if (left < 0) {
                left = 0;
            }
            if (top > $(window).height() - 20) {
                top = $(window).height() - 20;
            }
            if (left > $(window).width() - 20) {
                left = $(window).width() - 20;
            }
            this.setCookie("blackbox.top", top);
            this.setCookie("blackbox.left", left);
            $(".blackbox .button.window").removeClass("window").addClass("fullscreen");
        },
        saveSize: function (size) {
            this.setCookie("blackbox.width", size.width);
            this.setCookie("blackbox.height", size.height);
            $(".blackbox .button.window").removeClass("window").addClass("fullscreen");
            $(".blackbox .button.expand").removeClass("expand").addClass("collapse");
        },
        setPositionAndSize: function () {
            var top = this.getCookie("blackbox.top");
            var left = this.getCookie("blackbox.left");
            var width = this.getCookie("blackbox.width");
            var height = this.getCookie("blackbox.height");
            if (top === null) {
                top = 380;
            }
            if (left === null) {
                left = 1300;
            }
            if (width === null) {
                width = 490;
            }
            if (height === null) {
                height = 380;
            }
            $(".blackbox .resizable").css({
                "width": width + "px",
                "height": height + "px"
            });
            $(".blackbox.draggable").css({
                "top": top + "px",
                "left": left + "px",
                "display": "block"
            });
        },
        maximize: function () {
            $(".blackbox.draggable").css({
                "top": 3 + "px",
                "left": 91 + "px",
                "display": "block"
            });
            $(".blackbox .resizable").css({
                "width": ($(window).width() - 106) + "px",
                "height": ($(window).height() - 18) + "px"
            });
            $(".blackbox .button.fullscreen").removeClass("fullscreen").addClass("window");
            $(".blackbox .button.expand").removeClass("expand").addClass("collapse");
        },
        minimize: function () {
            this.setPositionAndSize();
            $(".blackbox .button.window").removeClass("window").addClass("fullscreen");
            $(".blackbox .button.expand").removeClass("expand").addClass("collapse");
        },
        collapse: function () {
            this.expandedSize = {
                "height": $(".blackbox .resizable").height(), 
                "width": $(".blackbox .resizable").width()
            };
            $(".blackbox .resizable").css({
                "height": 49 + "px"
            });
            $(".blackbox .button.collapse").removeClass("collapse").addClass("expand");
            $(".blackbox .button.window").removeClass("window").addClass("fullscreen");
        },
        expand: function () {
            if ($(".blackbox .button.expand").hasClass("expand")) {
                $(".blackbox .resizable").css({
                    "width": this.expandedSize.width + "px",
                    "height": this.expandedSize.height + "px"
                });
                $(".blackbox .button.expand").removeClass("expand").addClass("collapse");
            }
        }
    };
});
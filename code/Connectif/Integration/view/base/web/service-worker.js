(function e(t,n,r){function s(o,u){if(!n[o]){if(!t[o]){var a=typeof require=="function"&&require;if(!u&&a)return a(o,!0);if(i)return i(o,!0);throw new Error("Cannot find module '"+o+"'")}var f=n[o]={exports:{}};t[o][0].call(f.exports,function(e){var n=t[o][1][e];return s(n?n:e)},f,f.exports,e,t,n,r)}return n[o].exports}var i=typeof require=="function"&&require;for(var o=0;o<r.length;o++)s(r[o]);return s})({1:[function(require,module,exports){
"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
self.addEventListener('install', function (event) {
    event.waitUntil(self.skipWaiting());
});
self.addEventListener('activate', function (event) {
    event.waitUntil(self.clients.claim());
});
self.addEventListener('push', function (event) {
    if (!event.data) {
        console.error('[Service Worker] No data found on event received');
        return;
    }
    var dataJson = event.data.json();
    var now = new Date();
    if (dataJson.options.data.expirationDate && (new Date(dataJson.options.data.expirationDate) <= now)) {
        return;
    }
    var title = dataJson.title;
    var options = dataJson.options;
    if (!('image' in Notification.prototype)) {
        // No rich notifications allowed, set compact
        options.image = undefined;
        if (options.data.compact && options.data.compact.icon && options.data.compact.icon !== '') {
            options.icon = options.data.compact.icon;
        }
        if (options.data.compact && options.data.compact.body && options.data.compact.body !== '') {
            options.body = options.data.compact.body;
        }
    }
    event.waitUntil(self.registration.showNotification(title, options).then(function () {
        return fetch(options.data.trackOpenEndpoint);
    }));
});
self.addEventListener('notificationclick', function (event) {
    var actionData = event.notification.data[event.action] || event.notification.data['notificationClick'];
    if (!actionData) {
        return;
    }
    if (actionData.closeOnClick) {
        event.notification.close();
    }
    if (actionData.actionTypeId === 'open-new-window') {
        if (!actionData.redirectUrl) {
            return;
        }
        return self.clients.openWindow(actionData.redirectUrl);
    }
}, false);

},{}]},{},[1])
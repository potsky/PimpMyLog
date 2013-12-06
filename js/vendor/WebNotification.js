/** @license
 * WebNotification.js <https://github.com/auchenberg/WebNotification.js/>
 * Released under the MIT license
 * Author: Kenneth Auchenberg
 * Version: 0.1.0
 */
 /* global define */


;(function(window) {

  'use strict';

  var NativeNotification = window.Notification,
      PrefixedNotification = window.webkitNotifications;

  var utils = {
    isFunction: function(obj) {
      return typeof obj === 'function';
    }
  };

  function WebNotification(title, options) {
    return new NativeNotification(title, options);
  }

  WebNotification.getPermission = function() {

    if (NativeNotification) {

      // Official W3C/WHATWG Web Notifications API
      if (NativeNotification.permission) {
        return NativeNotification.permission;
      }

      // Older WebKit API
      if (utils.isFunction(NativeNotification.permissionLevel)) {
        return NativeNotification.permissionLevel();
      }
    }

    // Oldprefixed WebKit API
    if (PrefixedNotification && utils.isFunction(PrefixedNotification.checkPermission)) {
      switch (PrefixedNotification.checkPermission()) {
        case 0:
          return 'granted';
        case 1:
          return 'default';
        case 2:
          return 'denied';
      }
    }

    return 'default';
  };

  WebNotification.requestPermission = function(callback) {
    var context = this;

    function _onPermissionRequested() {
      context.permission = context.getPermission();

      if (callback) {
        callback.call(context);
      }
    }

    // Native first, then prefxied
    if (NativeNotification && utils.isFunction(NativeNotification.requestPermission)) {
      NativeNotification.requestPermission(_onPermissionRequested);
    } else if (PrefixedNotification && utils.isFunction(PrefixedNotification.requestPermission)) {
      PrefixedNotification.requestPermission(_onPermissionRequested);
    } else {
      throw 'Could not call requestPermission';
    }

  };

  WebNotification.permission = WebNotification.getPermission();

  if (typeof define === 'function' && define.amd) {
    define(function() {
      if('Notification' in window) {
        window.Notification = WebNotification;
        return WebNotification;
      }
    });
  } else {
    if('Notification' in window) {
      window.Notification = WebNotification;
    }
  }

})(this);

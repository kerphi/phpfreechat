/*jslint node: true, maxlen: 150, maxerr: 50, indent: 2 */
'use strict';

/**
 * phpfreechat's JQuery plugin
 * http://www.phpfreechat.net
 */
var phpFreeChat = (function (pfc, $, window, undefined) {

  var pluginName = 'phpfreechat';
  var document = window.document;
  var defaults = {
    // phpfreechat server url
    serverUrl: '../server',

    // phpfreechat package.json url
    packageUrl: '../package.json',

    // callback executed when interface is loaded
    loaded: null,
    
    // load interface data (only used for tests and design work)
    loadTestData: false,
    
    // time to wait between each message check
    refresh_delay: 5000,
    
     // Setting this to true will give the focus to the input text box when connecting to the chat
    focus_on_connect: true,
    
    // if true a backlink to phpfreechat must be present in the page (see license page for more info)
    check_backlink: true,

    // if true powered by phpfreechat text is shown
    show_powered_by: true
  };

  function Plugin(element, options) {
    // plugin attributs
    this.element = element;
    this.options = $.extend({}, defaults, options);
    this._defaults = defaults;
    this._name = pluginName;

    // run phpfreechat stuff
    pfc.init(this);
  }

  // connect as a jquery plugin
  // multiple instantiations are forbidden
  $.fn[pluginName] = function (options) {
    return this.each(function () {
      if (!$.data(this, 'plugin_' + pluginName)) {
        $.data(this, 'plugin_' + pluginName, new Plugin(this, options));
      }
    });
  }

  return pfc;
}(phpFreeChat || {}, jQuery, window));
/**
 * phpfreechat's JQuery plugin
 * http://www.phpfreechat.net
 */
var phpFreeChat = (function (pfc, $, window, undefined) {

  var pluginName = 'phpfreechat',
      document = window.document,
      defaults = {
        serverUrl: '../server', // phpfreechat server url
        loaded: null,           // executed when interface is loaded
        loadTestData: false,    // load interface data for tests
        refresh_delay: 5000,
      };

  function Plugin(element, options) {
    // plugin attributs
    this.element = element;
    this.options = $.extend({}, defaults, options) ;
    this._defaults = defaults;
    this._name = pluginName;

    // run phpfreechat stuff
    pfc.init(this);    
  }

  // connect as a jquery plugin
  // multiple instantiations are forbidden
  $.fn[pluginName] = function ( options ) {
      return this.each(function () {
          if (!$.data(this, 'plugin_' + pluginName)) {
              $.data(this, 'plugin_' + pluginName, new Plugin( this, options ));
          }
      });
  }
  
  return pfc;
}(phpFreeChat || {}, jQuery, window));
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

    // phpfreechat check.php url
    serverCheckUrl: '../check.php',
    
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
    show_powered_by: true,
    
    // set it to true if PUT and DELETE http methods are not allowed by the server
    use_post_wrapper: true,
    
    // when true, the first AJAX request is used to verify that server config is ok
    check_server_config: true,
    
    // number of tolerated network error before stoping chat refresh
    tolerated_network_errors: 5,
    
    // flag used to force skiping intro message about donation
    skip_intro: false,

    // skip login step ? (if true, chat will not be usable)
    skip_auth: false,
    
    // show user avatar or not
    show_avatar: false
  };

  function Plugin(element, options) {

    // to be sure options.serverUrl is filled
    options = $.extend({}, options);
    if (!options || !options.serverUrl) {
      options.serverUrl = defaults.serverUrl;
    }
    
    // adjust the packageUrl parameter if serverUrl is specified
    if (!options || !options.packageUrl) {
      options.packageUrl = options.serverUrl + '/../package.json';
    }
    // same for serverCheckUrl
    if (!options || !options.serverCheckUrl) {
      options.serverCheckUrl = options.serverUrl + '/../check.php';
    }
    
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
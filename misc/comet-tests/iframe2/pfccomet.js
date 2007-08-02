/**
 * This class is used to get data from server using a persistent connexion
 * thus the clients are informed in realtime from the server changes (very usefull for a chat application)
 * Usage:
 *   var comet = new pfcComet({'url': './cometbackend.php', 'id': 1});
 *   comet.onResponse   = function(comet,data) { alert('response:'+data); };
 *   comet.onConnect    = function(comet) { alert('connected'); };
 *   comet.onDisconnect = function(comet) { alert('disconnected'); };
 */
var pfcComet = Class.create();
pfcComet.prototype = {

  url: null,
  _isconnected: false,

  initialize: function(params) {
    if (!params) params = {};
    if (!params['url']) alert('error: url parameter is mandatory');
    this.url = params['url'];
  },

  connect: function()
  {
    if (this._isconnected) return;
    this._openPersistentConnexion();
  },

  disconnect: function()
  {
    if (!this._isconnected) return;
    this._onDisconnect();
  },

  _openPersistentConnexion: function()
  {
    this._iframe    = null;
    this._iframediv = null;
    Event.observe(window, "unload", this._onDisconnect);

    if (navigator.appVersion.indexOf("MSIE") != -1) {

      // For IE browsers
      this._iframe = new ActiveXObject("htmlfile");
      this._iframe.open();
      this._iframe.write("<html>");
      this._iframe.write("<script>document.domain = '"+document.domain+"'");
      this._iframe.write("</html>");
      this._iframe.close();
      this._iframediv = this._iframe.createElement("div");
      this._iframe.appendChild(this._iframediv);
      this._iframe.parentWindow.pfccomet = this;
      this._iframediv.innerHTML = '<iframe id="comet_iframe" src="' + this.url + '"></iframe>';

    } else if (navigator.appVersion.indexOf("KHTML") != -1) {

      // for KHTML browsers
      this._iframe = document.createElement('iframe');
      this._iframe.setAttribute('id',  'comet_iframe');
      this._iframe.setAttribute('src', this.url);
      with (this._iframe.style) {
        position   = "absolute";
        left       = top   = "-100px";
        height     = width = "1px";
        visibility = "hidden";
      }
      document.body.appendChild(this._iframe);

    } else {
    
      // For other browser (Firefox...)
      this._iframe = document.createElement('iframe');
      this._iframe.setAttribute('id', 'comet_iframe');
      with (this._iframe.style) {
        left       = top   = "-100px";
        height     = width = "1px";
        visibility = "hidden";
        display    = 'none';
      }
      this._iframediv = document.createElement('iframe');
      this._iframediv.setAttribute('src', this.url);
      this._iframe.appendChild(this._iframediv);
      document.body.appendChild(this._iframe);

    }
  },

  _onConnect: function()
  {
    this._isconnected = true;
    this.onConnect(this);
  },

  _onDisconnect: function()
  {
    if (this._iframe) {
      if (navigator.appVersion.indexOf("MSIE") == -1 &&
          navigator.appVersion.indexOf("KHTML") == -1) // or Konqueror will crash
      {
        this._iframe.remove();
      }
      this._iframe = false; // release the iframe to prevent problems with IE when reloading the page
    }
    this._isconnected = false;
    this.onDisconnect(this);
  },

  _onResponse: function(data)
  {
    this.onResponse(this,data);
  },

  /**
   * User's callbacks
   */
  onResponse: function(pfccomet, data) {},
  onConnect: function(pfccomet) {},
  onDisconnect: function(pfccomet) {}
}

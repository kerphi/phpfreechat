/**
 * This class is used to get data from server using a persistent connexion
 * thus the clients are informed in realtime from the server changes (very usefull for a chat application)
 * Usage:
 *   var comet = new pfcComet({'url': './cometbackend.php', 'id': 1});
 *   comet.onRead       = function(req) { alert('id:'+req['id']+' response:'+req['data']); };
 *   comet.onConnect    = function(comet) { alert('connected'); };
 *   comet.onDisconnect = function(comet) { alert('disconnected'); };
 */
var pfcComet = Class.create();
pfcComet.prototype = {

  read_url: null,
  id: 0,
  timeout: 5000,

  _noerror: false,
  _ajax: null,
  _isconnected: false,

  initialize: function(params) {
    if (!params) params = {};
    if (!params['read_url']) alert('error: read_url parameter is mandatory');
//    if (!params['write_url']) alert('error: write_url parameter is mandatory');
    this.read_url       = params['read_url'];
    if (params['id'])      this.id = params['id'];
    if (params['timeout']) this.timeout = params['timeout'];
  },

  connect: function()
  {
    if (this._isconnected) return;
    this._isconnected = true;
    this.onConnect(this);
    this._waitForData();
  },

  disconnect: function()
  {
    if (!this._isconnected) return;
    this._isconnected = false;
    this.onDisconnect(this);
    // remove the registred callbacks in order to ignore the next response
    this._ajax.transport.abort();
//    this._ajax.options.onSuccess =  null;
//    this._ajax.options.onComplete = null;
  },

  write: function(data)
  {
  },

  _waitForData: function()
  {
    if (!this._isconnected) return;

    this._ajax = new Ajax.Request(this.read_url, {
      method: 'get',
      parameters: { 'id' : this.id },
      onSuccess: function(transport) {
        // handle the server response
        var response = transport.responseText.evalJSON();
        this.comet.id = response['id'];
        this.comet.onResponse(response);
        this.comet._noerror = true;
      },
      onComplete: function(transport) {
        // send a new ajax request when this request is finished
        if (!this.comet._noerror)
          // if a connection problem occurs, try to reconnect periodicaly
          setTimeout(function(){ this.comet._waitForData(); }.bind(this), this.comet.timeout); 
        else
          // of wait for the next data
          this.comet._waitForData();
        this.comet._noerror = false;
      }
    });
    this._ajax.comet = this;
  },

  /**
   * User's callbacks
   */
  onRead: function(data)
  {
  },
  onWrite: function(data)
  {
  },
  onConnect: function(comet)
  {
  },
  onDisconnect: function(comet)
  {
  },
}

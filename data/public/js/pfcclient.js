// Browser detection mostly taken from prototype.js 1.5.1.1.
var is_ie     = !!(window.attachEvent && !window.opera);
var is_khtml  = !!(navigator.appName.match("Konqueror") || navigator.appVersion.match("KHTML"));
var is_gecko  = navigator.userAgent.indexOf('Gecko') > -1 && navigator.userAgent.indexOf('KHTML') == -1;
var is_ie7    = navigator.userAgent.indexOf('MSIE 7') > 0;
var is_opera  = !!window.opera;
var is_webkit = navigator.userAgent.indexOf('AppleWebKit/') > -1;

/**
 * This class is the client part of phpFreeChat
 * (depends on prototype library)
 * @author Stephane Gully
 */
var pfcClient = Class.create();

//defining the rest of the class implmentation
pfcClient.prototype = {
  
  initialize: function()
  {    
    // load the graphical user interface builder
    this.gui = new pfcGui();
    // load the resources manager (labels and urls)
    this.res = new pfcResource();

    this.nickname = pfc_nickname;
    this.nickid   = pfc_nickid;
    this.usermeta = $H();
    this.chanmeta = $H();
    this.nickwhoisbox = $H();

    // this array contains all the sent commands
    // use the up and down arrow key to navigate through the history
    this.cmdhistory   = Array();
    this.cmdhistoryid = -1;
    this.cmdhistoryissearching = false;
    
    /*
    this.channels      = Array();
    this.channelids    = Array();
    */
    this.privmsgs      = Array();
    this.privmsgids    = Array();
    
    this.timeout            = null;
    this.timeout_time       = new Date().getTime();

    this.refresh_delay       = pfc_refresh_delay;
    this.refresh_delay_steps = pfc_refresh_delay_steps;
    this.last_response_time = new Date().getTime();
    this.last_request_time  = new Date().getTime();
    this.last_activity_time = new Date().getTime();

    /* unique client id for each windows used to identify a open window
     * this id is passed every time the JS communicate with server
     * (2 clients can use the same session: then only the nickname is shared) */
    this.clientid      = pfc_clientid;

    this.isconnected   = false;
    this.nicklist      = $H();
    this.nickcolor     = Array();
    this.colorlist     = Array();

    this.blinktmp     = Array();
    this.blinkloop    = Array();
    this.blinktimeout = Array(); 
  },

  loadChat: function() {
    new Ajax.Request(pfc_server_script_url, {
      method: 'get',
      parameters: {pfc_ajax: 1, f: 'loadChat'},
      onSuccess: function(transport) {
        eval( transport.responseText );
      }
    });
  },

  connectListener: function()
  {
    this.el_words     = $('pfc_words');
    this.el_handle    = $('pfc_handle');
    this.el_container = $('pfc_container');
//    this.el_online    = $('pfc_online');
    this.el_errors    = $('pfc_errors');

    this.detectactivity = new DetectActivity(this.el_container);
    // restore the window title when user come back to the active zone
    if (pfc_notify_window) this.detectactivity.onunactivate = this.gui.unnotifyWindow.bindAsEventListener(this.gui);

    /* the events callbacks */
    this.el_words.onkeypress = this.callbackWords_OnKeypress.bindAsEventListener(this);
// don't use this line because when doing completeNick the "return false" doesn't work (focus is lost)
//    Event.observe(this.el_words,     'keypress',  this.callbackWords_OnKeypress.bindAsEventListener(this), false);
    Event.observe(this.el_words,     'keydown',   this.callbackWords_OnKeydown.bindAsEventListener(this), false);
    Event.observe(this.el_words,     'keyup',     this.callbackWords_OnKeyup.bindAsEventListener(this), false);
    Event.observe(this.el_words,     'mouseup',   this.callbackWords_OnMouseup.bindAsEventListener(this), false);
    Event.observe(this.el_words,     'focus',     this.callbackWords_OnFocus.bindAsEventListener(this), false);
    Event.observe(document.body,     'unload',    this.callback_OnUnload.bindAsEventListener(this), false);
  },

  refreshGUI: function()
  {
    this.minmax_status = pfc_start_minimized;
    var cookie = getCookie('pfc_minmax_status');
    if (cookie != null)
      this.minmax_status = (cookie == 'true');
    
    cookie = getCookie('pfc_nickmarker');
    this.nickmarker = (cookie == 'true');
    if (cookie == '' || cookie == null)
      this.nickmarker = pfc_nickmarker;
    
    cookie = getCookie('pfc_clock');
    this.clock = (cookie == 'true');
    if (cookie == '' || cookie == null)
      this.clock = pfc_clock;

    cookie = getCookie('pfc_showsmileys');
    this.showsmileys = (cookie == 'true');
    if (cookie == '' || cookie == null)
      this.showsmileys = pfc_showsmileys;
    
    cookie = getCookie('pfc_showwhosonline');
    this.showwhosonline = (cookie == 'true');
    if (cookie == '' || cookie == null)
      this.showwhosonline = pfc_showwhosonline;

    // '' means no forced color, let CSS choose the text color
    this.current_text_color = '';
    cookie = getCookie('pfc_current_text_color');
    if (cookie != null)
      this.switch_text_color(cookie);

    cookie = getCookie('pfc_issoundenable');
    this.issoundenable = (cookie == 'true');
    if (cookie == '' || cookie == null)
      this.issoundenable = pfc_startwithsound;

    this.refresh_loginlogout();
    this.refresh_minimize_maximize();
    this.refresh_Smileys();
    this.refresh_sound();
    this.refresh_nickmarker();
  },

  /**
   * Show a popup dialog to ask user to choose a nickname
   */
  askNick: function(nickname,error_text)
  {
    // ask to choose a nickname
    if (nickname == '' || nickname == undefined) nickname = this.nickname;

    // build a dhtml prompt box
    var pfcp = this.getPrompt();//new pfcPrompt($('pfc_container'));
    pfcp.callback = function(v) { pfc.askNickResponse(v); }
    pfcp.prompt((error_text != undefined ? '<span style="color:red">'+error_text+'</span><br/>' : '')+this.res.getLabel('Please enter your nickname'), nickname);
    pfcp.focus();
  },
  askNickResponse: function(newnick)
  {
    if (newnick)
    {
      if (this.isconnected)
        this.sendRequest('/nick "'+newnick+'"');
      else
        this.sendRequest('/connect "'+newnick+'"');
    }
  },

  /**
   * Reacte to the server response
   */
  handleResponse: function(cmd, resp, param)
  {
    // display some debug messages
    if (pfc_debug)
      if (cmd != "update")
      {
        var param2 = param;
        if (cmd == "who" || cmd == "who2")
        {
          param2 = $H(param2);
          param2.set('meta', $H(param2.get('meta')));
          param2.get('meta').set('users', $H(param2.get('meta').get('users')));
          trace('handleResponse: '+cmd + "-"+resp+"-"+param2.inspect());
        }
        else
        if (cmd == "whois" || cmd == "whois2")
        {
          param2 = $H(param2);
          trace('handleResponse: '+cmd + "-"+resp+"-"+param2.inspect());
        }
        else
        if (cmd == "getnewmsg" || cmd == "join")
        {
          param2 = $A(param2);
          trace('handleResponse: '+cmd + "-"+resp+"-"+param2.inspect());
        }
        else
          trace('handleResponse: '+cmd + "-"+resp+"-"+param);
      }

    if (cmd != "update") 
    {
       // speed up timeout if activity
       this.last_activity_time = new Date().getTime();
       var delay = this.calcDelay();
       if (this.timeout_time - new Date().getTime() > delay)
       {
          clearTimeout(this.timeout);
          this.timeout = setTimeout('pfc.updateChat(true)', delay);
          this.timeout_time = new Date().getTime() + delay;
       }
    }

    if (cmd == "connect")
    {
      if (resp == "ok")
      {
        this.nickname = param[0]; 
        this.isconnected = true;

        // start the polling system
        this.updateChat(true);
      }
      else
        this.isconnected = false;
      this.refresh_loginlogout();
    }
    else if (cmd == "quit")
    {
      if (resp =="ok")
      {
        // stop updates
        this.updateChat(false);
        this.isconnected = false;
        this.refresh_loginlogout();
      }
    }
    else if (cmd == "join" || cmd == "join2")
    {
      if (resp =="ok")
      {
        // create the new channel
        var tabid = param[0];
        var name  = param[1];
        this.gui.createTab(name, tabid, "ch");
        if (cmd != "join2" || this.gui.tabs.length == 1) this.gui.setTabById(tabid);
        this.refresh_Smileys();
        this.refresh_WhosOnline();
      }
      else if (resp == "max_channels")
      {
        this.displayMsg( cmd, this.res.getLabel('Maximum number of joined channels has been reached') );
      }
      else
        alert(cmd + "-"+resp+"-"+param);
    }
    else if (cmd == "leave")
    {
      if (resp =="ok")
      {
        // remove the channel
        var tabid = param;
        this.gui.removeTabById(tabid);

        // synchronize the channel client arrays
        /*
        var index = -1;
        index = this.channelids.indexOf(tabid);
        this.channelids = this.channelids.without(tabid);
        this.channels   = this.channels.without(this.channels[index]);
        */
        
        // synchronize the privmsg client arrays
        index = -1;
        index = indexOf(this.privmsgids, tabid);
        this.privmsgids = without(this.privmsgids, tabid);
        this.privmsgs   = without(this.privmsgs, this.privmsgs[index]);
        
      }
    }
    else if (cmd == "privmsg" || cmd == "privmsg2")
    {
      if (resp == "ok")
      {
        // create the new channel
        var tabid = param[0];
        var name  = param[1];
        this.gui.createTab(name, tabid, "pv");
        if (cmd != "privmsg2" || this.gui.tabs.length == 1) this.gui.setTabById(tabid);
        
        this.privmsgs.push(name);
        this.privmsgids.push(tabid);
        
      }
      else if (resp == "max_privmsg")
      {
        this.displayMsg( cmd, this.res.getLabel('Maximum number of private chat has been reached') );
      }
      else if (resp == "unknown")
      {
        // speak to unknown user
        this.displayMsg( cmd, this.res.getLabel('You are trying to speak to a unknown (or not connected) user') );
      }
      else if (resp == "speak_to_myself")
      {
        this.displayMsg( cmd, this.res.getLabel('You are not allowed to speak to yourself') );
      }
      else
        alert(cmd + "-"+resp+"-"+param);
    }
    else if (cmd == "nick")
    {
      // give focus the the input text box if wanted
      if (pfc_focus_on_connect) this.el_words.focus();

      if (resp == "connected" || resp == "notchanged")
      {
        cmd = '';
      }

      if (resp == "ok" || resp == "notchanged" || resp == "changed" || resp == "connected")
      {
        this.setUserMeta(this.nickid, 'nick', param);
        this.el_handle.innerHTML = this.getUserMeta(this.nickid, 'nick').escapeHTML();
        this.nickname = this.getUserMeta(this.nickid, 'nick');
        this.updateNickBox(this.nickid);

        // clear the possible error box generated by the bellow displayMsg(...) function
        this.clearError(Array(this.el_words));
      }
      else if (resp == "isused")
      {
        this.setError(this.res.getLabel('Chosen nickname is already used'), Array());
        this.askNick(param,this.res.getLabel('Chosen nickname is already used'));
      }
      else if (resp == "notallowed")
      {
        // When frozen_nick is true and the nickname is already used, server will return
        // the 'notallowed' status. It will display a message and stop chat update.
        // If the chat update is not stopped, this will loop forever 
        // as long as the forced nickname is not changed.

        // display a message
        this.setError(this.res.getLabel('Chosen nickname is not allowed'), Array());
        // then stop chat updates
        this.updateChat(false);
        this.isconnected = false;
        this.refresh_loginlogout();
      }
    }
    else if (cmd == "update")
    {
    }
    else if (cmd == "version")
    {
      if (resp == "ok")
      {
        this.displayMsg( cmd, this.res.getLabel('phpfreechat current version is %s',param) );
      }
    }
    else if (cmd == "help")
    {
      if (resp == "ok")
      {
        this.displayMsg( cmd, param);
      }
    }
    else if (cmd == "rehash")
    {
      if (resp == "ok")
      {
        this.displayMsg( cmd, this.res.getLabel('Configuration has been rehashed') );
      }
      else if (resp == "ko")
      {
        this.displayMsg( cmd, this.res.getLabel('A problem occurs during rehash') );
      }
    }
    else if (cmd == "banlist")
    {
      if (resp == "ok" || resp == "ko")
      {
        this.displayMsg( cmd, param );
      }
    }
    else if (cmd == "unban")
    {
      if (resp == "ok" || resp == "ko")
      {
        this.displayMsg( cmd, param );
      }
    }
    else if (cmd == "auth")
    {
      if (resp == "ban")
      {
        alert(param);
      }
      if (resp == "frozen")
      {
        alert(param);
      }
      else if (resp == "nick")
      {
        this.displayMsg( cmd, param );
      }
    }
    else if (cmd == "debug")
    {
      if (resp == "ok" || resp == "ko")
      {
        this.displayMsg( cmd, param );
      }
    }
    else if (cmd == "clear")
    {
      var tabid     = this.gui.getTabId();
      var container = this.gui.getChatContentFromTabId(tabid);
      container.innerHTML = "";
    }    
    else if (cmd == "identify")
    {
      this.displayMsg( cmd, param );
    }
    else if (cmd == "checknickchange")
    {
      this.displayMsg( cmd, param );
    }
    else if (cmd == "whois" || cmd == "whois2")
    {
      param = $H(param);
      var nickid = param.get('nickid');
      if (resp == "ok")
      {
        this.setUserMeta(nickid, param);
        this.updateNickBox(nickid);
        this.updateNickWhoisBox(nickid);
      }
      if (cmd == "whois")
      {
        // display the whois info
        var um = this.getAllUserMeta(nickid);
        var um_keys = um.keys();
        var msg = '';
        for (var i=0; i<um_keys.length; i++)
        {
          var k = um_keys[i];
          var v = um.get(k);
          if (v &&
              // these parameter are used internaly (don't display it)
              k != 'nickid' &&
              k != 'floodtime' &&
              k != 'flood_nbmsg' &&
              k != 'flood_nbchar')
            msg = msg + '<strong>' + k + '</strong>: ' + v + '<br/>';
        }
        this.displayMsg( cmd, msg );
      }
    }
    else if (cmd == "who" || cmd == "who2")
    {
      param = $H(param);
      var chan   = param.get('chan');
      var chanid = param.get('chanid');
      var meta   = $H(param.get('meta'));
      meta.set('users', $H(meta.get('users')));
      if (resp == "ok") 
      { 
        this.setChanMeta(chanid,meta);
        // send /whois commands for unknown users 
        for (var i=0; i<meta.get('users').get('nickid').length; i++)
        {
          var nickid = meta.get('users').get('nickid')[i];
          var nick   = meta.get('users').get('nick')[i];
          var um = this.getAllUserMeta(nickid);  
          if (!um) this.sendRequest('/whois2 "'+nickid+'"');
        }

        // update the nick list display on the current channel
        this.updateNickListBox(chanid);
      }
      if (cmd == "who")
      {
        // display the whois info
        var cm = this.getAllChanMeta(chanid);
        var cm_keys = cm.keys();
        var msg = '';
        for (var i=0; i<cm_keys.length; i++)
        {
          var k = cm_keys[i];
          var v = cm[k];
          if (k != 'users')
          {
            msg = msg + '<strong>' + k + '</strong>: ' + v + '<br/>';
          }
        }
        this.displayMsg( cmd, msg );
      }
    }
    else if (cmd == "getnewmsg")
    {
      if (resp == "ok") 
      {
        this.handleComingRequest(param);
      }
    }
    else if (cmd == "send")
    {
    }
    else
      alert(cmd + "-"+resp+"-"+param);
  },
  
  getAllUserMeta: function(nickid)
  {
    if (nickid && this.usermeta.get(nickid))
      return this.usermeta.get(nickid);
    else
      return null;
  },

  getUserMeta: function(nickid, key)
  {
    if (nickid && key && this.usermeta.get(nickid) && this.usermeta.get(nickid).get(key))
      return this.usermeta.get(nickid).get(key);    
    else
      return '';
  },

  setUserMeta: function(nickid, key, value)
  {
    if (nickid && key)
    {
      if (!this.usermeta.get(nickid)) this.usermeta.set(nickid, $H());
      if (value)
        this.usermeta.get(nickid).set(key, value);
      else
        this.usermeta.set(nickid, $H(key));
    }
  },

  getAllChanMeta: function(chanid)
  {
    if (chanid && this.chanmeta.get(chanid))
      return this.chanmeta.get(chanid);
    else
      return null;
  },

  getChanMeta: function(chanid, key)
  {
    if (chanid && key && this.chanmeta.get(chanid) && this.chanmeta.get(chanid).get(key))
      return this.chanmeta.get(chanid).get(key);
    else
      return '';
  },

  setChanMeta: function(chanid, key, value)
  {
    if (chanid && key)
    {
      if (!this.chanmeta.get(chanid)) this.chanmeta.set(chanid, $H());
      if (value)
        this.chanmeta.get(chanid).set(key,value);
      else
        this.chanmeta.set(chanid, $H(key));
    }
  },

  doSendMessage: function()
  {
    var w = this.el_words;
    var wval = w.value;

    // Append the string to the history.
    this.cmdhistory.push(wval);
    this.cmdhistoryid = this.cmdhistory.length;
    this.cmdhistoryissearching = false;

    // Send the string to the server.
    re = new RegExp("^(\/[a-zA-Z0-9]+)( (.*)|)");
    if (wval.match(re))
    {
      // A user command.
      cmd = wval.replace(re, '$1');
      param = wval.replace(re, '$3');
      this.sendRequest(cmd +' '+ param.substr(0, pfc_max_text_len + 2*this.clientid.length));
    }
    else
    {
      // A classic 'send' command.

      // Empty messages with only spaces.
      rx = new RegExp('^[ ]*$','g');
      wval = wval.replace(rx,'');
        
      // Truncate the text length.
      wval = wval.substr(0,pfc_max_text_len);

      // Colorize the text with current_text_color.
      if (this.current_text_color != '' && wval.length != '')
        wval = '[color=#' + this.current_text_color + '] ' + wval + ' [/color]';

      this.sendRequest('/send '+ wval);
    }
    w.value = '';
    return false;
  },

  /**
   * Try to complete a nickname like on IRC when pressing the TAB key.
   * Nicks with spaces may not work under certain circumstances.
   * Replacing spaces with alternate spaces (e.g., &nbsp;) helps.
   * Gecko browsers convert the &nbsp; to regular spaces, so no help for these browsers.
   * Note: IRC does not allow nicks with spaces, so it's much easier for those clients. :)
   * @author Gerard Pinzone
   */
  completeNick: function()
  {
    var w = this.el_words;
    var selStart = w.value.length; // Default for browsers that don't support selection/caret position commands.
    var selEnd = selStart;
    
    // Get selection/caret position.
    if (w.setSelectionRange) 
    {
      // We don't rely on the stored values for browsers that support
      // the selectionStart and selectionEnd commands.
      selStart = w.selectionStart;
      selEnd   = w.selectionEnd;
    }
    else if (w.createTextRange && document.selection)
    {
      // We must rely on the stored values for IE browsers.
      selStart = (w.selStart != null) ? w.selStart : w.value.length;
      selEnd   = (w.selEnd != null) ? w.selEnd : w.value.length;
    }
    
    var begin          = w.value.lastIndexOf(' ', selStart - 1) + 1;
    var end            = (w.value.indexOf(' ', selStart) >= 0) ? w.value.indexOf(' ', selStart) : w.value.length;
    var nick_src       = w.value.substring(begin, end);
    var non_nick_begin = w.value.substring(0, begin);
    var non_nick_end   = w.value.substring(end, w.value.length);

    if (nick_src != '')
    {
      var tabid = this.gui.getTabId();
      var n_list = this.getChanMeta(tabid, 'users')['nick'];
      var nick_match = false;
      for (var i = 0; i < n_list.length; i++)
      {
        var nick_tmp = n_list[i];
        // replace spaces in nicks with &nbsp;
        nick_tmp = nick_tmp.replace(/ /g, '\240');
        if (nick_tmp.indexOf(nick_src) == 0)
        {
          if (! nick_match)
          {
            nick_match = true;
            nick_replace = nick_tmp;
          }
          else
          {
            // more than one possibility for completion
            var nick_len = Math.min(nick_tmp.length, nick_replace.length);
            // only keep characters that are common to all matches
            var j = 0;
            for (j = 0; j < nick_len; j++)
              if (nick_tmp.charAt(j) != nick_replace.charAt(j))
                break;

            nick_replace = nick_replace.substr(0, j);
          }
        }
      }
      if (nick_match)
      {
        w.value = non_nick_begin + nick_replace + non_nick_end;
        w.selStart = w.selEnd = non_nick_begin.length + nick_replace.length;
        
        // Move cursor to end of completed nick.
        if (w.setSelectionRange)
          w.setSelectionRange(w.selEnd, w.selEnd); // Gecko
        else
          this.setSelection(w);  // IE
      }
    }
  },
  
  /**
   * Cycle to older entry in history
   */
  historyUp: function()
  {
    // Write the previous command in the history.
    if (this.cmdhistory.length > 0)
    {
      var w = this.el_words;
      if (this.cmdhistoryissearching == false && w.value != "")
        this.cmdhistory.push(w.value);
      this.cmdhistoryissearching = true;
      this.cmdhistoryid = this.cmdhistoryid - 1;
      if (this.cmdhistoryid < 0)
        this.cmdhistoryid = 0; // stop at oldest entry
      w.value = this.cmdhistory[this.cmdhistoryid];
    }
  },

  /**
   * Cycle to newer entry in history
   */
  historyDown: function()
  {
    // Write the next command in the history.
    if (this.cmdhistory.length > 0)
    {
      var w = this.el_words;
      if (this.cmdhistoryissearching == false && w.value != "")
        this.cmdhistory.push(w.value);
      this.cmdhistoryissearching = true;
      this.cmdhistoryid = this.cmdhistoryid + 1;
      if (this.cmdhistoryid >= this.cmdhistory.length)
      {
        this.cmdhistoryid = this.cmdhistory.length; // stop at newest entry + 1
        w.value = ""; // blank input box
      }
      else
        w.value = this.cmdhistory[this.cmdhistoryid];
    }
  },

  /**
   * Handle the pressed keys.
   * see also callbackWords_OnKeydown
   */
  callbackWords_OnKeypress: function(evt)
  {
    // All browsers except for IE should use "evt.which."
    var code = (evt.which) ? evt.which : evt.keyCode;
    if (code == Event.KEY_RETURN) /* ENTER key */
    {
      return this.doSendMessage();
    }
    else
    {
      // Allow other key defaults.
      return true;
    }
  },
  
  /**
   * Handle the pressed keys.
   * see also callbackWords_OnKeypress
   * WARNING: Suppressing defaults on the keydown event 
   *          may prevent keypress and/or keyup events
   *          from firing.
   */
  callbackWords_OnKeydown: function(evt)
  {
    if (!this.isconnected) return false;
    this.clearError(Array(this.el_words));
    var code = (evt.which) ? evt.which : evt.keyCode
    if (code == 38 && (is_gecko || is_ie || is_opera || is_webkit)) // up arrow key
    {
      /* TODO: Fix up arrow issue in Opera - may be a bug in Opera. See TAB handler comments below. */
      /* Konqueror cannot use this feature due to keycode conflicts. */
      
      // Write the previous command in the history.
      this.historyUp();

      if (evt.returnValue) // IE
        evt.returnValue = false;
      if (evt.preventDefault) // DOM
        evt.preventDefault();
      return false; // should work in all browsers
    }
    else if (code == 40 && (is_gecko || is_ie || is_opera || is_webkit)) // down arrow key
    {
      /* Konqueror cannot use this feature due to keycode conflicts. */
      
      // Write the previous command in the history.
      this.historyDown();

      if (evt.returnValue) // IE
        evt.returnValue = false;
      if (evt.preventDefault) // DOM
        evt.preventDefault();
      return false; // should work in all browsers
    }
    else if (code == 9) /* TAB key */
    {
      // Do nickname completion like on IRC / Unix command line.
      this.completeNick();
      
      if (is_opera)
      {
        // Fixes Opera's loss of focus after TAB key is pressed.
        // This is most likely due to a bug in Opera
        // that executes the default key operation BEFORE the
        // keydown and keypress event handler.
        // This is probably the reason for the "up arrow" issue above.
        //window.setTimeout(function(){evt.target.focus();}, 0);
        evt.target.onblur = function() { this.focus(); this.onblur = null; };
      }
      
      if (evt.returnValue) // IE
        evt.returnValue = false;
      if (evt.preventDefault) // DOM
        evt.preventDefault();
      return false; // Should work in all browsers.
    }
    else
    {
      // Allow other key defaults.
      return true;
    }
  },
  callbackWords_OnKeyup: function(evt)
  {
    // Needed for IE since the text box loses selection/caret position on blur
    this.storeSelectionPos(this.el_words);
  },
  callbackWords_OnMouseup: function(evt)
  {
    // Needed for IE since the text box loses selection/caret position on blur
    this.storeSelectionPos(this.el_words);
  },
  callbackWords_OnFocus: function(evt)
  {
    //    if (this.el_handle && this.el_handle.value == '' && !this.minmax_status)
    //      this.el_handle.focus();
    
    // Needed for IE since the text box loses selection/caret position on blur
    this.setSelection(this.el_words);
  },
  callback_OnUnload: function(evt)
  {
    /* don't disconnect users when they reload the window
     * this event doesn't only occurs when the page is closed but also when the page is reloaded */
    if (pfc_quit_on_closedwindow)
    {
      if (!this.isconnected) return false;
      this.sendRequest('/quit');
    }
  },


  /**
   * hide error area and stop blinking fields
   */
  clearError: function(ids)
  { 
    this.el_errors.style.display = 'none';
    for (var i=0; i<ids.length; i++)
      this.blink(ids[i].id, 'stop');
  },

  /**
   * show error area and assign to it an error message and start the blinking of given fields
   */
  setError: function(str, ids)
  {
    this.el_errors.innerHTML = str;
    this.el_errors.style.display = 'block';
    for (var i=0; i<ids.length; i++)
      this.blink(ids[i].id, 'start');
  },

  /**
   * blink routines used by Error functions
   */
  blink: function(id, action)
  {
    clearTimeout(this.blinktimeout[id]);
    if ($(id) == null) return;
    if (action == 'start')
    {
      this.blinktmp[id] = $(id).style.backgroundColor;
      clearTimeout(this.blinktimeout[id]);
      this.blinktimeout[id] = setTimeout('pfc.blink(\'' + id + '\',\'loop\')', 500);
    }
    if (action == 'stop')
    {
      $(id).style.backgroundColor = this.blinktmp[id];
    }
    if (action == 'loop')
    {
      if (this.blinkloop[id] == 1)
      {
        $(id).style.backgroundColor = '#FFDFC0';
        this.blinkloop[id] = 2;
      }
      else
      {
        $(id).style.backgroundColor = '#FFFFFF';
        this.blinkloop[id] = 1;
      }
      this.blinktimeout[id] = setTimeout('pfc.blink(\'' + id + '\',\'loop\')', 500);
    }
  },

  displayMsg: function( cmd, msg )
  {
    this.setError(msg, Array());

    // @todo find a better crossbrowser way to display messages
/*
    // get the current selected tab container
    var tabid     = this.gui.getTabId();
    var container = this.gui.getChatContentFromTabId(tabid);

    // to fix IE6 display bug
    // http://sourceforge.net/tracker/index.php?func=detail&aid=1545403&group_id=158880&atid=809601
    div = document.createElement('div');
    // div.style.padding = "2px 5px 2px 5px"; // this will clear the screen in IE6
    div.innerHTML = '<div class="pfc_info pfc_info_'+cmd+'" style="margin:5px">'+msg+'</div>';

    // finaly append this to the message list
    container.appendChild(div); 
    this.gui.scrollDown(tabid, div);
*/
  },

  handleComingRequest: function( cmds )
  {
    var msg_html = $H();
    var max_msgid = $H();
    
    //alert(cmds.inspect());

    for(var mid = 0; mid < cmds.length ; mid++)
    {
      var id          = cmds[mid][0];
      var date        = cmds[mid][1];
      var time        = cmds[mid][2];
      var sender      = cmds[mid][3];
      var recipientid = cmds[mid][4];
      var cmd         = cmds[mid][5];
      var param       = cmds[mid][6];
      var fromtoday   = cmds[mid][7];
      var oldmsg      = cmds[mid][8];
      
      // format and post message
      var line = '';
      line += '<div id="pfc_msg_'+recipientid+'_'+id+'" class="pfc_cmd_'+ cmd +' pfc_message';
      line  += (id % 2 == 0) ? ' pfc_evenmsg' : ' pfc_oddmsg';
      if (oldmsg == 1) line += ' pfc_oldmsg';
      line += '">';
      line += '<span class="pfc_date';
      if (fromtoday == 1) line += ' pfc_invisible';
      line += '">'+ date +'</span> ';
      line += '<span class="pfc_heure">'+ time +'</span> ';
      if (cmd == 'send')
      {
        line += ' <span class="pfc_nick">';
        line += '&#x2039;';
        line += '<span ';
        line += 'onclick="pfc.insert_text(\'' + sender.escapeHTML().replace("'", '\\\'') + ', \',\'\',false)" ';
        line += 'class="pfc_nickmarker pfc_nick_'+ _to_utf8(sender).md5() +'">';
        line += sender.escapeHTML();
        line += '</span>';
        line += '&#x203A;';
        line += '</span> ';
      }
      if (cmd == 'notice' || cmd == 'me')
        line += '<span class="pfc_words">* '+ this.parseMessage(param) +'</span> ';
      else
        line += '<span class="pfc_words">'+ this.parseMessage(param) +'</span> ';
      line += '</div>';

      if (oldmsg == 0)
        if (cmd == 'send' || cmd == 'me')
        {
          // notify the hidden tab a message has been received
          // don't notify anything if this is old messages
          var tabid = recipientid;
          if (this.gui.getTabId() != tabid)
            this.gui.notifyTab(tabid);
          // notify the window (change the title)
          if (!this.detectactivity.isActive() && pfc_notify_window)
            this.gui.notifyWindow();
        }
        
      if (msg_html.get(recipientid) == null)
        msg_html.set(recipientid, line);
      else
        msg_html.set(recipientid, msg_html.get(recipientid) + line);
      
      // remember the max message id in order to clean old lines
      if (!max_msgid.get(recipientid)) max_msgid.set(recipientid, 0);
      if (max_msgid.get(recipientid) < id) max_msgid.set(recipientid, id);
    }

    // loop on all recipients and post messages
    var keys = msg_html.keys();
    for( var i=0; i<keys.length; i++)
    {
      var recipientid  = keys[i];
      var tabid        = recipientid;
      // create the tab if it doesn't exists yet
      var recipientdiv = this.gui.getChatContentFromTabId(tabid);
      
      // create a dummy div to avoid konqueror bug when setting nickmarkers
      var m = document.createElement('div');  // do not setup a inline element (ex: span) because the element height will be wrong on FF2 -> scrollDown(..) will be broken
      m.innerHTML = msg_html.get(recipientid);
      this.colorizeNicks(m);
      this.refresh_clock(m);
      // finaly append this to the message list
      recipientdiv.appendChild(m);
      this.gui.scrollDown(tabid, m);

      // delete the old messages from the client (save some memory)
      var limit_msgid = max_msgid.get(recipientid) - pfc_max_displayed_lines;
      var elt = $('pfc_msg_'+recipientid+'_'+limit_msgid);
      while (elt)
      {
        // delete this element to save browser memory
        if(elt.parentNode)
          elt.parentNode.removeChild(elt);
        else if(elt.parentElement)  // older IE browsers (<6.0) may not support parentNode
          elt.parentElement.removeChild(elt);
        else  // if all else fails
          elt.innerHTML = '';
        limit_msgid--;
        elt = $('pfc_msg_'+recipientid+'_'+limit_msgid);
      }
    }
    
  },

  calcDelay: function()
  {
    var lastact = new Date().getTime() - this.last_activity_time;
    var dlist = this.refresh_delay_steps.slice();
    var delay = dlist.shift();
    if (this.refresh_delay > delay) delay = this.refresh_delay;
    var limit;
    while (typeof (limit = dlist.shift()) != "undefined")
    {
      var d = dlist.shift();
      if (d < delay) continue;
      if (lastact > limit) delay = d;
    }
    return delay;
  },
  
  /**
   * Call the ajax request function
   * Will query the server
   */
  sendRequest: function(cmd, recipientid)
  {
    // do not send another ajax requests if the last one is not yet finished 
    if (cmd == '/update' && this.pfc_ajax_connected) return;

    var delay = this.calcDelay();

    if (cmd != "/update")
    {
      // setup a new timeout to update the chat in 5 seconds (in refresh_delay more exactly)
      clearTimeout(this.timeout);
      this.timeout = setTimeout('pfc.updateChat(true)', delay);
      this.timeout_time = new Date().getTime() + delay;

      if (pfc_debug)
        trace('sendRequest: '+cmd);
    }
  
    // prepare the command string
    var rx = new RegExp('(^\/[^ ]+) *(.*)','ig');
    if (!recipientid) recipientid = this.gui.getTabId();
    cmd = cmd.replace(rx, '$1 '+this.clientid+' '+(recipientid==''?'0':recipientid)+' $2');

    // send the real ajax request
    var url = pfc_server_script_url;
    new Ajax.Request(url, {
      method: 'post',
      parameters: {'pfc_ajax':1, 'f':'handleRequest', 'cmd': cmd },
      onCreate: function(transport) {
        this.pfc_ajax_connected = true; 
        // request time counter used by ping indicator
        this.last_request_time = new Date().getTime();
      }.bind(this),
      onSuccess: function(transport) {
        if (!transport.status) return; // fix strange behavior on KHTML

        // request time counter used by ping indicator
        this.last_response_time = new Date().getTime();
        // evaluate the javascript response
        eval( transport.responseText );
      }.bind(this),
      onComplete: function(transport) {
        this.pfc_ajax_connected = false;

        // calculate the ping and display it
        this.ping = Math.abs(this.last_response_time - this.last_request_time);
        if ($('pfc_ping')) $('pfc_ping').innerHTML = this.ping+'ms'+' ['+parseInt(this.calcDelay() / 1000)+'s]';
      }.bind(this)
    });
  },

  /**
   * update function to poll the server each 'refresh_delay' time
   */
  updateChat: function(start)
  {
    clearTimeout(this.timeout);
    if (start)
    {
      this.sendRequest('/update');
      
      // setup the next update
      var delay = this.calcDelay();
      this.timeout = setTimeout('pfc.updateChat(true)', delay);
      this.timeout_time = new Date().getTime() + delay;
    }
  },

  /**
   * Stores the caret/selection position for IE 6.x and 7.x
   * Returns true if text range start and end values were updated.
   * Code based on: http://www.bazon.net/mishoo/articles.epl?art_id=1292
   */
  storeSelectionPos: function(obj)
  {
    // We don't need to store the start and end positions if the browser
    // supports the Gecko selection model. However, these values may be
    // useful for debugging. Also, Opera recognizes Gecko and IE range
    // commands, so we need to ensure Opera only uses the Gecko model.
    /* WARNING: Do not use this for textareas. They require a more
                complex algorithm. */
    if (obj.setSelectionRange) 
    {
      obj.selStart = obj.selectionStart;
      obj.selEnd   = obj.selectionEnd;
      
      return true;
    }
    
    // IE
    else if (obj.createTextRange && document.selection)
    {
      // Determine current selection start position.
      var range = document.selection.createRange();
      var isCollapsed = range.compareEndPoints("StartToEnd", range) == 0;
      if (!isCollapsed)
        range.collapse(true);
      var b = range.getBookmark();
      obj.selStart = b.charCodeAt(2) - b.charCodeAt(0) - 1;
  
      // Determine current selection end position.
      range = document.selection.createRange();
      isCollapsed = range.compareEndPoints("StartToEnd", range) == 0;
      if (!isCollapsed)
        range.collapse(false);
      b = range.getBookmark();
      obj.selEnd = b.charCodeAt(2) - b.charCodeAt(0) - 1;
      
      return true;
    }
    
    // Browser does not support selection range processing.
    else
      return false;
  },

  /**
   * Sets the selection/caret in the object based on the
   * object's selStart and selEnd parameters.
   * This should only be needed for IE only.
   */
  setSelection: function(obj)
  {
    // This part of the function is included to prevent
    // Opera from executing the IE portion.
    /* WARNING: Do not attempt to use this function as
       a wrapper for the Gekco based setSelectionRange.
       It causes problems in Opera when executed from
       the event trigger onFocus. */
    if (obj.setSelectionRange)
    {
      return null;
    }  
    // IE
    else if (obj.createTextRange)
    {
      var range = obj.createTextRange();
      range.collapse(true);
      range.moveStart("character", obj.selStart);
      range.moveEnd("character", obj.selEnd - obj.selStart);
      range.select();
    
      return range;
    }
    // Browser does not support selection range processing.
    else
      return null;
  },
  
  /**
   * insert a smiley
   */
  insertSmiley: function(smiley)
  {
    var w = this.el_words;
    
    if (w.setSelectionRange)
    {
      // Gecko
      var s = w.selectionStart;
      var e = w.selectionEnd;
      w.value = w.value.substring(0, s) + smiley + w.value.substr(e);
      w.setSelectionRange(s + smiley.length, s + smiley.length);
      w.focus();
    }
    else if (w.createTextRange)
    {
      // IE
      w.focus();

      // Get range based on stored values.
      var range = this.setSelection(w);
      
      range.text = smiley;

      // Move caret position to end of smiley and collapse selection, if any.
      // Check if internally kept values for selection are initialized.
      w.selStart = (w.selStart) ? w.selStart + smiley.length : smiley.length;
      w.selEnd   = w.selStart;
    }
    else
    {
      // Unsupported browsers get smiley at end of string like old times.
      w.value += smiley;
      w.focus();
    }
  },

  updateNickBox: function(nickid)
  {
    // @todo optimize this function because it is called lot of times so it could cause CPU consuming on client side
    var chanids = this.chanmeta.keys();
    for(var i = 0; chanids.length > i; i++)
    {
      this.updateNickListBox(chanids[i]);
    }
  },

  /**
   * fill the nickname list with connected nicknames
   */
  updateNickListBox: function(chanid)
  {
    var className = (! is_ie) ? 'class' : 'className';

    var nickidlst = this.getChanMeta(chanid,'users').get('nickid');
    var nickdiv = this.gui.getOnlineContentFromTabId(chanid);
    var ul = document.createElement('ul');
    ul.setAttribute(className, 'pfc_nicklist');
    for (var i=0; i<nickidlst.length; i++)
    {
      var nickid = nickidlst[i];
      var li = this.buildNickItem(nickid);
      li.setAttribute(className, 'pfc_nickitem_'+nickid);
      ul.appendChild(li);
    }
    var fc = nickdiv.firstChild;
    if (fc)
      nickdiv.replaceChild(ul,fc);
    else
      nickdiv.appendChild(ul,fc);
    this.colorizeNicks(nickdiv);
  },

  getNickWhoisBox: function(nickid)
  {
    if (!this.nickwhoisbox.get(nickid))
      this.updateNickWhoisBox(nickid);
    return this.nickwhoisbox.get(nickid);
  },

  updateNickWhoisBox: function(nickid)
  {
    var className = (! is_ie) ? 'class' : 'className';

    var usermeta = this.getAllUserMeta(nickid);
    var div  = document.createElement('div');
    div.setAttribute(className, 'pfc_nickwhois');

    var p = document.createElement('p');
    p.setAttribute(className, 'pfc_nickwhois_header');
    div.appendChild(p);

    // add the close button
    var img = document.createElement('img');
    img.setAttribute(className, 'pfc_nickwhois_close');
    img.pfc_parent = div;
    img.onclick = function(evt){
      this.pfc_parent.style.display = 'none';
      return false;
    }
    img.setAttribute('src', this.res.getFileUrl('images/close-whoisbox.gif'));
    img.alt = this.res.getLabel('Close');
    p.appendChild(img);
    p.appendChild(document.createTextNode(usermeta.get('nick'))); // append the nickname text in the title

    // add the whois information table
    var table = document.createElement('table');
    var tbody = document.createElement('tbody');
    table.appendChild(tbody);
    var um_keys = usermeta.keys();
    var msg = '';
    for (var i=0; i<um_keys.length; i++)
    {
      var k = um_keys[i];
      var v = usermeta.get(k);
      if (v && k != 'nickid'
            && k != 'nick' // useless because it is displayed in the box title
            && k != 'isadmin' // useless because of the gold shield icon
            && k != 'floodtime'
            && k != 'flood_nbmsg'
            && k != 'flood_nbchar'
         )
      {
        var tr = document.createElement('tr');
        if (pfc_nickmeta_key_to_hide.indexOf(k) != -1)
        {
          var td2 = document.createElement('td');
          td2.setAttribute(className, 'pfc_nickwhois_c2');
          td2.setAttribute('colspan', 2);
          td2.innerHTML = v;
          tr.appendChild(td2);
        }
        else
        {
          var td1 = document.createElement('td');
          td1.setAttribute(className, 'pfc_nickwhois_c1');
          var td2 = document.createElement('td');
          td2.setAttribute(className, 'pfc_nickwhois_c2');
          td1.innerHTML = k;
          td2.innerHTML = v;
          tr.appendChild(td1);
          tr.appendChild(td2);
        }
        tbody.appendChild(tr);
      }
    }
    div.appendChild(table);

    // add the privmsg link (do not add it if the nick is yours)
    if (pfc.getUserMeta(nickid,'nick') != this.nickname)
    {
      var p = document.createElement('p');
      p.setAttribute(className, 'pfc_nickwhois_pv');
      var a = document.createElement('a');
      a.setAttribute('href', '');
      a.pfc_nickid = nickid;
      a.pfc_parent = div;
      a.onclick = function(evt){
        var nick = pfc.getUserMeta(this.pfc_nickid,'nick');
        pfc.sendRequest('/privmsg "'+nick+'"');
        this.pfc_parent.style.display = 'none';
        return false;
      }
      var img = document.createElement('img');
      img.setAttribute('src', this.res.getFileUrl('images/openpv.gif'));
      img.alt = document.createTextNode(this.res.getLabel('Private message'));
      a.appendChild(img);
      a.appendChild(document.createTextNode(this.res.getLabel('Private message')));
      p.appendChild(a);
      div.appendChild(p);
    }

    this.nickwhoisbox.set(nickid, div);
  },

  buildNickItem: function(nickid)
  {
    var className = (! is_ie) ? 'class' : 'className';

    var nick = this.getUserMeta(nickid, 'nick');
    var isadmin = this.getUserMeta(nickid, 'isadmin');
    if (isadmin == '') isadmin = false;

    var li = document.createElement('li');

    var a = document.createElement('a');
    a.setAttribute('href','#');
    a.pfc_nick   = nick;
    a.pfc_nickid = nickid;
    a.onclick = function(evt){
      var d = pfc.getNickWhoisBox(this.pfc_nickid);
      document.body.appendChild(d);
      d.style.display = 'block';
      d.style.zIndex = '400';
      d.style.position = 'absolute';
      d.style.left = (mousePosX(evt)-7)+'px';
      d.style.top  = (mousePosY(evt)-7)+'px';
      return false;
    }
    li.appendChild(a);

    var img = document.createElement('img');
    if (isadmin)
      img.setAttribute('src', this.res.getFileUrl('images/user-admin.gif'));
    else
      img.setAttribute('src', this.res.getFileUrl('images/user.gif'));
    img.style.marginRight = '5px';
    img.setAttribute(className, 'pfc_nickbutton');
    a.appendChild(img);

    // nobr is not xhtml valid but it's a workeround 
    // for IE which doesn't support 'white-space: pre' css rule
    var nobr = document.createElement('nobr');
    var span = document.createElement('span');
    span.setAttribute(className, 'pfc_nickmarker pfc_nick_'+nickid);
    span.innerHTML = nick.escapeHTML();
    nobr.appendChild(span);
    a.appendChild(nobr);

    return li;
  },
  
  /**
   * clear the nickname list
   */
  clearNickList: function()
  {
    /*
    var nickdiv = this.el_online;
    var fc = nickdiv.firstChild;
    if (fc) nickdiv.removeChild(fc);
    */
  },


  /**
   * clear the message list history
   */
  clearMessages: function()
  {
    //var msgdiv = $('pfc_chat');
    //msgdiv.innerHTML = '';
  },

  /**
   * parse the message
   */
  parseMessage: function(msg)
  {
    var rx = null;
/*
    // parse urls
    var rx_url = new RegExp('(^|[^\\"])([a-z]+\:\/\/[a-z0-9.\\~\\/\\?\\=\\&\\-\\_\\#:;%,@]*[a-z0-9\\/\\?\\=\\&\\-\\_\\#])([^\\"]|$)','ig');
    var ttt = msg.split(rx_url);
    if (ttt.length > 1 &&
        !navigator.appName.match("Explorer|Konqueror") &&
        !navigator.appVersion.match("KHTML"))
    {
      msg = '';
      for( var i = 0; i<ttt.length; i++)
      {
        var offset = (ttt[i].length - 7) / 2;
        var delta = (ttt[i].length - 7 - 60);
        var range1 = 7+offset-delta;
        var range2 = 7+offset+delta;
        if (ttt[i].match(rx_url))
        {
          msg = msg + '<a href="' + ttt[i] + '"';
          if (pfc_openlinknewwindow)
            msg = msg + ' onclick="window.open(this.href,\'_blank\');return false;"';
          msg = msg + '>' + (delta>0 ? ttt[i].substring(7,range1)+ ' ... ' + ttt[i].substring(range2,ttt[i].length) :  ttt[i]) + '</a>';
        }
        else
        {
          msg = msg + ttt[i];
        }
      }
    }
    else
    {
      // fallback for IE6/Konqueror which do not support split with regexp
      replace = '$1<a href="$2"';
      if (pfc_openlinknewwindow)
        replace = replace + ' onclick="window.open(this.href,\'_blank\');return false;"';
      replace = replace + '>$2</a>$3';
      msg = msg.replace(rx_url, replace);
    }
*/    

    // Remove auto-linked entries.
    if ( false )
    {
      rx = new RegExp('<a href="mailto:(.*?)".*?>.*?<\/a>','ig');
      msg = msg.replace(rx, '$1');
      rx = new RegExp('<a href="(.*?)".*?>.*?<\/a>','ig');
      msg = msg.replace(rx, '$1');
    }

    // Replace double spaces outside of tags by "&nbsp; " entity.
    rx = new RegExp(' (?= )(?![^<]*>)','g');
    msg = msg.replace(rx, '&nbsp;');
    
    // try to parse bbcode
    rx = new RegExp('\\[b\\](.+?)\\[\/b\\]','ig');
    msg = msg.replace(rx, '<span style="font-weight: bold">$1</span>');
    rx = new RegExp('\\[i\\](.+?)\\[\/i\\]','ig');
    msg = msg.replace(rx, '<span style="font-style: italic">$1</span>');
    rx = new RegExp('\\[u\\](.+?)\\[\/u\\]','ig');
    msg = msg.replace(rx, '<span style="text-decoration: underline">$1</span>');
    rx = new RegExp('\\[s\\](.+?)\\[\/s\\]','ig');
    msg = msg.replace(rx, '<span style="text-decoration: line-through">$1</span>');
    //    rx = new RegExp('\\[pre\\](.+?)\\[\/pre\\]','ig');
    // msg = msg.replace(rx, '<pre>$1</pre>'); 
/*
    rx = new RegExp('\\[email\\]([A-z0-9][\\w.-]*@[A-z0-9][\\w\\-\\.]+\\.[A-z0-9]{2,6})\\[\/email\\]','ig');
    msg = msg.replace(rx, '<a href="mailto: $1">$1</a>'); 
    rx = new RegExp('\\[email=([A-z0-9][\\w.-]*@[A-z0-9][\\w\\-\\.]+\\.[A-z0-9]{2,6})\\](.+?)\\[\/email\\]','ig');
    msg = msg.replace(rx, '<a href="mailto: $1">$2</a>');
*/
    rx = new RegExp('\\[color=([a-zA-Z]+|\\#?[0-9a-fA-F]{6}|\\#?[0-9a-fA-F]{3})](.+?)\\[\/color\\]','ig');
    msg = msg.replace(rx, '<span style="color: $1">$2</span>');
    // parse bbcode colors twice because the current_text_color is a bbcolor
    // so it's possible to have a bbcode color imbrication
    rx = new RegExp('\\[color=([a-zA-Z]+|\\#?[0-9a-fA-F]{6}|\\#?[0-9a-fA-F]{3})](.+?)\\[\/color\\]','ig');
    msg = msg.replace(rx, '<span style="color: $1">$2</span>');   

    // try to parse smileys
    var smileys = this.res.getSmileyHash();
    var sl = this.res.getSmileyKeys(); // Keys should be sorted by length from pfc.gui.loadSmileyBox()
    for(var i = 0; i < sl.length; i++)
    {
      // We don't want to replace smiley strings inside of tags.
      // Use negative lookahead to search for end of tag.
      rx = new RegExp(RegExp.escape(sl[i]) + '(?![^<]*>)','g');
      msg = msg.replace(rx, '<img src="'+ smileys.get(sl[i]) +'" alt="' + sl[i] + '" title="' + sl[i] + '" />');
    }
    
    // try to parse nickname for highlighting 
    rx = new RegExp('(^|[ :,;])'+RegExp.escape(this.nickname)+'([ :,;]|$)','gi');
    msg = msg.replace(rx, '$1<strong>'+ this.nickname +'</strong>$2');
    
    // this piece of code is replaced by the word-wrap CSS3 rule.
    /*
    // don't allow to post words bigger than 65 caracteres
    // doesn't work with crappy IE and Konqueror !
    rx = new RegExp('([^ \\:\\<\\>\\/\\&\\;]{60})','ig');
    var ttt = msg.split(rx);
    if (ttt.length > 1 &&
        !navigator.appName.match("Explorer|Konqueror") &&
        !navigator.appVersion.match("KHTML"))
    {
      msg = '';
      for( var i = 0; i<ttt.length; i++)
      {
        msg = msg + ttt[i] + ' ';
      }
    }
    */
    return msg;
  },

  /**
   * apply nicknames color to the root childs
   */
  colorizeNicks: function(root)
  {
    if (this.nickmarker)
    {
      var nicklist = this.getElementsByClassName(root, 'pfc_nickmarker', '');
      for(var i = 0; i < nicklist.length; i++)
      {
        var cur_nick = nicklist[i].innerHTML;
        var cur_color = this.getAndAssignNickColor(cur_nick);
        nicklist[i].style.color = cur_color;
      }
    }
  },
  
  /**
   * Initialize the color array used to colirize the nicknames
   */
  reloadColorList: function()
  {
    this.colorlist = $A(pfc_nickname_color_list);
  },
  

  /**
   * get the corresponding nickname color
   */
  getAndAssignNickColor: function(nick)
  {
    /* check the nickname is colorized or not */
    var already_colorized = false;
    var nc = '';
    for(var j = 0; j < this.nickcolor.length && !already_colorized; j++)
    {
      if (this.nickcolor[j][0] == nick)
      {
        already_colorized = true;
        nc = this.nickcolor[j][1];
      }
    }
    if (!already_colorized)
    {
      /* reload the color stack if it's empty */
      if (this.colorlist.length == 0) this.reloadColorList();
      /* take the next color from the list and colorize this nickname */
      var cid = Math.round(Math.random()*(this.colorlist.length-1));
      nc = this.colorlist[cid];
      this.colorlist.splice(cid,1);
      this.nickcolor.push(new Array(nick, nc));
    }

    return nc;
  },
  

  /**
   * Colorize with 'color' all the nicknames found as a 'root' child
   */
  applyNickColor: function(root, nick, color)
  {
    
    var nicktochange = this.getElementsByClassName(root, 'pfc_nick_'+ _to_utf8(nick).md5(), '');
    for(var i = 0; nicktochange.length > i; i++) 
      nicktochange[i].style.color = color;
    
  },

  /**
   * Returns a list of elements which have a clsName class
   */
  getElementsByClassName: function( root, clsName, clsIgnore )
  {
    var i, matches = new Array();
    var els = root.getElementsByTagName('*');
    var rx1 = new RegExp('.*'+clsName+'.*');
    var rx2 = new RegExp('.*'+clsIgnore+'.*');
    for(i=0; i<els.length; i++) {
      if(els.item(i).className.match(rx1) &&
         (clsIgnore == '' || !els.item(i).className.match(rx2)) )
      {
        matches.push(els.item(i));
      }
    }
    return matches;
  },

  showClass: function(root, clsName, clsIgnore, show)
  {
    var elts = this.getElementsByClassName(root, clsName, clsIgnore);
    for(var i = 0; elts.length > i; i++)
    if (show)
      elts[i].style.display = 'inline';
    else
      elts[i].style.display = 'none';
  },


  /**
   * Nickname marker show/hide
   */
  nickmarker_swap: function()
  {
    if (this.nickmarker) {
      this.nickmarker = false;
    } else {
      this.nickmarker = true;
    }
    this.refresh_nickmarker()
    setCookie('pfc_nickmarker', this.nickmarker);
  },
  refresh_nickmarker: function(root)
  {
    var nickmarker_icon = $('pfc_nickmarker');
    if (!root) root = $('pfc_channels_content');
    if (this.nickmarker)
    {
      nickmarker_icon.src   = this.res.getFileUrl('images/color-on.gif');
      nickmarker_icon.alt   = this.res.getLabel("Hide nickname marker");
      nickmarker_icon.title = nickmarker_icon.alt;
      this.colorizeNicks(root);
    }
    else
    {
      nickmarker_icon.src   = this.res.getFileUrl('images/color-off.gif');
      nickmarker_icon.alt   = this.res.getLabel("Show nickname marker");
      nickmarker_icon.title = nickmarker_icon.alt;
      var elts = this.getElementsByClassName(root, 'pfc_nickmarker', '');
      for(var i = 0; elts.length > i; i++)
      {
        // this is not supported in konqueror =>>>  elts[i].removeAttribute('style');
        elts[i].style.color = '';
      }
    }
  },


  /**
   * Date/Hour show/hide
   */
  clock_swap: function()
  {
    if (this.clock) {
      this.clock = false;
    } else {
      this.clock = true;
    }
    this.refresh_clock();
    setCookie('pfc_clock', this.clock);
  },
  refresh_clock: function( root )
  {
    var clock_icon = $('pfc_clock');
    if (!root) root = $('pfc_channels_content');
    if (this.clock)
    {
      clock_icon.src   = this.res.getFileUrl('images/clock-on.gif');
      clock_icon.alt   = this.res.getLabel('Hide dates and hours');
      clock_icon.title = clock_icon.alt;
      this.showClass(root, 'pfc_date', 'pfc_invisible', true);
      this.showClass(root, 'pfc_heure', 'pfc_invisible', true);
    }
    else
    {
      clock_icon.src   = this.res.getFileUrl('images/clock-off.gif');
      clock_icon.alt   = this.res.getLabel('Show dates and hours');
      clock_icon.title = clock_icon.alt;
      this.showClass(root, 'pfc_date', 'pfc_invisible', false);
      this.showClass(root, 'pfc_heure', 'pfc_invisible', false);
    }
    // browser automaticaly scroll up misteriously when showing the dates
    //    $('pfc_chat').scrollTop += 30;
  },
  
  /**
   * Sound button
   */
  sound_swap: function()
  {
    if (this.issoundenable) {
      this.issoundenable = false;
    } else {
      this.issoundenable = true;
    }
    this.refresh_sound();
    setCookie('pfc_issoundenable', this.issoundenable);
  },
  refresh_sound: function( root )
  {
    var snd_icon = $('pfc_sound');
    if (this.issoundenable)
    {
      snd_icon.src   = this.res.getFileUrl('images/sound-on.gif');
      snd_icon.alt   = this.res.getLabel('Disable sound notifications');
      snd_icon.title = snd_icon.alt;
    }
    else
    {
      snd_icon.src   = this.res.getFileUrl('images/sound-off.gif');
      snd_icon.alt   = this.res.getLabel('Enable sound notifications');
      snd_icon.title = snd_icon.alt;
    }
  },
  
  /**
   * Connect/disconnect button
   */
  connect_disconnect: function()
  {
    if (this.isconnected)
      this.sendRequest('/quit');
    else
    {
      if (this.nickname == '')
        this.askNick();
      else
        this.sendRequest('/connect "'+this.nickname+'"');
    }
  },
  refresh_loginlogout: function()
  {
    var loginlogout_icon = $('pfc_loginlogout');
    if (this.isconnected)
    {
      loginlogout_icon.src   = this.res.getFileUrl('images/logout.gif');
      loginlogout_icon.alt   = this.res.getLabel('Disconnect');
      loginlogout_icon.title = loginlogout_icon.alt;
    }
    else
    {
      this.clearMessages();
      this.clearNickList();
      loginlogout_icon.src   = this.res.getFileUrl('images/login.gif');
      loginlogout_icon.alt   = this.res.getLabel('Connect');
      loginlogout_icon.title = loginlogout_icon.alt;
    }
  },


  /**
   * Minimize/Maximized the chat zone
   */
  swap_minimize_maximize: function()
  {
    if (this.minmax_status) {
      this.minmax_status = false;
    } else {
      this.minmax_status = true;
    }
    setCookie('pfc_minmax_status', this.minmax_status);
    this.refresh_minimize_maximize();
  },
  refresh_minimize_maximize: function()
  {
    var content = $('pfc_content_expandable');
    var btn     = $('pfc_minmax');
    if (this.minmax_status)
    {
      btn.src = this.res.getFileUrl('images/maximize.gif');
      btn.alt = this.res.getLabel('Magnify');
      btn.title = btn.alt;
      content.style.display = 'none';
    }
    else
    {
      btn.src = this.res.getFileUrl('images/minimize.gif');
      btn.alt = this.res.getLabel('Cut down');
      btn.title = btn.alt;
      content.style.display = 'block';
    }
  },

  
  /**
   * BBcode ToolBar
   */
  insert_text: function(open, close, promptifselempty) 
  {
    var msgfield = $('pfc_words');

    var pfcp = this.getPrompt();
    pfcp.msgfield = msgfield;
    pfcp.open     = open;
    pfcp.close    = close;
    pfcp.promptifselempty = promptifselempty;
    pfcp.callback = this.insert_text_callback;

    // Gecko
    /* Always check for Gecko selection processing commands
       first. This is needed for Opera. */
    if (msgfield.selectionStart || msgfield.selectionStart == '0')
    {
      var startPos = msgfield.selectionStart;
      var endPos   = msgfield.selectionEnd;
      
      var text = msgfield.value.substring(startPos, endPos);
      if (startPos == endPos && promptifselempty)
      {
        pfcp.prompt(this.res.getLabel('Enter the text to format'), '');
        pfcp.focus();
      }
      else
        this.insert_text_callback(text, pfcp);
    }

    // IE
    else if (document.selection && document.selection.createRange)
    {
      msgfield.focus();
      
      // Get selection range.
      pfcp.range = this.setSelection(msgfield);
      var text = pfcp.range.text;
      if (text == "" && promptifselempty)
      {
        pfcp.prompt(this.res.getLabel('Enter the text to format'), '');
        pfcp.focus();
      }
      else
        this.insert_text_callback(text, pfcp);
    }
    
    // Fallback support for other browsers
    else
    {
      pfcp.prompt(this.res.getLabel('Enter the text to format'), '');
      pfcp.focus();
    }
    return;
  },
  insert_text_callback: function(text, pfcp)
  {
    var open             = pfcp.open;
    var close            = pfcp.close;
    var promptifselempty = pfcp.promptifselempty;
    var msgfield         = pfcp.msgfield;
    var range            = pfcp.range;

    // Gecko
    /* Always check for Gecko selection processing commands
       first. This is needed for Opera. */
    if (msgfield.selectionStart || msgfield.selectionStart == '0')
    {
      var startPos = msgfield.selectionStart;
      var endPos   = msgfield.selectionEnd;
      
      var extralength = 0;
      if (startPos == endPos && promptifselempty)
      {
        if (text == null) text = "";
        extralength = text.length;
      }
      if (text.length > 0 || !promptifselempty)
      {
        msgfield.value = msgfield.value.substring(0, startPos) + open + text + close + msgfield.value.substring(endPos, msgfield.value.length);
        var caretPos = endPos + open.length + extralength + close.length;
        msgfield.setSelectionRange(caretPos, caretPos);
        msgfield.focus();
      }
    }
    // IE
    else if (document.selection && document.selection.createRange)
    {
      if (text == null) text = "";
      if (text.length > 0 || !promptifselempty)
      {
        msgfield.focus();

        range.text = open + text + close;

        // Increment caret position.
        // Check if internally kept values for selection are initialized.
        msgfield.selStart = (msgfield.selStart) ? msgfield.selStart + open.length + text.length + close.length : open.length + text.length + close.length;
        msgfield.selEnd   = msgfield.selStart;

        msgfield.focus();
      }
    }
    // Fallback support for other browsers
    else
    {
      if (text == null) text = "";
      if (text.length > 0 || !promptifselempty)
      {
        msgfield.value += open + text + close;
        msgfield.focus();
      }
    }
  },
  
  /**
   * Minimize/Maximize none/inline
   */
  minimize_maximize: function(idname, type)
  {
    var element = $(idname);
    if(element.style)
    {
      if(element.style.display == type )
      {
        element.style.display = 'none';
      }
      else
      {
        element.style.display = type;
      }
    }
  },
  
  switch_text_color: function(color)
  {
    /* clear any existing borders on the color buttons */
    var colorbtn = this.getElementsByClassName($('pfc_colorlist'), 'pfc_color', '');
    for(var i = 0; colorbtn.length > i; i++)
    {
      colorbtn[i].style.border = 'none';
      colorbtn[i].style.padding = '0';
    }

    /* assign the new border style to the selected button */
    this.current_text_color = color;
    setCookie('pfc_current_text_color', this.current_text_color);
    var idname = 'pfc_color_' + color;
    $(idname).style.border = '1px solid #555';
    $(idname).style.padding = '1px';

    // assigne the new color to the input text box
    this.el_words.style.color = '#'+color;
    this.el_words.focus();
  },
  
  /**
   * Smiley show/hide
   */
  showHideSmileys: function()
  {
    if (this.showsmileys)
    {
      this.showsmileys = false;
    }
    else
    {
      this.showsmileys = true;
    }
    setCookie('pfc_showsmileys', this.showsmileys);
    this.refresh_Smileys();
  },
  refresh_Smileys: function()
  {
    // first of all : show/hide the smiley box
    var content = $('pfc_smileys');
    if (this.showsmileys)
      content.style.display = 'block';
    else
      content.style.display = 'none';

    // then switch the button icon
    var btn = $('pfc_showHideSmileysbtn');
    if (this.showsmileys)
    {
      if (btn)
      {
        btn.src = this.res.getFileUrl('images/smiley-on.gif');
        btn.alt = this.res.getLabel('Hide smiley box');
        btn.title = btn.alt;
      }
    }
    else
    {
      if (btn)
      {
        btn.src = this.res.getFileUrl('images/smiley-off.gif');
        btn.alt = this.res.getLabel('Show smiley box');
        btn.title = btn.alt;
      }
    }
  },
  
  
  /**
   * Show Hide who's online
   */
  showHideWhosOnline: function()
  {
    if (this.showwhosonline)
    {
      this.showwhosonline = false;
    }
    else
    {
      this.showwhosonline = true;
    }
    setCookie('pfc_showwhosonline', this.showwhosonline);
    this.refresh_WhosOnline();
  },
  refresh_WhosOnline: function()
  {
    // first of all : show/hide the nickname list box
    var root = $('pfc_channels_content');
    var contentlist = this.getElementsByClassName(root, 'pfc_online', '');
    for(var i = 0; i < contentlist.length; i++)
    {
      var content = contentlist[i];
      if (this.showwhosonline)
        content.style.display = 'block';
      else
        content.style.display = 'none';
      content.style.zIndex = '100'; // for IE6, force the nickname list borders to be shown
    }

    // then refresh the button icon
    var btn = $('pfc_showHideWhosOnlineBtn');
    if (!btn) return;
    if (this.showwhosonline)
    {
      btn.src = this.res.getFileUrl('images/online-on.gif');
      btn.alt = this.res.getLabel('Hide online users box');
      btn.title = btn.alt;
    }
    else
    {
      btn.src = this.res.getFileUrl('images/online-off.gif');
      btn.alt = this.res.getLabel('Show online users box');
      btn.title = btn.alt;
    }
    this.refresh_Chat();
  },

  /**
   * Resize chat
   */
  refresh_Chat: function()
  {
    // resize all the tabs content
    var root = $('pfc_channels_content');
    var contentlist = this.getElementsByClassName(root, 'pfc_chat', '');
    for(var i = 0; i < contentlist.length; i++)
    {
      var chatdiv = contentlist[i];
      if (!this.showwhosonline)
      {
        chatdiv.style.width = '100%';
      }
      else
      {
        chatdiv.style.width = '';
      }
    }
  },

  getPrompt: function()
  {
    if (!this.pfc)
    this.pfc = new pfcPrompt($('pfc_container'));
    return this.pfc;
  }
};

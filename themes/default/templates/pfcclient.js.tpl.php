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
    /* user description */
    this.nickname      = '<?php echo $u->nick; ?>';

    
    this.channels      = Array();
    this.channelids    = Array();
    this.privmsgs      = Array();
    this.privmsgids    = Array();
    
    this.timeout       = null;
    this.refresh_delay = <?php echo $refresh_delay; ?>;
    /* unique client id for each windows used to identify a open window
     * this id is passed every time the JS communicate with server
     * (2 clients can use the same session: then only the nickname is shared) */
    this.clientid      = '<?php echo md5(uniqid(rand(), true)); ?>';

    this.el_words     = $('<?php echo $prefix; ?>words');
    this.el_handle    = $('<?php echo $prefix; ?>handle');
    this.el_container = $('<?php echo $prefix; ?>container');
    this.el_online    = $('<?php echo $prefix; ?>online');
    this.el_errors    = $('<?php echo $prefix; ?>errors');

    this.minmax_status = <?php echo $start_minimized ? "true" : "false"; ?>;
    var cookie = getCookie('<?php echo $prefix; ?>minmax_status');
    if (cookie != null)
      this.minmax_status = (cookie == 'true');

    cookie = getCookie('<?php echo $prefix; ?>nickmarker');
    this.nickmarker = (cookie == 'true');
    if (cookie == '' || cookie == null)
      this.nickmarker = <?php echo $nickmarker ? "true" : "false"; ?>;
    
    cookie = getCookie('<?php echo $prefix; ?>clock');
    this.clock = (cookie == 'true');
    if (cookie == '' || cookie == null)
      this.clock = <?php echo $clock ? "true" : "false"; ?>;

    cookie = getCookie('<?php echo $prefix; ?>showsmileys');
    this.showsmileys = (cookie == 'true');
    if (cookie == '' || cookie == null)
      this.showsmileys = <?php echo $showsmileys ? "true" : "false"; ?>;
    
    cookie = getCookie('<?php echo $prefix; ?>showwhosonline');
    this.showwhosonline = (cookie == 'true');
    if (cookie == '' || cookie == null)
      this.showwhosonline = <?php echo $showwhosonline ? "true" : "false"; ?>;

    /* '' means no forced color, let CSS choose the text color */
    this.current_text_color = '';
    cookie = getCookie('<?php echo $prefix; ?>current_text_color');
    if (cookie != null)
      this.switch_text_color(cookie);
             
    this.isconnected   = false;
    this.nicklist      = $H();
    this.nickcolor     = Array();
    this.colorlist     = Array();

    this.blinktmp     = Array();
    this.blinkloop    = Array();
    this.blinktimeout = Array();

    /* the events callbacks */
    this.el_words.onkeypress = this.callbackWords_OnKeypress.bindAsEventListener(this);
    this.el_words.onkeydown  = this.callbackWords_OnKeydown.bindAsEventListener(this);
    this.el_words.onfocus    = this.callbackWords_OnFocus.bindAsEventListener(this);
    this.el_handle.onkeydown = this.callbackHandle_OnKeydown.bindAsEventListener(this);
    this.el_handle.onchange  = this.callbackHandle_OnChange.bindAsEventListener(this);
    this.el_container.onmousemove = this.callbackContainer_OnMousemove.bindAsEventListener(this);
    this.el_container.onmousedown = this.callbackContainer_OnMousedown.bindAsEventListener(this);
    this.el_container.onmouseup   = this.callbackContainer_OnMouseup.bindAsEventListener(this);
    document.body.onunload = this.callback_OnUnload.bindAsEventListener(this);

    // the i18n translations
    this.i18n = new pfcI18N();
    this.i18n.setLabel('hide_nickname_color', '<?php echo _pfc("Hide nickname marker"); ?>');
    this.i18n.setLabel('show_nickname_color', '<?php echo _pfc("Show nickname marker"); ?>');
    this.i18n.setLabel('hide_clock',          '<?php echo _pfc("Hide dates and hours"); ?>');
    this.i18n.setLabel('show_clock',          '<?php echo _pfc("Show dates and hours"); ?>');
    this.i18n.setLabel('logout',              '<?php echo _pfc("Disconnect"); ?>');
    this.i18n.setLabel('login',               '<?php echo _pfc("Connect"); ?>');
    this.i18n.setLabel('maximize',            '<?php echo _pfc("Magnify"); ?>');
    this.i18n.setLabel('minimize',            '<?php echo _pfc("Cut down"); ?>');
    this.i18n.setLabel('hidesmiley',          '<?php echo _pfc("Hide smiley box"); ?>');
    this.i18n.setLabel('showsmiley',          '<?php echo _pfc("Show smiley box"); ?>');
    this.i18n.setLabel('hideonline',          '<?php echo _pfc("Hide online users box"); ?>');
    this.i18n.setLabel('showonline',          '<?php echo _pfc("Show online users box"); ?>');
    this.i18n.setLabel('enter_nickname',      '<?php echo _pfc("Please enter your nickname"); ?>');
    this.i18n.setLabel('Private message',     '<?php echo _pfc("Private message"); ?>');
    this.i18n.setLabel('Close this tab',      '<?php echo _pfc("Close this tab"); ?>');

    // the graphical user interface
    this.gui = new pfcGui(this.i18n);
 
    /* the smileys */
    var smileys = {
      <?php
      $output = "";
      foreach($smileys as $s_file => $s_str) { 
	for($j = 0; $j<count($s_str) ; $j++) {
	  $s = $s_str[$j];
	  $output .= "'".$s."': '".$s_file."',";
	}
      }
      $output = substr($output, 0, strlen($output)-1); // remove last ','
      echo $output;
      ?>
    }
    this.smileys = $H(smileys);
  },

  /**
   * Show a popup dialog to ask user to choose a nickname
   */
  askNick: function(nickname)
  {
    // ask to choose a nickname
    if (nickname == '') nickname = this.nickname;
    var newnick = prompt(this.i18n._('enter_nickname'), nickname);
    if (newnick)
      this.sendRequest('/nick', newnick);
  },
  
  /**
   * Reacte to the server response
   */
  handleResponse: function(cmd, resp, param)
  {
    if (cmd == "connect")
    {
      //alert(cmd + "-"+resp+"-"+param);
      if (resp == "ok")
      {                
        if (this.nickname == '')
          // ask to choose a nickname
          this.askNick(this.nickname);
        else
        {
          this.sendRequest('/nick', this.nickname);

          // now join channels comming from sessions
          // or the default one
          <?php
          if (count($u->channels) == 0)
            echo "this.sendRequest('/join', '".addslashes($c->channel)."');\n";
          foreach($u->channels as $ch)
            echo "this.sendRequest('/join', '".addslashes($ch["name"])."');\n";
          foreach($u->privmsg as $pv)
            echo "this.sendRequest('/privmsg', '".addslashes($pv["name"])."');\n";
          ?>
        }
        
        // give focus the the input text box if wanted
        <?php if($c->focus_on_connect) { ?>
        this.el_words.focus();
        <?php } ?>

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
    else if (cmd == "join")
    {
      if (resp =="ok")
      {
        // create the new channel
        var tabid = param[0];
        var name  = param[1];
        this.gui.createTab(name, tabid, "ch");
        this.gui.setTabById(tabid);
        /*
        this.channels.push(name);
        this.channelids.push(tabid);
        */
      }
      else
        alert(cmd + "-"+resp+"-"+param);
    }
    else if (cmd == "leave")
    {
      //alert(cmd + "-"+resp+"-"+param);
      if (resp =="ok")
      {
        // remove the channel
        var tabid = param;
        this.gui.removeTabById(tabid);

        // synchronize the channel client arrays
        var index = -1;
        index = this.channelids.indexOf(tabid);
        this.channelids = this.channelids.without(tabid);
        this.channels   = this.channels.without(this.channels[index]);

        // synchronize the privmsg client arrays
        index = -1;
        index = this.privmsgids.indexOf(tabid);
        this.privmsgids = this.privmsgids.without(tabid);
        this.privmsgs   = this.privmsgs.without(this.privmsgs[index]);
        
      }
    }
    else if (cmd == "privmsg")
    {
      if (resp == "ok")
      {
        // create the new channel
        var tabid = param[0];
        var name  = param[1];
        this.gui.createTab(name, tabid, "pv");
        this.gui.setTabById(tabid);
        
        this.privmsgs.push(name);
        this.privmsgids.push(tabid);
        
      }
      else if (resp == "unknown")
      {
        // speak to unknown user
      }
      else
        alert(cmd + "-"+resp+"-"+param);
    }
    else if (cmd == "nick")
    {
      if (resp == "connected")
      {
        // now join channels comming from sessions
        // or the default one
        <?php
        if (count($u->channels) == 0)
          echo "this.sendRequest('/join', '".addslashes($c->channel)."');\n";
        foreach($u->channels as $ch)
          echo "this.sendRequest('/join', '".addslashes($ch["name"])."');\n";
        foreach($u->privmsg as $pv)
          echo "this.sendRequest('/privmsg', '".addslashes($pv["name"])."');\n";
        ?>
      }
      if (resp == "ok" || resp == "notchanged" || resp == "changed" || resp == "connected")
      {
        this.el_handle.value = param;
      }
      else if (resp == "isused")
      {
        this.askNick(param);
      }
      else
        alert(cmd + "-"+resp+"-"+param);
    }
    else if (cmd == "update")
    {
      if (resp == "ok")
      {
      }
      else if (resp == "privmsg")
      {
        // check if the wanted privmsg exists or not
        var index = this.privmsgs.indexOf(param);
        if (index == -1)
        {
          // it doesn't exists, create it
          this.sendRequest('/privmsg', param);
        }
        else
        {
          var tabid = this.privmsgids[index];
          if (this.gui.getTabId() != tabid)
          {
            // alert user something occurs in the pv tab
            //            alert("todo: highlight the '"+param+"' tab (tabid="+tabid+")");
          }
        }
      }
      //      else
      //        alert(cmd + "-"+resp+"-"+param);
    }
    else if (cmd == "rehash")
    {
      if (resp == "ok")
      {
        this.displayMsg( 'rehash', this.i18n._('Configuration has been rehashed') );
      }
      else if (resp == "ko")
      {
        this.displayMsg( 'rehash', this.i18n._('A problem occurs during rehash') );
      }
    }
    else
      alert(cmd + "-"+resp+"-"+param);
  },
  
  /**
   * Try to complete a nickname like on IRC when pressing the TAB key
   * @todo: improve the algorithme, it should take into account the cursor position
   */
  completeNick: function()
  {
    var w = this.el_words;
    var nick_src = w.value.substring(w.value.lastIndexOf(' ')+1,w.value.length);
    if (nick_src != '')
    {
      var ul_online = this.el_online.firstChild;
      for (var i=0; i<ul_online.childNodes.length; i++)
      {
	var nick = ul_online.childNodes[i].innerHTML;
	if (nick.indexOf(nick_src) == 0)
	  w.value = w.value.replace(nick_src, nick);
      }
    }
  },

  /**
   * Handle the pressed keys
   * see also callbackWords_OnKeydown
   */
  callbackWords_OnKeypress: function(evt)
  {
    var code = (evt.which) ? evt.which : evt.keyCode
    if (code == 9) /* tab key */
    {
      /* FF & Konqueror workaround : ignore TAB key here */
      /* do the nickname completion work like on IRC */
      this.completeNick();
      return false; /* do not leave the tab key default behavior */
    }
    else if (code == 13) /* enter key */
    {
      var w = this.el_words;
      var wval = w.value;
      re = new RegExp("^(\/[a-z]+)( (.*)|)");
      if (wval.match(re))
      {
	/* a user command */
	cmd   = wval.replace(re, '$1');
	param = wval.replace(re, '$3');
	this.sendRequest(cmd, param.substr(0,<?php echo $max_text_len; ?> + this.clientid.length));
      }
      else
      {
	/* a classic 'send' command*/

        // empty messages with only spaces
        rx = new RegExp('^[ ]*$','g');
        wval = wval.replace(rx,'');
        
	/* truncate the text length */
	wval = wval.substr(0, <?php echo $max_text_len; ?>);

	/* colorize the text with current_text_color */
	if (this.current_text_color != '' && wval.length != '')
  	  wval = '[color=#' + this.current_text_color + '] ' + wval + ' [/color]';

	this.sendRequest('/send', wval);
      }
      w.value = '';
      return false;
    }
    else
    {
      /* allow other keys */
      return true;
    }
  },
  /**
   * Handle the pressed keys
   * see also callbackWords_OnKeypress
   */
  callbackWords_OnKeydown: function(evt)
  {
    if (!this.isconnected) return false;
    this.clearError(Array(this.el_words));
    var code = (evt.which) ? evt.which : event.keyCode
    if (code == 9) /* tab key */
    {
      /* IE workaround : ignore TAB key here */
      /* do the nickname completion work like on IRC */
      this.completeNick();
      return false; /* do not leave the tab key default behavior */
    }
    else
    {
      return true;
    }
  },
  callbackWords_OnFocus: function(evt)
  {
    //    if (this.el_handle && this.el_handle.value == '' && !this.minmax_status)
    //      this.el_handle.focus();
  },
  callbackHandle_OnKeydown: function(evt)
  {
  },
  callbackHandle_OnChange: function(evt)
  {
  },
  callback_OnUnload: function(evt)
  {
    /* don't disconnect users when they reload the window
     * this event doesn't only occurs when the page is closed but also when the page is reloaded */
    <?php if ($c->quit_on_closedwindow) { ?>
    if (!this.isconnected) return false;
    this.sendRequest('/quit');
    <?php } ?>
  },

  callbackContainer_OnMousemove: function(evt)
  {
    this.isdraging = true;
  },
  callbackContainer_OnMousedown: function(evt)
  {
    this.isdraging = false;
  },
  callbackContainer_OnMouseup: function(evt)
  {
    if (!this.isdraging)
      if (this.el_words && !this.minmax_status)
        this.el_words.focus();
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
    // get the current selected tab container
    var tabid     = this.gui.getTabId();
    var container = this.gui.getChatContentFromTabId(tabid);

    // create a dummy div to avoid konqueror bug when setting nickmarkers
    var m = document.createElement('div');
    m.innerHTML = '<pre class="<?php echo $prefix; ?>cmd_'+cmd+'">'+msg+'</pre>';
    // finaly append this to the message list
    container.appendChild(m); 
    this.gui.scrollDown(tabid, m);
  },
  
  handleComingRequest: function( cmds )
  {
    var msg_html = $H();
    
    //    alert(cmds.inspect());
    
    //    var html = '';
    for(var mid = 0; mid < cmds.length ; mid++)
    {
      var id          = cmds[mid][0];
      var date        = cmds[mid][1];
      var time        = cmds[mid][2];
      var sender      = cmds[mid][3];
      var recipientid = cmds[mid][4];
      var cmd         = cmds[mid][5];
      var param       = cmds[mid][6];
      //var fromtoday = cmds[mid][6];
      //var oldmsg    = cmds[mid][7];


      // check the nickname is in the list or not
      var nickfound = false;
      for(var i = 0; i < this.nicklist[recipientid].length && !nickfound; i++)
      {
	if (this.nicklist[recipientid][i] == sender)
	  nickfound = true;
      }
      var nickcolor = '';
      if (nickfound) nickcolor = this.getAndAssignNickColor(sender);
      
      // format and post message
      var line = '';
      line += '<div id="<?php echo $prefix; ?>msg'+ id +'" class="<?php echo $prefix; ?>'+ cmd +' <?php echo $prefix; ?>message';
      //      if (oldmsg == 1) line += ' <?php echo $prefix; ?>oldmsg';
      line += '">';
      line += '<span class="<?php echo $prefix; ?>date';
      //      if (fromtoday == 1) line += ' <?php echo $prefix; ?>invisible';
      line += '">'+ date +'</span> ';
      line += '<span class="<?php echo $prefix; ?>heure">'+ time +'</span> ';
      if (cmd == 'send')
      {
	line += ' <span class="<?php echo $prefix; ?>nick">';
	line += '&#x2039;';
	line += '<span ';
        if (nickcolor != '') line += 'style="color: ' + nickcolor + '" ';
	line += 'class="<?php echo $prefix; ?>nickmarker <?php echo $prefix; ?>nick_'+ hex_md5(_to_utf8(sender)) +'">';
	line += sender;
	line += '</span>';
	line += '&#x203A;';
	line += '</span> ';
      }
      if (cmd == 'notice' || cmd == 'me')
	line += '<span class="<?php echo $prefix; ?>words">* '+ this.parseMessage(param) +'</span> ';
      else
	line += '<span class="<?php echo $prefix; ?>words">'+ this.parseMessage(param) +'</span> ';
      line += '</div>';

      if (msg_html[recipientid] == null)
        msg_html[recipientid] = line;
      else
        msg_html[recipientid] += line;
    }

    // loop on all recipients and post messages
    var keys = msg_html.keys();
    for( var i=0; i<keys.length; i++)
    {
      var recipientid  = keys[i];
      var tabid        = recipientid;

      if (this.gui.getTabId() != tabid)
        this.gui.notifyTab(tabid);
      
      // create the tab if it doesn't exists yet
      var recipientdiv = this.gui.getChatContentFromTabId(tabid);
      
      // create a dummy div to avoid konqueror bug when setting nickmarkers
      var m = document.createElement('div');
      m.innerHTML = msg_html[recipientid];
      // finaly append this to the message list
      recipientdiv.appendChild(m);
      this.gui.scrollDown(tabid, m);
    }
  },
  
  /**
   * Call the ajax request function
   * Will query the server
   */
  sendRequest: function(cmd, param)
  {
    var recipientid = this.gui.getTabId();
    var req = cmd+" "+this.clientid+" "+(recipientid==''?'0':recipientid)+(param?" "+param : "");
    //    if (cmd != "/update") alert(req);
    return <?php echo $prefix; ?>handleRequest(req);
  },

  /**
   * update function to poll the server each 'refresh_delay' time
   */
  updateChat: function(start)
  {
    clearTimeout(this.timeout);
    if (start)
    {
      var res = this.sendRequest('/update');
      // adjust the refresh_delay if the connection was lost
      if (res == false) { this.refresh_delay = this.refresh_delay * 2; }
      // setup the next update
      this.timeout = setTimeout('pfc.updateChat(true)', this.refresh_delay);
    }
  },

  /**
   * insert a smiley
   */
  insertSmiley: function(s)
  {
    this.el_words.value += s;
    this.el_words.focus();
  },

  /**
   * fill the nickname list with connected nicknames
   */
  updateNickList: function(tabid,lst)
  {
    //    alert('updateNickList: tabid='+tabid+"-lst="+lst.inspect());
    //var tabid = hex_md5(_to_utf8("ch_"+recipient));

    this.nicklist[tabid] = lst;
    var nicks   = lst;
    var nickdiv = this.gui.getOnlineContentFromTabId(tabid).firstChild;
    var ul = document.createElement('ul');
    for (var i=0; i<nicks.length; i++)
    {
      var li = document.createElement('li');     
      Element.addClassName(li, '<?php echo $prefix; ?>nickmarker');
      Element.addClassName(li, '<?php echo $prefix; ?>nick_'+ hex_md5(_to_utf8(nicks[i])));

      if (nicks[i] != this.nickname)
      {
        // this is someone -> create a privmsg link
        var img = document.createElement('img');
        img.setAttribute('src', '<?php echo $c->getFileUrlFromTheme('images/user.gif'); ?>');
        img.alt = this.i18n._('Private message');
        img.title = img.alt;
        img.style.marginRight = '5px';
        var a = document.createElement('a');
        a.setAttribute('href', 'opo');
        a.pfc_nick = nicks[i];
        a.onclick = function(){pfc.sendRequest('/privmsg', this.pfc_nick); return false;}
        a.appendChild(img);
        li.appendChild(a);
      }
      else
      {
        // this is myself -> do not create a privmsg link
        var img = document.createElement('img');
        img.setAttribute('src', '<?php echo $c->getFileUrlFromTheme('images/user-me.gif'); ?>');
        img.alt = '';
        img.title = img.alt;
        img.style.marginRight = '5px';
        li.appendChild(img);
      }
      
      li.appendChild(document.createTextNode(nicks[i]));      
      li.style.borderBottom = '1px solid #AAA';
      
      ul.appendChild(li);
    }
    var fc = nickdiv.firstChild;
    if (fc)
      nickdiv.replaceChild(ul,fc);
    else
      nickdiv.appendChild(ul,fc);
    this.colorizeNicks(tabid,nickdiv);
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
    //var msgdiv = $('<?php echo $prefix; ?>chat');
    //msgdiv.innerHTML = '';
  },

  /**
   * parse the message
   */
  parseMessage: function(msg)
  {
    var rx = null;
   
    // parse urls
    var rx_url = new RegExp('(^|[^\\"])([a-z]+\:\/\/[a-z0-9.\\/\\?\\=\\&\\-\\_\\#]*)([^\\"]|$)','ig');
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
          msg = msg + '<a href="' + ttt[i] + '"<?php if($openlinknewwindow) echo ' target="_blank"'; ?>>' + (delta>0 ? ttt[i].substring(7,range1)+ ' ... '+ ttt[i].substring(range2,ttt[i].length) :  ttt[i]) + '</a>';
        else
        {
          msg = msg + ttt[i];
        }
      }
    }
    else
      // fallback for IE6/Konqueror which do not support split with regexp
      msg = msg.replace(rx_url, '$1<a href="$2"<?php if($openlinknewwindow) echo ' target="_blank"'; ?>>$2</a>$3');
    
    // replace double spaces by &nbsp; entity
    rx = new RegExp('  ','g');
    msg = msg.replace(rx, '&nbsp;&nbsp;');

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
    rx = new RegExp('\\[email\\]([A-z0-9][\\w.-]*@[A-z0-9][\\w\\-\\.]+\\.[A-z0-9]{2,6})\\[\/email\\]','ig');
    msg = msg.replace(rx, '<a href="mailto: $1">$1</a>'); 
    rx = new RegExp('\\[email=([A-z0-9][\\w.-]*@[A-z0-9][\\w\\-\\.]+\\.[A-z0-9]{2,6})\\](.+?)\\[\/email\\]','ig');
    msg = msg.replace(rx, '<a href="mailto: $1">$2</a>');
    rx = new RegExp('\\[color=([a-zA-Z]+|\\#?[0-9a-fA-F]{6}|\\#?[0-9a-fA-F]{3})](.+?)\\[\/color\\]','ig');
    msg = msg.replace(rx, '<span style="color: $1">$2</span>');
    // parse bbcode colors twice because the current_text_color is a bbcolor
    // so it's possible to have a bbcode color imbrication
    rx = new RegExp('\\[color=([a-zA-Z]+|\\#?[0-9a-fA-F]{6}|\\#?[0-9a-fA-F]{3})](.+?)\\[\/color\\]','ig');
    msg = msg.replace(rx, '<span style="color: $1">$2</span>');   

    // try to parse smileys
    var sl = this.smileys.keys();
    for(var i = 0; i < sl.length; i++)
    {
      rx = new RegExp(RegExp.escape(sl[i]),'g');
      msg = msg.replace(rx, '<img src="'+ this.smileys[sl[i]] +'" alt="' + sl[i] + '" title="' + sl[i] + '" />');
    }
    
    // try to parse nickname for highlighting 
    rx = new RegExp('(^|[ :,;])'+RegExp.escape(this.nickname)+'([ :,;]|$)','gi');
    msg = msg.replace(rx, '$1<strong>'+ this.nickname +'</strong>$2');

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
    return msg;
  },

  /**
   * parse messages and append it to the message list
   */
  parseAndPost: function(msgs)
  {
    /*
    var msgdiv = $('<?php echo $prefix; ?>chat');
    var msgids = Array();

    var html = '';
    for(var mid = 0; mid < msgs.length ; mid++)
    {      
      var id        = msgs[mid][0];
      var date      = msgs[mid][1];
      var heure     = msgs[mid][2];
      var nick      = msgs[mid][3];
      var words     = msgs[mid][4];
      var cmd       = msgs[mid][5];
      var fromtoday = msgs[mid][6];
      var oldmsg    = msgs[mid][7];

      msgids.push(id);

      // check the nickname is in the list or not
      var nickfound = false;
      for(var i = 0; i < this.nicklist.length && !nickfound; i++)
      {
	if (this.nicklist[i] == nick)
	  nickfound = true;
      }
      var nickcolor = '';
      if (nickfound) nickcolor = this.getAndAssignNickColor(nick);

      // format and post message
      var line = '';
      line += '<div id="<?php echo $prefix; ?>msg'+ id +'" class="<?php echo $prefix; ?>'+ cmd +' <?php echo $prefix; ?>message';
      if (oldmsg == 1) line += ' <?php echo $prefix; ?>oldmsg';
      line += '">';
      line += '<span class="<?php echo $prefix; ?>date';
      if (fromtoday == 1) line += ' <?php echo $prefix; ?>invisible';
      line += '">'+ date +'</span> ';
      line += '<span class="<?php echo $prefix; ?>heure">'+ heure +'</span> ';
      if (cmd == 'cmd_msg')
      {
	line += ' <span class="<?php echo $prefix; ?>nick">';
	line += '&#x2039;';
	line += '<span ';
	if (nickcolor != '') line += 'style="color: ' + nickcolor + '" ';
	line += 'class="<?php echo $prefix; ?>nickmarker <?php echo $prefix; ?>nick_'+ hex_md5(_to_utf8(nick)) +'">';
	line += nick;
	line += '</span>';
	line += '&#x203A;';
	line += '</span> ';
      }
      if (cmd == 'cmd_notice' || cmd == 'cmd_me')
	line += '<span class="<?php echo $prefix; ?>words">* '+ this.parseMessage(words) +'</span> ';
      else
	line += '<span class="<?php echo $prefix; ?>words">'+ this.parseMessage(words) +'</span> ';
      line += '</div>';
      html += line;
    }

    // create a dummy div to avoid konqueror bug when setting nickmarkers
    var m = document.createElement('div');
    m.innerHTML = html;

    // finaly append this to the message list
    msgdiv.appendChild(m);
    
    for(var i = 0; i < msgids.length ; i++)
    {
      this.scrolldown($('<?php echo $prefix; ?>msg'+ msgids[i]));
      // colorize messages nicknames
      var root = $('<?php echo $prefix; ?>msg'+ msgids[i]);
      this.refresh_nickmarker(root);
      this.refresh_clock(root);
    }
    */
  },

  /**
   * apply nicknames color to the root childs
   */
  colorizeNicks: function(tabid, root)
  {
    for(var i = 0; i < this.nicklist[tabid].length; i++)
    {
      var cur_nick = this.nicklist[tabid][i];
      var cur_color = this.getAndAssignNickColor(cur_nick);
      this.applyNickColor(root, cur_nick, cur_color);
    }
  },

  /**
   * Initialize the color array used to colirize the nicknames
   */
  reloadColorList: function()
  {
    this.colorlist = Array('#CCCCCC',
			   '#000000',
			   '#3636B2',
			   '#2A8C2A',
			   '#C33B3B',
			   '#C73232',
			   '#80267F',
			   '#66361F',
			   '#D9A641',
			   '#3DCC3D',
			   '#1A5555',
			   '#2F8C74',
			   '#4545E6',
			   '#B037B0',
			   '#4C4C4C',
			   '#959595'
			   );
  },
  

  /**
   * get the corresponding nickname color
   */
  getAndAssignNickColor: function(nick)
  {
    /* check the nickname is colorized or not */
    var allready_colorized = false;
    var nc = '';
    for(var j = 0; j < this.nickcolor.length; j++)
    {
      if (this.nickcolor[j][0] == nick)
      {
	allready_colorized = true;
	nc = this.nickcolor[j][1];
      }
    }
    if (!allready_colorized)
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
    
    var nicktochange = this.getElementsByClassName(root, '<?php echo $prefix; ?>nick_'+ hex_md5(_to_utf8(nick)), '')
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
         (clsIgnore == '' || !els.item(i).className.match(rx2)) ) {
	matches.push(els.item(i));
      }
    }
    return matches;
  },

  showClass: function(root, clsName, clsIgnore, show)
  {
    /*
    var elts = this.getElementsByClassName(root, clsName, clsIgnore);
    for(var i = 0; elts.length > i; i++)
    if (show)
      elts[i].style.display = 'inline';
    else
      elts[i].style.display = 'none';
    */
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
    setCookie('<?php echo $prefix; ?>nickmarker', this.nickmarker);
  },
  refresh_nickmarker: function(root)
  {
    var nickmarker_icon = $('<?php echo $prefix; ?>nickmarker');
    if (!root) root = $('<?php echo $prefix; ?>chat');
    if (this.nickmarker)
    {
      nickmarker_icon.src   = "<?php echo $c->getFileUrlFromTheme('images/color-on.gif'); ?>";
      nickmarker_icon.alt   = this.i18n._('hide_nickname_color');
      nickmarker_icon.title = nickmarker_icon.alt;
      //      this.colorizeNicks(root);
      //      this.colorizeNicks($('<?php echo $prefix; ?>online'));
    }
    else
    {
      nickmarker_icon.src   = "<?php echo $c->getFileUrlFromTheme('images/color-off.gif'); ?>";
      nickmarker_icon.alt   = this.i18n._('show_nickname_color');
      nickmarker_icon.title = nickmarker_icon.alt;
      var elts = this.getElementsByClassName(root, '<?php echo $prefix; ?>nickmarker', '');
      for(var i = 0; elts.length > i; i++)
      {
	/* this is not supported in konqueror =>>>  elts[i].removeAttribute('style');*/
	elts[i].style.color = '';
      }
      var elts = this.getElementsByClassName($('<?php echo $prefix; ?>online'), '<?php echo $prefix; ?>nickmarker', '');
      for(var i = 0; elts.length > i; i++)
      {
	/* this is not supported in konqueror =>>>  elts[i].removeAttribute('style');*/
	elts[i].style.color = '';
      }
    }
  },


  /**
   * Date/Hour show/hide
   */
  /*
  clock_swap: function()
  {
    if (this.clock) {
      this.clock = false;
    } else {
      this.clock = true;
    }
    this.refresh_clock();
    setCookie('<?php echo $prefix; ?>clock', this.clock);
  },
  refresh_clock: function( root )
  {
    var clock_icon = $('<?php echo $prefix; ?>clock');
    if (!root) root = $('<?php echo $prefix; ?>chat');
    if (this.clock)
    {
      clock_icon.src   = "<?php echo $c->getFileUrlFromTheme('images/clock-on.gif'); ?>";
      clock_icon.alt   = this.i18n._('hide_clock');
      clock_icon.title = clock_icon.alt;
      this.showClass(root, '<?php echo $prefix; ?>date', '<?php echo $prefix; ?>invisible', true);
      this.showClass(root, '<?php echo $prefix; ?>heure', '<?php echo $prefix; ?>invisible', true);
    }
    else
    {
      clock_icon.src   = "<?php echo $c->getFileUrlFromTheme('images/clock-off.gif'); ?>";
      clock_icon.alt   = this.i18n._('show_clock');
      clock_icon.title = clock_icon.alt;
      this.showClass(root, '<?php echo $prefix; ?>date', '<?php echo $prefix; ?>invisible', false);
      this.showClass(root, '<?php echo $prefix; ?>heure', '<?php echo $prefix; ?>invisible', false);
    }
    // browser automaticaly scroll up misteriously when showing the dates
    $('<?php echo $prefix; ?>chat').scrollTop += 30;
  },
  */
  
  /**
   * Connect/disconnect button
   */
  connect_disconnect: function()
  {
    if (this.isconnected)
      this.sendRequest('/quit');
    else
      this.sendRequest('/connect');
  },
  refresh_loginlogout: function()
  {
    var loginlogout_icon = $('<?php echo $prefix; ?>loginlogout');
    if (this.isconnected)
    {
      //      this.updateNickList(this.nicklist);
      loginlogout_icon.src   = "<?php echo $c->getFileUrlFromTheme('images/logout.gif'); ?>";
      loginlogout_icon.alt   = this.i18n._('logout');
      loginlogout_icon.title = loginlogout_icon.alt;
    }
    else
    {
      this.clearMessages();
      this.clearNickList();
      loginlogout_icon.src   = "<?php echo $c->getFileUrlFromTheme('images/login.gif'); ?>";
      loginlogout_icon.alt   = this.i18n._('login');
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
    setCookie('<?php echo $prefix; ?>minmax_status', this.minmax_status);
    this.refresh_minimize_maximize();
  },
  refresh_minimize_maximize: function()
  {
    var content = $('<?php echo $prefix; ?>content_expandable');
    var btn     = $('<?php echo $prefix; ?>minmax');
    if (this.minmax_status)
    {
      btn.src = "<?php echo $c->getFileUrlFromTheme('images/maximize.gif'); ?>";
      btn.alt = this.i18n._('maximize');
      btn.title = btn.alt;
      content.style.display = 'none';
    }
    else
    {
      btn.src = "<?php echo $c->getFileUrlFromTheme('images/minimize.gif'); ?>";
      btn.alt = this.i18n._('minimize');
      btn.title = btn.alt;
      content.style.display = 'block';
    }
  },
  
  /**
   * BBcode ToolBar
   */
  insert_text: function(open, close) 
  {
    var msgfield = $('<?php echo $prefix; ?>words');
    
    // IE support
    if (document.selection && document.selection.createRange)
    {
      msgfield.focus();
      sel = document.selection.createRange();
      sel.text = open + sel.text + close;
      msgfield.focus();
    }
    
    // Moz support
    else if (msgfield.selectionStart || msgfield.selectionStart == '0')
    {
      var startPos = msgfield.selectionStart;
      var endPos = msgfield.selectionEnd;
      
      msgfield.value = msgfield.value.substring(0, startPos) + open + msgfield.value.substring(startPos, endPos) + close + msgfield.value.substring(endPos, msgfield.value.length);
      msgfield.selectionStart = msgfield.selectionEnd = endPos + open.length + close.length;
      msgfield.focus();
    }
    
    // Fallback support for other browsers
    else
    {
      msgfield.value += open + close;
      msgfield.focus();
    }
    return;
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
    var colorbtn = this.getElementsByClassName($('<?php echo $prefix; ?>colorlist'), '<?php echo $prefix; ?>color', '');
    for(var i = 0; colorbtn.length > i; i++)
      colorbtn[i].style.border = 'none';

    /* assign the new border style to the selected button */
    this.current_text_color = color;
    setCookie('<?php echo $prefix; ?>current_text_color', this.current_text_color);
    var idname = '<?php echo $prefix; ?>color_' + color;
    $(idname).style.border = '1px solid #666';
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
    setCookie('<?php echo $prefix; ?>showsmileys', this.showsmileys);
    this.refresh_Smileys();
  },
  refresh_Smileys: function()
  {
    var content = $('<?php echo $prefix; ?>smileys');
    var btn = $('<?php echo $prefix; ?>showHideSmileysbtn');
    if (this.showsmileys)
    {
      if (btn)
      {
        btn.src = "<?php echo $c->getFileUrlFromTheme('images/smiley-on.gif'); ?>";
        btn.alt = this.i18n._('hidesmiley');
        btn.title = btn.alt;
      }
      content.style.display = 'block';
    }
    else
    {
      if (btn)
      {
        btn.src = "<?php echo $c->getFileUrlFromTheme('images/smiley-off.gif'); ?>";
        btn.alt = this.i18n._('showsmiley');
        btn.title = btn.alt;
      }
      content.style.display = 'none';
    }
    this.refresh_Chat();
    this.refresh_OnlineAndSmileys();
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
    setCookie('<?php echo $prefix; ?>showwhosonline', this.showwhosonline);
    this.refresh_WhosOnline();
  },
  refresh_WhosOnline: function()
  {
    var content = $('<?php echo $prefix; ?>online');
    var btn = $('<?php echo $prefix; ?>showHideWhosOnlineBtn');
    if (!btn) return;
    if (this.showwhosonline)
    {
      btn.src = "<?php echo $c->getFileUrlFromTheme('images/online-on.gif'); ?>";
      btn.alt = this.i18n._('hideonline');
      btn.title = btn.alt;
      content.style.display = 'block';
    }
    else
    {
      btn.src = "<?php echo $c->getFileUrlFromTheme('images/online-off.gif'); ?>";
      btn.alt = this.i18n._('showonline');
      btn.title = btn.alt;
      content.style.display = 'none';
    }
    this.refresh_Chat();
    this.refresh_OnlineAndSmileys();
  },
  
  /**
   * Resize online and smileys
   */
  refresh_OnlineAndSmileys: function()
  {
    var onlinediv = $('<?php echo $prefix; ?>online');
    var smileysdiv = $('<?php echo $prefix; ?>smileys');
    var style = $H();
    
    if (this.showwhosonline)
    {
      style['height'] = '';
      Element.setStyle(smileysdiv, style);
    }
    else
    {
      style['height'] = '100%';
      Element.setStyle(smileysdiv, style);
    }
    if (this.showsmileys)
    {
      style['height'] = '';
      Element.setStyle(onlinediv, style);
    }
    else
    {
      style['height'] = '100%';
      Element.setStyle(onlinediv, style);
    }

    // for IE7 CSS refresh
    // if fixes the smiley and online boxes resize problem on IE6
    if (document.recalc) setTimeout('document.recalc(true);', 0);
  },
  
  /**
   * Resize chat
   */
  refresh_Chat: function()
  {
    var chatdiv = $('<?php echo $prefix; ?>chat');
    var style = $H();
    if (!this.showwhosonline && !this.showsmileys)
    {
      style['width'] = '100%';
      Element.setStyle(chatdiv, style);
    }
    else
    {
      style['width'] = '';
      Element.setStyle(chatdiv, style);
    }
  }
};


<?php include($c->getFileUrlFromTheme('templates/pfcclient-custo.js.tpl.php')); ?>

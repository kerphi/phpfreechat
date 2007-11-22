/**
 * This class centralize the pfc' Graphic User Interface manipulations
 * (depends on prototype library)
 * @author Stephane Gully
 */
var pfcGui = Class.create();
pfcGui.prototype = {
  
  initialize: function()
  {
//    this.builder = new pfcGuiBuilder();
    this.current_tab    = '';
    this.current_tab_id = '';
    this.tabs       = Array();
    this.tabids     = Array();
    this.tabtypes   = Array();
    this.chatcontent   = $H();
    this.onlinecontent = $H();
    this.scrollpos     = $H();
    this.elttoscroll   = $H();
    this.windownotifynb = 0;
  },

  /**
   * Scroll down the message list area by elttoscroll height
   * - elttoscroll is a message DOM element which has been appended to the tabid's message list
   * - this.elttoscroll is an array containing the list of messages that will be scrolled 
   *   when the corresponding tab will be shown (see setTabById bellow).
   *   It is necessary to keep in cache the list of hidden (because the tab is inactive) messages 
   *   because the 'scrollTop' javascript attribute
   *   will not work if the element (tab content) is hidden.
   */
  scrollDown: function(tabid, elttoscroll)
  {
    // check the wanted tabid is the current active one
    if (this.getTabId() != tabid)
    {
      // no it's not the current active one so just cache the elttoscroll in the famouse this.elttoscroll array
      if (!this.elttoscroll.get(tabid)) this.elttoscroll.set(tabid, Array());
      this.elttoscroll.get(tabid).push(elttoscroll);
      return;
    }
    // the wanted tab is active so just scroll down the tab content element
    // by elttoscroll element height (use 'offsetHeight' attribute)
    var content = this.getChatContentFromTabId(tabid);

    // the next line seems to help with IE6 scroll on the first load
    // http://sourceforge.net/tracker/index.php?func=detail&aid=1568264&group_id=158880&atid=809601
    var dudVar = content.scrollTop;
    content.scrollTop += elttoscroll.offsetHeight+2;
    this.scrollpos.set(tabid, content.scrollTop);
  },
  
  isCreated: function(tabid)
  {
    /*
    for (var i = 0; i < this.tabids.length ; i++)
    {
      if (this.tabids[i] == tabid) return true;
    }
    return false;
    */
    return (indexOf(this.tabids, tabid) >= 0);
  },
  
  setTabById: function(tabid)
  {
    var className = (! is_ie) ? 'class' : 'className';

    // first of all save the scroll pos of the visible tab
    var content = this.getChatContentFromTabId(this.current_tab_id);
    this.scrollpos.set(this.current_tab_id, content.scrollTop);
    
    // start without selected tabs
    this.current_tab     = '';
    this.current_tab_id  = '';
    var tab_to_show = null;
    // try to fine the tab to select and select it! 
    for (var i=0; i<this.tabids.length; i++)
    {
      var tabtitle   = $('pfc_channel_title'+this.tabids[i]);
      var tabcontent = $('pfc_channel_content'+this.tabids[i]);
      if (this.tabids[i] == tabid)
      {
        // select the tab
        tabtitle.setAttribute(className, 'selected');
        //Element.addClassName(tabtitle, 'selected');
        tab_to_show = tabcontent;
        this.current_tab     = this.tabs[i];
        this.current_tab_id  = tabid;
      }
      else
      {
        // unselect the tab
        tabtitle.setAttribute(className, ''); 
        //Element.removeClassName(tabtitle, 'selected');
        tabcontent.style.display = 'none';
      }
    }

    // show the new selected tab
    tab_to_show.style.display = 'block';
    
    // restore the scroll pos
    var content = this.getChatContentFromTabId(tabid);
    content.scrollTop = this.scrollpos.get(tabid);

    // scroll the new posted message
    if (this.elttoscroll.get(tabid) &&
        this.elttoscroll.get(tabid).length > 0)
    {
      // on by one
      for (var i=0; i<this.elttoscroll.get(tabid).length; i++)
        this.scrollDown(tabid,this.elttoscroll.get(tabid)[i]);
      // empty the cached element list because it has been scrolled
      this.elttoscroll.set(tabid, Array());
    }
    
    this.unnotifyTab(tabid);
  },
  
  getTabId: function()
  {
    return this.current_tab_id;
  },

  getChatContentFromTabId: function(tabid)
  {
    var className = (! is_ie) ? 'class' : 'className';

    // return the chat content if it exists
    var cc = this.chatcontent.get(tabid);
    if (cc) return cc;

    // if the chat content doesn't exists yet, just create a cached one
    cc = document.createElement('div');
    cc.setAttribute('id', 'pfc_chat_'+tabid);
    cc.setAttribute(className, 'pfc_chat');

    //    Element.addClassName(cc, 'pfc_chat');
    cc.style.display = "block"; // needed by IE6 to show the online div at startup (first loaded page)
    //    cc.style.marginLeft = "5px";

    this.chatcontent.set(tabid,cc);
    return cc;
  },
  getOnlineContentFromTabId: function(tabid)
  {
    var className = (! is_ie) ? 'class' : 'className';

    // return the online content if it exists
    var oc = this.onlinecontent.get(tabid);
    if (oc) return oc;

    oc = document.createElement('div');
    oc.setAttribute('id', 'pfc_online_'+tabid);
    oc.setAttribute(className, 'pfc_online');
    //Element.addClassName(oc, 'pfc_online');
    // I set the border style here because seting it in the CSS is not taken in account
    //    oc.style.borderLeft = "1px solid #555";
    oc.style.display = "block"; // needed by IE6 to show the online div at startup (first loaded page)

    this.onlinecontent.set(tabid,oc);
    return oc;
  },
  
  removeTabById: function(tabid)
  {
    // remove the widgets
    var tabparent_t = $('pfc_channels_list');
    var tabparent_c = $('pfc_channels_content');
    var tab_t = $('pfc_channel_title'+tabid);
    var tab_c = $('pfc_channel_content'+tabid);
    tabparent_t.removeChild(tab_t);
    tabparent_c.removeChild(tab_c);

    // empty the chat div content
    var div_chat = this.getChatContentFromTabId(tabid);
    div_chat.innerHTML = ''; // do not use ".update('')" or ".remove()" because it do not works on IE6

    // remove the tab from the list
    var tabpos = indexOf(this.tabids, tabid);
    var name = this.tabs[tabpos];
    this.tabids     = without(this.tabids, this.tabids[tabpos]);
    this.tabs       = without(this.tabs, this.tabs[tabpos]);
    this.tabtypes   = without(this.tabtypes, this.tabtypes[tabpos]);
    tabpos = indexOf(this.tabids, this.getTabId());
    if (tabpos<0) tabpos = 0;
    if (this.tabids[tabpos])
      this.setTabById(this.tabids[tabpos]);
    return name;    
  },

  /*
  removeTabByName: function(name)
  {
    var tabid = _to_utf8(name).md5();
    var ret = this.removeTabById(tabid);
    if (ret == name)
      return tabid;
    else
      return 0;
  },
  */
  
  createTab: function(name, tabid, type)
  {
    var className = (! is_ie) ? 'class' : 'className';

    // do not create empty tabs
    if(name == '') return;
    if(tabid == '') return;

    // do not create twice a the same tab
    if (this.isCreated(tabid)) return;

    //    var tabid = _to_utf8(name).md5();
    //alert(name+'='+tabid);
    this.tabs.push(name);
    this.tabids.push(tabid);
    this.tabtypes.push(type);

    //alert(this.tabs.toString());
    
    var li_title = document.createElement('li');
    li_title.setAttribute('id', 'pfc_channel_title'+tabid);

    var li_div = document.createElement('div');
    li_div.setAttribute('id', 'pfc_tabdiv'+tabid);
    li_title.appendChild(li_div);
    
    var a1 = document.createElement('a');
    a1.setAttribute(className, 'pfc_tabtitle');
    a1.setAttribute('href', '#');
    a1.pfc_tabid = tabid;
    a1.onclick = function(){pfc.gui.setTabById(this.pfc_tabid); return false;}
    li_div.appendChild(a1);

    if (pfc_displaytabimage)
    {
      var img = document.createElement('img');
      img.setAttribute('id', 'pfc_tabimg'+tabid);
      if (type == 'ch')
        img.setAttribute('src', pfc.res.getFileUrl('images/ch.gif'));
      if (type == 'pv')
        img.setAttribute('src', pfc.res.getFileUrl('images/pv.gif'));
      a1.appendChild(img);
    }
    
    // on ajoute le nom du channel
    a1.appendChild(document.createTextNode(name));

    if (pfc_displaytabclosebutton || type == 'pv')
    {
      var a2 = document.createElement('a');
      a2.pfc_tabid = tabid;
      a2.pfc_tabname = name;
      a2.pfc_tabtype = type;
      a2.onclick = function(){
        var msg = (type == 'pv' ? 'Are you sure you want to close this tab ?' :
                                  'Do you really want to leave this room ?');
        var res = confirm(pfc.res.getLabel(msg));
        if (res == true)
          pfc.sendRequest('/leave',this.pfc_tabid);
        return false;
      }
      a2.alt   = pfc.res.getLabel('Close this tab');
      a2.title = a2.alt;
      a2.setAttribute(className, 'pfc_tabclose');
      var img = document.createElement('img');
      img.setAttribute('src', pfc.res.getFileUrl('images/tab_remove.gif'));
      a2.appendChild(img);
      li_div.appendChild(a2);
    }
    
    var div_content = document.createElement('div');
    div_content.setAttribute('id', 'pfc_channel_content'+tabid);   
    //    Element.addClassName(div_content, 'pfc_content');
    div_content.setAttribute(className, 'pfc_content');
    div_content.style.display = 'none';

    var div_chat    = this.getChatContentFromTabId(tabid);
    var div_online  = this.getOnlineContentFromTabId(tabid);
    div_content.appendChild(div_chat);
    div_content.appendChild(div_online);
   
    $('pfc_channels_list').appendChild(li_title);
    $('pfc_channels_content').appendChild(div_content);

    // force the height of the chat/online zone in pixel in order fix blank screens on IE6
    div_chat.style.height   = ($('pfc_channels_content').offsetHeight-1)+'px';
    div_online.style.height = ($('pfc_channels_content').offsetHeight-1)+'px';

    return tabid;
  },

  /**
   * This function change the window title in order to catch the attention
   */
  notifyWindow: function()
  {
    this.windownotifynb += 1;
    var rx = new RegExp('^\\[[0-9]+\\](.*)','ig');
    document.title = document.title.replace(rx,'$1');
    document.title = '['+this.windownotifynb+']'+document.title;
    
    // play the sound    
    var soundcontainer = document.getElementById('pfc_sound_container');
    if (pfc.issoundenable)
    {
      var flash = '<object style="visibility:hidden" classid="clsid:D27CDB6E-AE6D-11CF-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,40,0" width="0" height="0">';
      flash += '<param name="movie" value="' + pfc.res.getFileUrl('sound.swf') + '">';
      flash += '<param name="quality" value="High">';
      flash += '<embed style="visibility:hidden" src="' + pfc.res.getFileUrl('sound.swf') + '" pluginspage="http://www.macromedia.com/go/getflashplayer" type="application/x-shockwave-flash" width="0" height="0" />';
      flash += '</object>';
      soundcontainer.innerHTML = flash;
    }    
  },
  unnotifyWindow: function()
  {
    this.windownotifynb = 0;
    var rx = new RegExp('^\\[[0-9]+\\](.*)','ig');
    document.title = document.title.replace(rx,'$1');
    
    // stop the sound    
    var soundcontainer = document.getElementById('pfc_sound_container');
    if (pfc.issoundenable)
      soundcontainer.innerHTML = '';
  },

  /**
   * This function change the tab icon in order to catch the attention
   */
  notifyTab: function(tabid)
  {
    var className = (! is_ie) ? 'class' : 'className';

    // first of all be sure the tab highlighting is cleared
    this.unnotifyTab(tabid);

    var tabpos = indexOf(this.tabids, tabid);
    var tabtype = this.tabtypes[tabpos];
   
    // handle the tab's image modification
    var img = $('pfc_tabimg'+tabid);
    if (img)
    {
      if (tabtype == 'ch')
        img.src = pfc.res.getFileUrl('images/ch-active.gif');
      if (tabtype == 'pv')
        img.src = pfc.res.getFileUrl('images/pv-active.gif');
    }
  
    // handle the blicking effect
    var div = $('pfc_tabdiv'+tabid);
    if (div)
    {
      if (div.blinkstat == true)
      {
        div.setAttribute(className, 'pfc_tabblink1');
      }
      else
      {
        div.setAttribute(className, 'pfc_tabblink2');
      }
      div.blinkstat = !div.blinkstat;
      div.blinktimeout = setTimeout('pfc.gui.notifyTab(\''+tabid+'\');', 500);
    }
  },

  /**
   * This function restore the tab icon to its default value
   */
  unnotifyTab: function(tabid)
  {
    var className = (! is_ie) ? 'class' : 'className';

    var tabpos = indexOf(this.tabids, tabid);
    var tabtype = this.tabtypes[tabpos];

    // restore the tab's image
    var img = $('pfc_tabimg'+tabid);
    if (img)
    {
      if (tabtype == 'ch')
        img.src = pfc.res.getFileUrl('images/ch.gif');
      if (tabtype == 'pv')
        img.src = pfc.res.getFileUrl('images/pv.gif');
    }

    // stop the blinking effect
    var div = $('pfc_tabdiv'+tabid);
    if (div) 
    {
      div.removeAttribute(className);
      clearTimeout(div.blinktimeout);
    }
  },

  loadSmileyBox: function()
  {
    var container = $('pfc_smileys');
    var smileys = pfc.res.getSmileyReverseHash();//getSmileyHash();
    var sl = smileys.keys();
    pfc.res.sortSmileyKeys(); // Sort smiley keys once.
    for(var i = 0; i < sl.length; i++)
    {
      s_url    = sl[i];
      s_symbol = smileys.get(sl[i]);
      s_symbol = s_symbol.unescapeHTML();
      // Replace &quot; with " for IE and Webkit browsers.
      // The prototype.js version 1.5.1.1 unescapeHTML() function does not do this.
      if (is_ie || is_webkit)
        s_symbol = s_symbol.replace(/&quot;/g,'"');
      
      var img = document.createElement('img');
      img.setAttribute('src', s_url);
      img.setAttribute('alt', s_symbol);
      img.setAttribute('title', s_symbol);
      img.s_symbol = s_symbol;
      img.onclick = function(){ pfc.insertSmiley(this.s_symbol); }
      container.appendChild(img);
      container.appendChild(document.createTextNode(' ')); // so smileys will wrap fine if lot of smiles in theme.
    }
  },

  loadBBCodeColorList: function()
  {
    var className = (! is_ie) ? 'class' : 'className';

    // color list
    var clist = $('pfc_colorlist');
    var clist_v = pfc_bbcode_color_list;
    for (var i=0 ; i<clist_v.length ; i++)
    {
      var bbc = clist_v[i];
      var elt = document.createElement('img');
      elt.bbc = bbc;
      elt.setAttribute(className, 'pfc_color');
      elt.setAttribute('id', 'pfc_color_'+bbc);
      elt.style.backgroundColor = '#'+bbc;
      elt.setAttribute('src', pfc.res.getFileUrl('images/color_transparent.gif'));
      elt.setAttribute('alt', bbc);
      elt.onclick = function(){ pfc.switch_text_color(this.bbc); }
      clist.appendChild(elt);
    }
  }
};

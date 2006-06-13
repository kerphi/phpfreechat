/**
 * This class centralize the pfc' Graphic User Interface manipulations
 * (depends on prototype library)
 * @author Stephane Gully
 */
var pfcGui = Class.create();
pfcGui.prototype = {
  
  initialize: function(i18n)
  {
    this.i18n = i18n;
    
    this.current_tab    = '';
    this.current_tab_id = '';
    this.tabs       = Array();
    this.tabids     = Array();
    this.tabtypes   = Array();
    this.chatcontent   = $H();
    this.onlinecontent = $H();
    this.scrollpos     = $H();
  },

  /**
   * scroll down from the posted message height
   */
  scrollDown: function(tabid, elttoscroll)
  {
    if (this.getTabId() != tabid) return; /* do nothing if this is not the current tab or it will reset the scrollbar position to 0 */
    var content = this.getChatContentFromTabId(tabid);
    content.scrollTop += elttoscroll.offsetHeight+2;
    this.scrollpos[tabid] = content.scrollTop;
  },
  
  isCreated: function(tabid)
  {
    return (this.tabids.indexOf(tabid) >= 0);
  },
  
  setTabById: function(tabid)
  {
    // first of all save the scroll post of the visible tab
    var content = this.getChatContentFromTabId(this.current_tab_id);
    this.scrollpos[this.current_tab_id] = content.scrollTop;
    
    // start without selected tabs
    this.current_tab     = '';
    this.current_tab_id  = '';
    var tab_to_show = null;
    // try to fine the tab to select and select it!
    for (var i=0; i<this.tabids.length; i++)
    {
      var tabtitle   = $('<?php echo $prefix; ?>channel_title'+this.tabids[i]);
      var tabcontent = $('<?php echo $prefix; ?>channel_content'+this.tabids[i]);
      if (this.tabids[i] == tabid)
      {
        // select the tab
        Element.addClassName(tabtitle, 'selected');
        tab_to_show = tabcontent;
        this.current_tab     = this.tabs[i];
        this.current_tab_id  = tabid;
      }
      else
      {
        // unselect the tab
        Element.removeClassName(tabtitle, 'selected');
        tabcontent.style.display = 'none';
      }
    }

    // show the new selected tab
    tab_to_show.style.display = 'block';
    // restore the scroll pos
    var content = this.getChatContentFromTabId(tabid);
    content.scrollTop = this.scrollpos[tabid];

    this.unnotifyTab(tabid);
  },
  
  getTabId: function()
  {
    return this.current_tab_id;
  },

  getChatContentFromTabId: function(tabid)
  {
    // return the chat content if it exists
    var cc = this.chatcontent[tabid];
    if (cc) return cc;

    // if the chat content doesn't exists yet, just create a cached one
    cc = document.createElement('div');
    cc.setAttribute('id', '<?php echo $prefix; ?>chat_'+tabid);
    Element.addClassName(cc, '<?php echo $prefix; ?>chat');
    cc.style.display = "block"; // needed by IE6 to show the online div at startup (first loaded page)
    cc.style.marginLeft = "5px";

    this.chatcontent[tabid] = cc;
    return cc;
  },
  getOnlineContentFromTabId: function(tabid)
  {
    // return the online content if it exists
    var oc = this.onlinecontent[tabid];
    if (oc) return oc;

    oc = document.createElement('div');
    oc.setAttribute('id', '<?php echo $prefix; ?>online_'+tabid);
    Element.addClassName(oc, '<?php echo $prefix; ?>online');
    // I set the border style here because seting it in the CSS is not taken in account
    oc.style.borderLeft = "1px solid #555";
    oc.style.display = "block"; // needed by IE6 to show the online div at startup (first loaded page)
    
    // Create a dummy div to add padding
    var div = document.createElement('div');
    div.style.padding = "5px";
    oc.appendChild(div);

    this.onlinecontent[tabid] = oc;
    return oc;
  },
  
  removeTabById: function(tabid)
  {
    // remove the widgets
    var tabparent_t = $('<?php echo $prefix; ?>channels_list');
    var tabparent_c = $('<?php echo $prefix; ?>channels_content');
    var tab_t = $('<?php echo $prefix; ?>channel_title'+tabid);
    var tab_c = $('<?php echo $prefix; ?>channel_content'+tabid);
    tabparent_t.removeChild(tab_t);
    tabparent_c.removeChild(tab_c);

    // empty the chat div content
    var div_chat = this.getChatContentFromTabId(tabid);
    div_chat.innerHTML = '';

    // remove the tab from the list
    var tabpos = this.tabids.indexOf(tabid);
    var name = this.tabs[tabpos];
    this.tabids     = this.tabids.without(this.tabids[tabpos]);
    this.tabs       = this.tabs.without(this.tabs[tabpos]);
    this.tabtypes   = this.tabtypes.without(this.tabtypes[tabpos]);
    tabpos = this.tabids.indexOf(this.getTabId());
    if (tabpos<0) tabpos = 0;
    this.setTabById(this.tabids[tabpos]);
    return name;    
  },

  /*
  removeTabByName: function(name)
  {
    var tabid = hex_md5(_to_utf8(name));
    var ret = this.removeTabById(tabid);
    if (ret == name)
      return tabid;
    else
      return 0;
  },
  */
  
  createTab: function(name, tabid, type)
  {
    // do not create empty tabs
    if(name == '') return;
    if(tabid == '') return;

    // do not create twice a the same tab
    if (this.isCreated(tabid)) return;
    
    //    var tabid = hex_md5(_to_utf8(name));
    //alert(name+'='+tabid);
    this.tabs.push(name);
    this.tabids.push(tabid);
    this.tabtypes.push(type);

    var li_title = document.createElement('li');
    li_title.setAttribute('id', '<?php echo $prefix; ?>channel_title'+tabid);

    var li_div = document.createElement('div');
    li_title.appendChild(li_div);
    
    var img = document.createElement('img');
    img.setAttribute('id', '<?php echo $prefix; ?>tabimg'+tabid);
    if (type == 'ch')
      img.setAttribute('src', '<?php echo $c->getFileUrlFromTheme('images/ch.gif'); ?>');
    if (type == 'pv')
      img.setAttribute('src', '<?php echo $c->getFileUrlFromTheme('images/pv.gif'); ?>');
    var a1 = document.createElement('a');
    Element.addClassName(a1, '<?php echo $prefix; ?>tabtitle');
    a1.appendChild(img);
    a1.appendChild(document.createTextNode(name));
    a1.setAttribute('href', '');
    a1.pfc_tabid = tabid;
    a1.onclick = function(){pfc.gui.setTabById(this.pfc_tabid); return false;}
    li_div.appendChild(a1);
    
    var a2 = document.createElement('a');
    a2.pfc_tabid = tabid;
    a2.onclick = function(){
      var res = confirm('<?php echo _pfc("Do you really want to leave this room ?"); ?>');
      if (res == true) pfc.sendRequest('/leave', this.pfc_tabid); return false;
    }
    a2.alt   = this.i18n._('Close this tab');
    a2.title = a2.alt;
    Element.addClassName(a2, '<?php echo $prefix; ?>tabclose');
    var img = document.createElement('img');
    img.setAttribute('src', '<?php echo $c->getFileUrlFromTheme('images/tab_remove.gif'); ?>');
    a2.appendChild(img);
    li_div.appendChild(a2);
    
    var div_content = document.createElement('div');
    div_content.setAttribute('id', '<?php echo $prefix; ?>channel_content'+tabid);   
    Element.addClassName(div_content, '<?php echo $prefix; ?>content');
    div_content.style.display = 'none';

    var div_chat    = this.getChatContentFromTabId(tabid);
    var div_online  = this.getOnlineContentFromTabId(tabid);
    div_content.appendChild(div_chat);
    div_content.appendChild(div_online);
    
    $('<?php echo $prefix; ?>channels_list').appendChild(li_title);
    $('<?php echo $prefix; ?>channels_content').appendChild(div_content);

    return tabid;
  },

  /**
   * This function change the tab icon in order to catch the attention
   */
  notifyTab: function(tabid)
  {
    var tabpos = this.tabids.indexOf(tabid);
    var tabtype = this.tabtypes[tabpos];
    var img = $('<?php echo $prefix; ?>tabimg'+tabid);
    if (img)
    {
      if (tabtype == 'ch')
        img.src = '<?php echo $c->getFileUrlFromTheme('images/ch-active.gif'); ?>';
      if (tabtype == 'pv')
        img.src = '<?php echo $c->getFileUrlFromTheme('images/pv-active.gif'); ?>';
    }
  },

  /**
   * This function restore the tab icon to its default value
   */
  unnotifyTab: function(tabid)
  {
    var tabpos = this.tabids.indexOf(tabid);
    var tabtype = this.tabtypes[tabpos];
    var img = $('<?php echo $prefix; ?>tabimg'+tabid);
    if (img)
    {
      if (tabtype == 'ch')
        img.src = '<?php echo $c->getFileUrlFromTheme('images/ch.gif'); ?>';
      if (tabtype == 'pv')
        img.src = '<?php echo $c->getFileUrlFromTheme('images/pv.gif'); ?>';
    }
  }
  
};

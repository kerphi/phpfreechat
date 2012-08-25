/*jslint node: true, maxlen: 500, maxerr: 50, indent: 2 */
'use strict';

/**
 * phpfreechat's init functions
 */
var phpFreeChat = (function (pfc, $, window, undefined) {

  /**
   * phpFreeChat entry point
   */
  pfc.init = function (plugin) {
    // copy jquery attributes needed by phpfreechat
    pfc.element = plugin.element;
    pfc.options = plugin.options;

    // users and channels data (cache)
    pfc.users    = {}; // users of the chat (uid -> userdata)
    pfc.channels = {}; // channels of the chat (cid -> chandata)

    // session data
    pfc.uid = null; // current connected user
    pfc.cid = null; // current active channel

    // check backlink presence
    if (pfc.options.check_backlink && !pfc.hasBacklink()) {
      return;
    }
    
    // load the interface
    pfc.loadHTML();
        
    // try to authenticate
    //pfc.logout(function (err) { pfc.login(); });
    pfc.login();
    
    // when logged in
    $(pfc.element).bind('pfc-login', function (evt, pfc, userdata) {
      pfc.uid = userdata.id;
      pfc.users[userdata.id] = userdata;
      pfc.cid = 'xxx'; // static channel id for the first 2.x version
      
      if (pfc.options.focus_on_connect) {
        // give focus to input textarea when auth
        $('div.pfc-compose textarea').focus();
      }
      
      pfc.join(pfc.cid);
    });

    // when logged out
    $(pfc.element).bind('pfc-logout', function (evt, pfc, userdata) {
      pfc.uid = null;
      pfc.clearUserList();
    });

  }

  /**
   * Check backlink in the page
   */
  pfc.hasBacklink = function () {
    var backlink = $('a[href="http://www.phpfreechat.net"]').length;
    if (!backlink) {
      $(pfc.element).html(
        '<div class="pfc-backlink">'
        + '<p>Please insert the phpfreechat backlink somewhere in your HTML in order to load the chat. The attended backlink is:</p>'
        + '<pre>'
        + $('<div/>').text('<a href="http://www.phpfreechat.net">phpFreeChat: simple Web chat</a>').html()
        + '</pre>'
        + '</div>'
      );
      return false;
    }
    return true;
  }

  /**
   * Load HTML used by the interface in the browser DOM
   */
  pfc.loadHTML = function () {
    // load chat HTML
    $(pfc.element).html(
        (pfc.options.loadTestData ?
        '      <div class="pfc-content">'
      : '      <div class="pfc-content pfc-notabs">')
      + '        <div class="pfc-tabs">'
      + '          <ul>'
      + (pfc.options.loadTestData ? ''
      + '            <li class="channel active">'
      + '              <div class="icon"></div>'
      + '              <div class="name">Channel 1</div>'
      + '              <div class="close"></div>'
      + '            </li>'
      + '            <li class="channel">'
      + '              <div class="icon"></div>'
      + '              <div class="name">Channel 2</div>'
      + '              <div class="close"></div>'
      + '            </li>'
      + '            <li class="pm">'
      + '              <div class="icon"></div>'
      + '              <div class="name">admin</div>'
      + '              <div class="close"></div>'
      + '            </li>'
      : '')
      + '            <li class="new-tab">'
      + '              <div class="icon"></div>'
      + '            </li>'
      + '          </ul>'
      + '        </div>'
      + ''
      + '        <div class="pfc-topic">'
      + '          <p><span class="pfc-topic-label">Topic:</span> <span class="pfc-topic-value">no topic for this channel</span></p>'
      + '        </div>'
      + ''
      + '        <div class="pfc-messages">'
      + (pfc.options.loadTestData ? ''
      + '          <div class="messages-group" data-stamp="1336815502" data-from="kerphi">'
      + '            <div class="avatar"><img src="http://www.gravatar.com/avatar/ae5979732c49cae7b741294a1d3a8682?d=wavatar&s=30" alt="" /></div>'
      + '            <div class="date">11:38:21</div>'
      + '            <div class="name">kerphi</div>'
      + '            <div class="message">123</div>'
      + '            <div class="message">456</div>'
      + '          </div>'
      + '          <div class="messages-group" data-stamp="1336815503" data-from="admin">'
      + '            <div class="avatar"><img src="http://www.gravatar.com/avatar/00000000000000000000000000000001?d=wavatar&s=30" alt="" /></div>'
      + '            <div class="date">11:38:22</div>'
      + '            <div class="name">admin</div>'
      + '            <div class="message">Hello</div>'
      + '            <div class="message">World</div>'
      + '            <div class="message">!</div>'
      + '          </div>'
      : '')
      + '        </div>'
      + ''
      + '        <div class="pfc-users">'
      + '          <div class="pfc-role-admin">'
      + '            <p class="role-title">Administrators</p>'
      + '            <ul>'
      + (pfc.options.loadTestData ? ''
      + '              <li class="first">'
      + '                <div class="status st-active"></div>'
      + '                <div class="name">admin</div>'
      + '                <div class="avatar"><img src="http://www.gravatar.com/avatar/00000000000000000000000000000001?d=wavatar&s=20" alt="" /></div>'
      + '              </li>'
      : '')
      + '            </ul>'
      + '          </div>'
      + '          <div class="pfc-role-user">'
      + '            <p class="role-title">Users</p>'
      + '            <ul>'
      + (pfc.options.loadTestData ? ''
      + '              <li class="first">'
      + '                <div class="status st-active"></div>'
      + '                <div class="name myself">kerphi</div>'
      + '                <div class="avatar"><img src="http://www.gravatar.com/avatar/ae5979732c49cae7b741294a1d3a8682?d=wavatar&s=20" alt="" /></div>'
      + '              </li>'
      + '              <li>'
      + '                <div class="status st-inactive"></div>'
      + '                <div class="name">Stéphane Gully</div>'
      + '                <div class="avatar"><img src="http://www.gravatar.com/avatar/00000000000000000000000000000002?d=wavatar&s=20" alt="" /></div>'
      + '              </li>'
      : '')
      + '            </ul>'
      + '          </div>'
      + '        </div>'
      + ''
      + '        <div class="pfc-footer">'
      + (pfc.options.show_powered_by ? 
        '          <p class="logo"><a href="http://www.phpfreechat.net">Powered by phpFreeChat</a></p>' :
        '')
      //+ '          <p class="ping">150ms</p>'
      + '          <ul>'
      //+ '            <li><div class="logout-btn"></div></li>'
      //+ '            <li><div class="smiley-btn" title="Not implemented"></div></li>'
      //+ '            <li><div class="sound-btn" title="Not implemented"></div></li>'
      //+ '            <li><div class="online-btn"></div></li>'
      + '          </ul>'
      + '        </div>'
      + ''
      + '        <div class="pfc-compose">'
      + '          <textarea data-to="channel|xxx"></textarea>'
      + '        </div>'
      + ''
      + '        <div class="pfc-modal-overlay"></div>'
      + '        <div class="pfc-modal-box"></div>'
      + '      </div>'
    );

    // connect the textarea enter key event
    $('.pfc-compose textarea').keydown(function (evt) {
      if (evt.keyCode == 13 && evt.shiftKey === false) {
        pfc.postToChannel(pfc.cid, $('.pfc-compose textarea').val());
        $('.pfc-compose textarea').val('');
        return false;
      }
    });

    // once html is loaded init modalbox
    // because modalbox is hooked in pfc's html
    pfc.modalbox.init();

    // call the loaded callback when finished
    if (pfc.options.loaded) {
      pfc.options.loaded(pfc);
    }
    // trigger the pfc-loaded event when finished
    setTimeout(function () { $(pfc.element).trigger('pfc-loaded', [ pfc ]) }, 0);
  };


  return pfc;
}(phpFreeChat || {}, jQuery, window));
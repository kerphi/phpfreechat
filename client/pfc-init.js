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
    pfc.loadResponsiveBehavior();
    pfc.loadActionMenu();
    pfc.loadThemeUI();    
    
    // run quick tests
    pfc.checkServerConfig(pfc.startChatLogic);
  }

  /**
   * Run few tests to be sure the server is ready to receive requests
   */
  pfc.checkServerConfig = function (next) {
  
    if (pfc.options.check_server_config) {
      pfc.checkServerConfigPHP(function () {
        pfc.checkServerConfigRewrite(next);
      });
    } else {
      next();
    }
    
  };

  /**
   * Test the server php config file
   */
  pfc.checkServerConfigPHP = function (next) {
    $.ajax({
      type: 'GET',
      url:  pfc.options.serverCheckUrl
    }).done(function (errors) {
      // parse json
      try {
        if (errors instanceof String) {
          errors = JSON.parse(errors);
        }
      } catch (err) {
        errors = [ errors ];
      }
      // show errors if one
      if (errors && errors.length > 0) {
        pfc.showErrorsPopup(errors);
      } else {
        next();
      }
    }).error(function () {
      pfc.showErrorsPopup([ 'Unknown error: check.php cannot be found' ]);
    });
  };
      
  /**
   * Test the rewrite rules are enabled on the server
   */
  pfc.checkServerConfigRewrite = function (next) {
    var err_rewrite_msg = '.htaccess must be allowed on your server (AllowOverride All) and mod_rewrite must be enabled on your server and correctly configured ("RewriteBase" could be adjusted in server/.htaccess file)';
    $.ajax({
      type: 'GET',
      url:  pfc.options.serverUrl + '/status'
    }).done(function (status) {
      if (!status || !status.running) {
        pfc.showErrorsPopup([ err_rewrite_msg ]);
      } else {
        next();
      }
    }).error(function () {
      pfc.showErrorsPopup([ err_rewrite_msg ]);
    });
  };
  
  /**
   * Start to authenticate and to prepare chat dynamic
   */
  pfc.startChatLogic = function () {

    // show donation popup if not skiped
    pfc.showDonationPopup(function () {
      if (!pfc.options.skip_auth) {
        // then try to authenticate
        pfc.login();
      }
    });
    
    // when logged in
    $(pfc.element).bind('pfc-login', function (evt, pfc, userdata) {
      pfc.uid = userdata.id;
      pfc.users[userdata.id] = userdata;
      pfc.cid = 'xxx'; // static channel id for the first 2.x version
      
      if (pfc.options.focus_on_connect) {
        // give focus to input textarea when auth
        $('div.pfc-compose textarea').focus();
      }
      
      // start to read pending messages on the server
      pfc.readPendingMessages(true); // true = loop
      
      // join the default channel
      pfc.join(pfc.cid);
    });

    // when logged out
    $(pfc.element).bind('pfc-logout', function (evt, pfc, userdata) {
      pfc.uid = null;
      pfc.clearUserList();
    });
  };
  
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
  };

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
      + '            <li class="new-tab">'
      + '              <div class="icon"></div>'
      + '            </li>'
      : '')
      + '          </ul>'
      + '        </div>'
      + ''
      + '        <div class="pfc-topic">'
      + '          <a class="pfc-toggle-tabs"></a>'
      + '          <p><span class="pfc-topic-label">Topic:</span> <span class="pfc-topic-value">no topic for this channel</span></p>'
      + '          <a class="pfc-toggle-users"></a>'
      + '        </div>'
      + ''
      + '        <div class="pfc-messages">'
      + '          <div class="pfc-message-mobile-padding"></div>' // used to move message at bottom on mobile interface
      + (pfc.options.loadTestData ? ''
      + '          <div class="messages-group" data-stamp="1336815502" data-from="kerphi">'
      + '            <div class="avatar"><img src="http://www.gravatar.com/avatar/ae5979732c49cae7b741294a1d3a8682?d=wavatar&s=30" alt="" /></div>'
      + '            <div class="date">11:38:21</div>'
      + '            <div class="name">kerphi</div>'
      + '            <div class="message">123 <a href="#">test de lien</a></div>'
      + '            <div class="message">456</div>'
      + '          </div>'
      + '          <div class="messages-group" data-stamp="1336815503" data-from="admin">'
      + '            <div class="avatar"><img src="http://www.gravatar.com/avatar/00000000000000000000000000000001?d=wavatar&s=30" alt="" /></div>'
      + '            <div class="date">11:38:22</div>'
      + '            <div class="name">admin</div>'
      + '            <div class="message">Hello</div>'
      + '            <div class="message">World</div>'
      + '            <div class="message">!</div>'
      + '            <div class="message">A very very very very very very very very very very very very very very very very very very very long text</div>'
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
        '          <p class="logo"><a href="http://www.phpfreechat.net" class="bt-powered" target="_blank">Powered by phpFreeChat</a><a href="http://www.phpfreechat.net/donate" class="bt-donate" title="Phpfreechat is for you and needs you" target="_blank">Donate</a></p>' :
        '')
      + (pfc.options.loadTestData ? ''
      + '          <p class="ping">150ms</p>'
      + '          <ul>'
      + '            <li><div class="logout-btn" title="Logout"></div></li>'
      + '            <li><div class="smiley-btn" title="Not implemented"></div></li>'
      + '            <li><div class="sound-btn" title="Not implemented"></div></li>'
      + '            <li><div class="online-btn"></div></li>'
      + '          </ul>'
      : '')
      + '        </div>'
      + ''
      + '        <div class="pfc-compose">'
      + '          <textarea data-to="channel|xxx"></textarea>'
      + '        </div>'
      + ''
      + '        <div class="pfc-modal-overlay"></div>'
      + '        <div class="pfc-modal-box"></div>'
      + ''
      + '        <div class="pfc-ad-desktop"></div>'
      + '        <div class="pfc-ad-tablet"></div>'
      + '        <div class="pfc-ad-mobile"></div>'
      + '      </div>'
    );

    // load phpfreechat version and hook it to "powered by" title attribute
    if (pfc.options.show_powered_by) {
      $.ajax({
        type: 'GET',
        url:  pfc.options.packageUrl
      }).done(function (p) {
        // some server force text/plain content-type instead of json
        if (typeof p === 'string') {
          try {
            p = JSON.parse(p); // nedd to parse because content-type can be text/plain on specific servers
          } catch (err) {
          }
        }
        if (p.version) {
          $(pfc.element).find('p.logo a.bt-powered').attr('title', 'version ' + p.version);
        }
      });
    }
    
    // connect the textarea enter key event
    $('.pfc-compose textarea').keydown(function (evt) {
      if (evt.keyCode == 13 && evt.shiftKey === false) {
        pfc.postCommand($('.pfc-compose textarea').val());
        $('.pfc-compose textarea').val('');
        return false;
      }
    });

    // when window is resized,
    // resize the textarea with javascript (because absolute positionning doesn't work on firefox)
    $(window).resize(function () {
      var textarea_border_width = parseInt($('.pfc-compose textarea').css('border-right-width'), 10);
      var textarea_padding = parseInt($('.pfc-compose textarea').css('padding-right'), 10)
                           + parseInt($('.pfc-compose textarea').css('padding-left'), 10);
      $('.pfc-compose textarea').width($('.pfc-compose').innerWidth() - textarea_border_width * 2 - textarea_padding);
    });

    // when window is reloaded or closed
    $(window).unload(function () {
      pfc.notifyThatWindowIsClosed();
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

  /**
   * Function used to display errors list in the overlay popup
   */
  pfc.showErrorsPopup = function (errors) {
    var popup = $('<ul class="pfc-errors"></ul>');
    $.each(errors, function (i, err) {
      popup.append($('<li></li>').html(err));
    });
    pfc.modalbox.open(popup);
  };
  
  /**
   * For mobile ergonomics
   */
  pfc.loadResponsiveBehavior = function () {
    var elt_tabs     = $(".pfc-tabs");
    var elt_users    = $(".pfc-users");
    var elt_messages = $(".pfc-messages");
    var height_slidetabs = elt_tabs.height();
    var width_users      = elt_users.width();
    var tab_slide_status = 0;
    
    
    // monitor mobile/desktop version
    // and switch tabs css class to adapte styles
    var elt_toggle_tabs_btn  = $('a.pfc-toggle-tabs');
    var elt_toggle_users_btn = $('a.pfc-toggle-users');
    $(window).resize(function () {
      if (elt_toggle_tabs_btn.is(':visible')) {
        switchTabsToMobileLook();
        scrollMessagesToBottom();
      } else {
        switchTabsToDesktopLook();
      }
      if (elt_toggle_users_btn.is(':visible')) {
        switchUsersToMobileLook();
        scrollMessagesToBottom();
      } else {
        switchUsersToDesktopLook();
      }
    });

    
    // tabs mobile version
    function switchTabsToMobileLook() {
      elt_tabs.removeClass('pfc-tabs').addClass('pfc-mobile-tabs');
      elt_tabs.hide();
      if (tab_slide_status == 1) {
        slideTabsUp();
        tab_slide_status = 0;
      }
    }

    // tabs desktop version
    function switchTabsToDesktopLook() {
      elt_tabs.addClass('pfc-tabs').removeClass('pfc-mobile-tabs');
      elt_tabs.show();
      if (tab_slide_status == 1) {
        slideTabsUp();
        tab_slide_status = 0;
      }
    }
    
    // move messages/users up and down if needed
    function slideTabsUp(withtabs) {
      if (withtabs) {
        elt_tabs.slideUp(500);
      }
      elt_messages.animate({
        top: "-=" + height_slidetabs
      }, 500);
      elt_users.animate({
        top: "-=" + height_slidetabs
      }, 500);
    }
    function slideTabsDown(withtabs) {
      if (withtabs) {
        elt_tabs.slideDown(500);
      }
      elt_messages.animate({
        top: "+=" + height_slidetabs
      }, 500);
      elt_users.animate({
        top: "+=" + height_slidetabs
      }, 500);
    }
    
    // show/hide channels tabs
    elt_toggle_tabs_btn.click(function () {
      elt_tabs.removeClass('pfc-tabs').addClass('pfc-mobile-tabs');
      height_slidetabs = elt_tabs.height();
      if (elt_tabs.is(":visible")) {
        tab_slide_status = 0;
        slideTabsUp(true);
      } else {
        tab_slide_status = 1;
        slideTabsDown(true);
      }
    });
    
    // show/hide user list
    elt_toggle_users_btn.click(function () {
      if (elt_users.is(":visible")) {
        elt_users.animate({
          width: "-=" + width_users
        }, 500);
        setTimeout(function () {
          elt_users.hide();
        }, 500);
      } else {
        elt_users.css("width", "0px").show();
        elt_users.animate({
          width: "+=" + width_users
        }, 500);
      }
    });

    // users mobile version
    function switchUsersToMobileLook() {
      elt_users.hide();
    }

    // users desktop version
    function switchUsersToDesktopLook() {
      elt_users.css("width", width_users + "px").show();
    }
    
    // function in charge of scrolling messages list to bottom
    function scrollMessagesToBottom() {
      var messages_dom = $(pfc.element).find('.pfc-messages');
      
      // calculate how many to scroll to have a bottom scrollbar
      var messages_height = 0;
      messages_dom.each(function (i, elt) { messages_height += $(elt).height(); });
      
      messages_dom.scrollTop(messages_dom.scrollTop() + messages_height);
    }
    
  };

  /**
   * Load action menu used for interactions with users in the list
   */
  pfc.loadActionMenu = function () {  
    $(".avatar").live("mouseenter",function () {
      var menu = '<div class="actions-menu"><ul class="menu">';
      menu += '<li><a href="#">Give operator rights</a></li>';
      menu += '<li><a href="#">Remove operator rights</a></li>';
      menu += '<li><a href="#">Kick</a></li>';
      menu += '<li><a href="#">Ban</a></li>';
      menu += '</ul></div>';
      $(this).append(menu);
    }).live("mouseleave", function () {
      $(".actions-menu").remove();
    });
  };

  /**
   * Load specific javascript defined by the theme
   */
  pfc.loadThemeUI = function () {
    $('link').each(function (i, link) {
      var href = $(link).attr('href');
      if (new RegExp("\/client\/themes\/").test(href)) {
        var base_url = href.replace(/[^\/]+$/, '');
        var theme_ui_url = base_url + 'theme.js';
        $.ajax({
          url: theme_ui_url,
          dataType: "script",
          cache: true
        });
      }
    });
  };

  return pfc;
}(phpFreeChat || {}, jQuery, window));

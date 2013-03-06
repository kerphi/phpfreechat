/*jslint node: true, maxlen: 150, maxerr: 50, indent: 2 */
'use strict';

/**
 * phpfreechat's core functions
 */
var phpFreeChat = (function (pfc, $, window, undefined) {


  /**
   * Read current user pending messages
   */
  pfc.readPendingMessages = function (loop) {

    // initialize the network error counter
    if (pfc.readPendingMessages.nb_network_error === undefined) {
      pfc.readPendingMessages.nb_network_error = 0;
    }
    
    // send periodicaly AJAX request to check pending messages
    $.ajax({
      type: 'GET',
      url:  pfc.options.serverUrl + '/users/' + pfc.uid + '/pending/'
    }).done(function (msgs) {
      // reset the error counter because a request has been well received
      pfc.readPendingMessages.nb_network_error = 0;

      $.each(msgs, function (i, m) {
        // specific actions for special messages
        if (pfc.commands[m.type] !== undefined) {
          pfc.commands[m.type].receive(m);
        } else {
          pfc.showErrorsPopup([ 'Unknown command ' + m.type ]);          
        }
      });
      if (loop) {
        setTimeout(function () { pfc.readPendingMessages(true) }, pfc.options.refresh_delay);
      }
    }).error(function (err) {
      // check how many network errors has been received and
      // block the automatic refresh if number of allowed errors is exceed
      if (pfc.readPendingMessages.nb_network_error++ > pfc.options.tolerated_network_errors) {
        pfc.showErrorsPopup([ 'Network error. Please reload the chat to continue.' ]);
      } else if (loop) {
        setTimeout(function () { pfc.readPendingMessages(true) }, pfc.options.refresh_delay);
      }
    });

  };

  /**
   * Join a channel
   */
  pfc.join = function (cid) {
    pfc.postCommand('/join "#xxx"');
  };
  
  /**
   * Wrapper for the leave a channel
   */
  pfc.leave = function (cid) {
    pfc.postCommand('/leave "#xxx"');
  };

  /**
   * Post a command to the server
   */
  pfc.postCommand = function (raw_cmd) {

    // do not execute empty command
    if (raw_cmd === '') {
      return false;
    }
    
    try {
      // parse command
      var cmd = pfc.parseCommand(raw_cmd);
      // send the command to the server
      pfc.commands[cmd[0]].send(cmd[1]);
    } catch (err) {
      // caught a command parsing error
      pfc.appendMessage({
        from: 'system-error',
        body: 'Invalid command syntax. Usage:\n' + err[1]
      });
    }

  };
  
  /**
   * Notify phpfreechat server that a windows close event occured
   * Thanks to this notification, server can tell other users that this user just leave the channels
   */
  pfc.notifyThatWindowIsClosed = function () {
    $.ajax({
      type: pfc.options.use_post_wrapper ? 'POST' : 'PUT',
      async: false, // important or this request will be lost when windows is closed
      url:  pfc.options.serverUrl + '/users/' + pfc.uid + '/closed',
      data: pfc.options.use_post_wrapper ? { _METHOD: 'PUT' } : '1'
    }).done(function () {
      //      console.log('notifyThatWindowIsClosed done');
    }).error(function (err) {
      console.log(err);
    });
  };

  /**
   * Appends a username in the user list
   * returns the id of the user's dom element
   */
  pfc.appendUser = function (user) {

    // be tolerent "user" parameter could be a uid
    if (pfc.users[user]) {
      user = pfc.users[user];
    }
    
    // user.role = admin or user
    // user.name = nickname
    // user.email = user email used to calculate gravatar
    // user.active = true if active
    
    // default values
    user.id     = (user.id !== undefined) ? user.id : 0;
    user.op     = ($.inArray(user.id, pfc.channels[pfc.cid].op) >= 0);
    user.role   = user.op ? 'admin' : 'user';
    user.name   = (user.name !== undefined) ? user.name : 'Guest ' + Math.round(Math.random() * 100);
    user.email  = (user.email !== undefined) ? user.email : user.name + '@phpfreechat.net';
    user.active = (user.active !== undefined) ? user.active : true;
    
    // user list DOM element
    var users_dom = $(pfc.element).find(user.role == 'admin' ? 'div.pfc-role-admin' :
                                                               'div.pfc-role-user');

    // create a blank DOM element for the user
    var html = $('              <li class="user">'
               + '                <div class="status"></div>'
               + '                <div class="name"></div>'
               + '                <div class="avatar"></div>'
               + '              </li>');

    // fill the DOM element
    if (user.name) {
      html.find('div.name').text(user.name);
    }
    if (users_dom.find('li').length === 0) {
      html.addClass('first');
    }
    html.find('div.status').addClass(user.active ? 'st-active' : 'st-inactive');

    // operators have a specific icon in the user list
    if (user.op) {
      html.find('div.status').addClass('st-op');
    }
    if (pfc.options.show_avatar) {
      html.find('div.avatar').append('<img src="http://www.gravatar.com/avatar/' + pfc.md5(user.email) + '?d=wavatar&amp;s=30" alt="" />');
    }
    
    // get all userids from the list (could be cached)
    var userids = [];
    $(pfc.element).find('div.pfc-users li.user').each(function (i, dom_user) {
      userids.push(parseInt($(dom_user).attr('id').split('_')[1], 10));
    });
    // if no user id is indicated, generate a new one
    if (user.id === 0) {
      do {
        user.id = Math.round(Math.random() * 10000);
      } while ($.inArray(user.id, userids) !== -1);
    }
    // add the id in the user's dom element
    if (user.id !== 0 && $.inArray(user.id, userids) === -1) {
      html.attr('id', 'user_' + user.id);
    } else {
      return 0;
    }

    // append the user dom element to the interface
    users_dom.find('ul').append(html);
    pfc.updateRolesTitles();

    return user.id;
  };
  
  /**
   * Remove a user from the user list
   * returns true if user has been found, else returns false
   */
  pfc.removeUser = function (uid) {
    var removed = ($(pfc.element).find('#user_' + uid).remove().length > 0);
    pfc.updateRolesTitles();
    return removed;
  }

  /**
   * Hide or show the roles titles
   */
  pfc.updateRolesTitles = function () {
    [ $(pfc.element).find('div.pfc-role-admin'),
      $(pfc.element).find('div.pfc-role-user') ].forEach(function (item, index) {
      if (item.find('li').length === 0) {
        item.find('.role-title').hide();
      } else {
        item.find('.role-title').show();
      }
    });
  }

  /**
   * Clear the user list
   */
  pfc.clearUserList = function () {
    $(pfc.element).find('li.user').remove();
    pfc.updateRolesTitles();
    return true;
  }

  /**
   * Appends a message to the interface
   */
  pfc.appendMessage = function (msg) {

    // default values
    msg.from      = (msg.type == 'msg') ? msg.sender : (msg.from !== undefined ? msg.from : 'system-message');
    msg.name      = (pfc.users[msg.sender] !== undefined) ? pfc.users[msg.sender].name : msg.name;
    msg.body      = (msg.body !== undefined) ? msg.body : '';
    msg.timestamp = (msg.timestamp !== undefined) ? msg.timestamp : Math.round(new Date().getTime() / 1000);
    msg.date      = new Date(msg.timestamp * 1000).toLocaleTimeString();

    msg.avatar    = (pfc.users[msg.sender] !== undefined) ?
      (pfc.users[msg.sender].email ?
        pfc.md5(pfc.users[msg.sender].email)
        : pfc.md5(pfc.users[msg.sender].name + '@phpfreechat.net'))
      : '';
    
    // reformat body text
    if (msg.type == 'join') {
      msg.body = msg.name + ' joined the channel';
    } else if (msg.type == 'leave') {
      msg.body = msg.name + ' left the channel' + (msg.body ? ' (' + msg.body + ')' : '');
    }

    var groupmsg_dom = $(pfc.element).find('.pfc-messages .messages-group:last');
    var messages_dom = $(pfc.element).find('.pfc-messages');
    var html         = null;
    if (groupmsg_dom.attr('data-from') != msg.from) {
      html = $('<div class="messages-group" data-stamp="" data-from="">'
        + (pfc.options.show_avatar ?
          '       <div class="avatar"><img src="http://www.gravatar.com/avatar/' + msg.avatar + '?d=wavatar&s=30" alt="" /></div>' :
          '')
        + '       <div class="date"></div>'
        + '       <div class="name"></div>'
        + '     </div>');

      // system messages (join, error ...)
      if (/^system-/.test(msg.from)) {
        html.addClass('from-' + msg.from);
        html.find('.name').remove();
        html.find('.avatar').remove();
      }
      
      // fill the html fragment
      html.find('.name').text(msg.name);
      html.attr('data-from', msg.from);
      html.find('.date').text(msg.date);
      html.attr('data-stamp', msg.timestamp);
        
      // add a new message group
      messages_dom.append(html);
      groupmsg_dom = html;
    }

    // add the message to the latest active message group
    msg.body = $('<pre></pre>').text(msg.body).html();
    var message = $('<div class="message"></div>').html(msg.body);
    groupmsg_dom.append(message);

    // scroll when a message is received
    if (groupmsg_dom == html) {
      messages_dom.scrollTop(messages_dom.scrollTop() + groupmsg_dom.outerHeight() + 10);
    } else {
      messages_dom.scrollTop(messages_dom.scrollTop() + message.outerHeight());
    }
    
    return message;
  };
  
  /**
   * Setup topic text
   */
  pfc.setTopic = function (topic) {
    $(pfc.element).find('.pfc-topic-value').text(topic);
  };

  /**
   * Shows a popup to ask for help with a donation
   */
  pfc.showDonationPopup = function (next) {
    
    // force skip by a parameter ?
    if (pfc.options.skip_intro) {
      next();
      return;
    }

    // check if skip intro checkbox has been set or not last time
    $.ajax({
      type: 'GET',
      url:  pfc.options.serverUrl + '/skipintro'
    }).complete(function (jqXHR) {
      if (jqXHR.status != 200) {
        buildAndShowDonationPopup();
      } else {
        next();
      }
    });

    function buildAndShowDonationPopup() {
      // html of the popup
      var box = pfc.modalbox.open(
          '<form class="popup-donate">'
        + '  <p>phpFreeChat is an adventure we have been sharing altogether since 2006.'
        + '     If this chat is a so successfull, with hundreds of daily downloads,'
        + '     it is thanks to those who have been helping the project financially.'
        + '     Keep making this adventure possible, make a donation. Thank you.'
        + '  </p>'
        + '  <div class="bt-validate">'
        + '    <input type="submit" name="cancel-donate" value="not now" />'
        + '    <input type="submit" name="ok-donate" value="DONATE" />'
        + '  </div>'
        + '  <span><label><input type="checkbox" name="skip-donate" /> skip next time</label></span>'
        + '</form>'
      );
      
      // default focus to donate button
      box.find('input[name=ok-donate]').focus();

      // press ESC to hide donate popup
      var esc_key_action = function (event) {
        if ( event.which == 27 ) {
          pfc.modalbox.close(true);
          $(document).off('keyup', esc_key_action);  // removes escape key event handler
          next();
        }
      };
      $(document).on('keyup', esc_key_action); 
      

      // donate or cancel button clicked
      box.find('input[type=submit]').click(function () {
        // donate button clicked
        if ($(this).attr('name') == 'ok-donate') {
          window.open('http://www.phpfreechat.net/donate', 'pfc-donate'); //,'width=400,height=200');
        }
        // skip intro button clicked
        if (box.find('input[name=skip-donate]').attr('checked')) {
          $.ajax({
            type: pfc.options.use_post_wrapper ? 'POST' : 'PUT',
            url:  pfc.options.serverUrl + '/skipintro',
            data: pfc.options.use_post_wrapper ? { _METHOD: 'PUT' } : 1
          }).done(function (res) {
          }).error(function (err) {
          });
        }
        pfc.modalbox.close(true);
        $(document).off('keyup', esc_key_action); // removes escape key event handler
        next();
      });
      
      // disable submit button action
      box.submit(function (evt) {
        evt.preventDefault();
      });
    }
    
  };

  /**
   * Displays an error
   * first parameter is the err object returned by the AJAX request
   */
  pfc.displayError = function (err) {
    
    // format the error (generic or specific)
    if (err.responseText) {
      err = JSON.parse(err.responseText);
    } else {
      err = {
        'error':      err.statusText,
        'errorCode' : err.status
      };
    }
    
    // display the error
    switch (err.errorCode) {
      
      case 40305:
        err.baninfo.timestamp = new Date(err.baninfo.timestamp * 1000);
        pfc.appendMessage({
          type: 'error',
          body: 'You cannot join this channel because you have been banned by ' + err.baninfo.opname +
                ' for ' + (err.baninfo.reason ? 'the reason "' + err.baninfo.reason + '"' : 'no reason') +
                ' on ' + err.baninfo.timestamp
        });
        break;

      default:
        // generic error
        pfc.appendMessage({
          type: 'error',
          body: err.error + ' [' + err.errorCode + ']'
        });
        break;
    }
  };
 
  return pfc;
}(phpFreeChat || {}, jQuery, window));
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
    
    $.ajax({
      type: 'GET',
      url:  pfc.options.serverUrl + '/users/' + pfc.uid + '/msg/',
    }).done(function (msgs) {

      msgs.forEach(function (m, i) {
        if (m.type == 'msg') {
          pfc.appendMessage(m);
        } else if (m.type == 'join') {
          pfc.users[m.sender] = m.body; // store new joined user data
          m.body = pfc.users[m.sender].name + ' joined the channel';
          pfc.appendMessage(m); // post the message
          pfc.appendUser(pfc.users[m.sender]); // append the user to the list
        } else if (m.type == 'leave') {
          m.body = pfc.users[m.sender].name + ' leave the channel (' + m.body + ')';
          pfc.appendMessage(m); // post the message
          pfc.removeUser(m.sender); // remove the user from the list
        } else {
          console.log('not implemented message type');
        }
      });
      if (loop) {
        setTimeout(function () { pfc.readPendingMessages(true) }, pfc.options.refresh_delay);
      }
    }).error(function (err) {
      console.log(err);
      if (loop) {
        setTimeout(function () { pfc.readPendingMessages(true) }, pfc.options.refresh_delay);
      }
    });

  };

  /**
   * Join a channel
   */
  pfc.join = function (cid) {

    $.ajax({
      type: 'PUT',
      url:  pfc.options.serverUrl + '/channels/' + cid + '/users/' + pfc.uid,
    }).done(function (users) {
      
      // store userdata in the cache
      // refresh the interface
      pfc.clearUserList();
      Object.keys(users).forEach(function (uid) {
        pfc.users[uid] = users[uid];
        pfc.appendUser(users[uid]);
      });

      // display a join message for him
      pfc.appendMessage({
        type: 'join',
        sender: pfc.uid,
        body: 'you joined the channel',
      });
      
      // start to read pending messages
      pfc.readPendingMessages(true); // true = loop

    }).error(function (err) {
      console.log(err);
    });
    

  };
  

  /**
   * Post a message to a channel
   */
  pfc.postToChannel = function (cid, msg) {

    $.ajax({
      type: 'POST',
      url:  pfc.options.serverUrl + '/channels/' + cid + '/msg/',
      contentType: 'application/json; charset=utf-8',
      data: JSON.stringify({ body: msg }),
    }).done(function (msg) {
      pfc.appendMessage(msg);
    }).error(function (err) {
      console.log(err);
    });

  };


  /**
   * Appends a username in the user list
   * returns the id of the user's dom element
   */
  pfc.appendUser = function (user) {

    // user.role = admin or user
    // user.name = nickname
    // user.email = user email used to calculate gravatar
    // user.active = true if active
    
    // default values
    user.id     = (user.id !== undefined) ? user.id : 0;
    user.role   = (user.role !== undefined) ? user.role : 'user';
    user.name   = (user.name !== undefined) ? user.name : 'Guest ' + Math.round(Math.random() * 100);
    user.email  = (user.email !== undefined) ? user.email : '';
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
    //html.find('div.avatar').append('<img src="http://www.gravatar.com/avatar/' + pfc.md5(user.email) + '?d=wavatar&amp;s=20" alt="" />');

    // get all userids from the list (could be cached)
    var userids = [];
    $(pfc.element).find('div.pfc-users li.user').each(function (i, dom_user) {
      userids.push(parseInt($(dom_user).attr('id').split('_')[1], 10));
    });
    // if no user id is indicated, generate a new one
    if (user.id === 0) {
      do {
        user.id = Math.round(Math.random() * 10000);
      } while (userids.indexOf(user.id) != -1);
    }
    // add the id in the user's dom element
    if (user.id !== 0 && userids.indexOf(user.id) == -1) {
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
    // do not show/hide role titles because not planned for 2.0
    return;
/*
    [ $(pfc.element).find('div.pfc-role-admin'),
      $(pfc.element).find('div.pfc-role-user') ].forEach(function (item, index) {
      if (item.find('li').length === 0) {
        item.find('.role-title').hide();
      } else {
        item.find('.role-title').show();
      }
    });*/
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
    msg.from      = (msg.type == 'msg') ? msg.sender : 'system';
    msg.name      = (pfc.users[msg.sender] !== undefined) ? pfc.users[msg.sender].name : '???';
    msg.body      = (msg.body !== undefined) ? msg.body : '';
    msg.timestamp = (msg.timestamp !== undefined) ? msg.timestamp : Math.round(new Date().getTime() / 1000);
    msg.date      = new Date(msg.timestamp * 1000).toLocaleTimeString();
    
    var groupmsg_dom = $(pfc.element).find('.pfc-messages .messages-group:last');
    var groupmsg_last_dom = groupmsg_dom;
    var messages_dom = $(pfc.element).find('.pfc-messages');
    
    if (groupmsg_dom.attr('data-from') != msg.from) {
      var html = $('<div class="messages-group" data-stamp="" data-from="">'
//      + '            <div class="avatar"><img src="http://www.gravatar.com/avatar/00000000000000000000000000000001?d=wavatar&s=30" alt="" /></div>'
//      + '            <div class="avatar"><div style="width:30px; height: 30px; background-color: #DDD;"></div></div>'
        + '            <div class="date"></div>'
        + '            <div class="name"></div>'
        + '          </div>');
      
      // system messages (join)
      if (msg.from == 'system') {
        html.addClass('system-message');
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

    // scroll to the last message and memorize the scroll position
    var scroll_pos = 0;
    if (groupmsg_last_dom.length === 0) {
      scroll_pos = groupmsg_dom.outerHeight();
    } else {
      scroll_pos = groupmsg_dom.outerHeight() + groupmsg_last_dom.data('scroll-pos');
    }
    messages_dom.animate({scrollTop: scroll_pos});
    groupmsg_dom.data('scroll-pos', scroll_pos);
    
    return message;
  }



  return pfc;
}(phpFreeChat || {}, jQuery, window));
/*jslint node: true, maxlen: 150, maxerr: 50, indent: 2 */
'use strict';

/**
 * phpfreechat's ban commands
 */
var phpFreeChat = (function (pfc, $, window, undefined) {

  pfc.commands = $.extend({}, pfc.commands);

  /**
   * ban command
   */
  pfc.commands.ban = {
    usage:      '/ban "<username>" ["reason"]',
    longusage:  '/ban ["#<channel>"] "<username>" ["reason"]',
    regexp:     [
      /^"([^#][^"]*?)"$/,
      /^"([^#][^"]*?)" +"([^"]+?)"$/,
      /^"#([^"]+?)" +"([^"]+?)"$/,      
      /^"#([^"]+?)" +"([^"]+?)" +"([^"]+?)"$/
    ],
    regexp_ids: [
      { 1: 'username' },
      { 1: 'username', 2: 'reason' },
      { 1: 'channel', 2: 'username' },
      { 1: 'channel', 2: 'username', 3: 'reason' }
    ],
    
    send: function (cmd_arg) {
      var name64 = pfc.base64.encode(cmd_arg.username);
      $.ajax({
        type: pfc.options.use_post_wrapper ? 'POST' : 'PUT',
        url:  pfc.options.serverUrl + '/channels/' + cmd_arg.cid + '/ban/' + name64,
        data: pfc.options.use_post_wrapper ? { _METHOD: 'PUT', reason: cmd_arg.reason } : { reason: cmd_arg.reason }
      }).done(function () {
        
        pfc.commands.ban.receive({
          type: 'ban',
          sender: pfc.uid,
          body: { opname: pfc.users[pfc.uid].name, name: cmd_arg.username, reason: cmd_arg.reason, kickban: false },
          recipient: 'channel|' + cmd_arg.cid
        });
        
      }).error(function (err) {
        console.log(err);
      });
    },

    receive: function (msg) {
      var cid = msg.recipient.split('|')[1];

      if (pfc.users[pfc.uid].name == msg.body.name) {
        // someone banned me
        
        // if i was also kicked from the channel
        if (msg.body.kickban) {
          pfc.clearUserList();
          // todo: close the tab & update the current channel         
        }

        // post a message
        msg.body = 'You were ' + (msg.body.kickban ? 'kick' : '') + 'banned by ' + msg.body.opname +
                   ' from #'  + pfc.getNameFromCid(cid) +
                   ' for ' + (msg.body.reason ? 'the reason "' + msg.body.reason + '"' : 'no reason');
        pfc.appendMessage(msg);

      } else {
        // someone banned someone (not me)

        // if the user was also kicked from the channel
        if (msg.body.kickban) {
          // update the channel operator list structure
          pfc.removeUidFromCid(pfc.getUidFromName(msg.body.name), cid);
          // update the users list interface
          pfc.removeUser(pfc.getUidFromName(msg.body.name));
        }
        
        // post a message
        msg.body = msg.body.name + ' was ' + (msg.body.kickban ? 'kick' : '') + 'banned by ' + msg.body.opname +
                   ' from this channel' +
                   ' for ' + (msg.body.reason ? 'the reason "' + msg.body.reason + '"' : 'no reason');
        pfc.appendMessage(msg);
        
      }
    }
  };

  /**
   * kickban command
   */
  pfc.commands.kickban = {
    usage:      '/kickban "<username>" ["reason"]',
    longusage:  '/kickban ["#<channel>"] "<username>" ["reason"]',
    regexp:     [
      /^"([^#][^"]*?)"$/,
      /^"([^#][^"]*?)" +"([^"]+?)"$/,
      /^"#([^"]+?)" +"([^"]+?)"$/,      
      /^"#([^"]+?)" +"([^"]+?)" +"([^"]+?)"$/
    ],
    regexp_ids: [
      { 1: 'username' },
      { 1: 'username', 2: 'reason' },
      { 1: 'channel', 2: 'username' },
      { 1: 'channel', 2: 'username', 3: 'reason' }
    ],
    
    send: function (cmd_arg) {
      var name64 = pfc.base64.encode(cmd_arg.username);
      $.ajax({
        type: pfc.options.use_post_wrapper ? 'POST' : 'PUT',
        url:  pfc.options.serverUrl + '/channels/' + cmd_arg.cid + '/ban/' + name64,
        data: pfc.options.use_post_wrapper ? { _METHOD: 'PUT', reason: cmd_arg.reason, kickban: true } : { reason: cmd_arg.reason }
      }).done(function () {
        
        pfc.commands.ban.receive({
          type: 'ban',
          sender: pfc.uid,
          body: { opname: pfc.users[pfc.uid].name, name: cmd_arg.username, reason: cmd_arg.reason, kickban: true },
          recipient: 'channel|' + cmd_arg.cid
        });
        
      }).error(function (err) {
        console.log(err);
      });
    },

    receive: pfc.commands.ban.receive
  };

  /**
   * unban command
   */
  pfc.commands.unban = {
    usage:      '/unban "<username>"',
    longusage:  '/unban ["#<channel>"] "<username>"',
    regexp:     [
      /^"([^#][^"]*?)"$/,
      /^"#([^"]+?)" +"([^"]+?)"$/
    ],
    regexp_ids: [
      { 1: 'username' },
      { 1: 'channel', 2: 'username' }
    ],
    
    send: function (cmd_arg) {
      var name64 = pfc.base64.encode(cmd_arg.username);
      $.ajax({
        type: pfc.options.use_post_wrapper ? 'POST' : 'DELETE',
        url:  pfc.options.serverUrl + '/channels/' + cmd_arg.cid + '/ban/' + name64,
        data: pfc.options.use_post_wrapper ? { _METHOD: 'DELETE' } : { }
      }).done(function () {
        
        pfc.commands.unban.receive({
          type: 'unban',
          sender: pfc.uid,
          body: { opname: pfc.users[pfc.uid].name, name: cmd_arg.username },
          recipient: 'channel|' + cmd_arg.cid
        });
        
      }).error(function (err) {
        console.log(err);
      });
    },

    receive: function (msg) {
      var cid    = msg.recipient.split('|')[1];
      
      // todo: post the message only on the concerned channel
      // post a message
      msg.body = msg.body.name + ' was unbanned by ' + msg.body.opname +
                  ' from #'  + pfc.getNameFromCid(cid);
      pfc.appendMessage(msg);
    }
  };

  /**
   * banlist command
   */
  pfc.commands.banlist = {
    usage:      '/banlist',
    longusage:  '/banlist ["#<channel>"]',
    regexp:     [
      /^$/,
      /^"#([^"]+?)"$/
    ],
    regexp_ids: [
      { },
      { 1: 'channel' }
    ],
    
    send: function (cmd_arg) {
      $.ajax({
        type: 'GET',
        url:  pfc.options.serverUrl + '/channels/' + cmd_arg.cid + '/ban/'
      }).done(function (banlist) {
        
        pfc.commands.banlist.receive({
          type: 'banlist',
          sender: pfc.uid,
          body: banlist,
          recipient: 'channel|' + cmd_arg.cid
        });
        
      }).error(function (err) {
        console.log(err);
      });
    },

    receive: function (msg) {
      var cid    = msg.recipient.split('|')[1];

      var banlist_txt = [];
      $.each(msg.body, function (key, value) {
        value.timestamp = new Date(value.timestamp * 1000);
        banlist_txt.push(
          key + ' (banned by ' + value.opname +
          ' for ' + (value.reason ? 'the reason "' + value.reason + '"' : 'no reason') +
          ' on ' + value.timestamp +
          ')');
      });
      if (banlist_txt.length > 0) {
        msg.body = 'Banished list on #' + pfc.getNameFromCid(cid) + '\n  - ' + banlist_txt.join('\n  - ');
      } else {
        msg.body = 'Empty banished list on  #' + pfc.getNameFromCid(cid);
      }
      pfc.appendMessage(msg);      
    }
  };

  return pfc;
}(phpFreeChat || {}, jQuery, window));
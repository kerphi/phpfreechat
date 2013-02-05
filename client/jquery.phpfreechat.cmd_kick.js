/*jslint node: true, maxlen: 150, maxerr: 50, indent: 2 */
'use strict';

/**
 * phpfreechat's kick command
 */
var phpFreeChat = (function (pfc, $, window, undefined) {

  pfc.commands = $.extend({}, pfc.commands);

  /**
   * kick command
   */
  pfc.commands.kick = {
    usage:      '/kick "<username>" ["reason"]',
    longusage:  '/kick ["#<channel>"] "<username>" ["reason"]',
    regexp:     [
      /^"([^#][^"]*?)"$/,
      /^"([^#][^"]*?)" +"([^"]+?)"$/,
      /^"#([^"]+?)" +"([^"]+?)"$/,      
      /^"#([^"]+?)" +"([^"]+?)" +"([^"]+?)"$/,      
    ],
    regexp_ids: [
      { 1: 'username' },
      { 1: 'username', 2: 'reason' },
      { 1: 'channel', 2: 'username' },
      { 1: 'channel', 2: 'username', 3: 'reason' }
    ],
    
    send: function (cmd_arg) {
      var uid = pfc.getUidFromName(cmd_arg.username);
      $.ajax({
        type: pfc.options.use_post_wrapper ? 'POST' : 'DELETE',
        url:  pfc.options.serverUrl + '/channels/' + cmd_arg.cid + '/users/' + uid,
        data: pfc.options.use_post_wrapper ? { _METHOD: 'DELETE', reason: cmd_arg.reason } : { reason: cmd_arg.reason }
      }).done(function () {
        
        pfc.commands.kick.receive({
          type: 'kick',
          sender: pfc.uid,
          body: { target: uid, reason: cmd_arg.reason },
          recipient: 'channel|' + cmd_arg.cid
        });
        
      }).error(function (err) {
        console.log(err);
      });
    },

    receive: function (msg) {
      var cid    = msg.recipient.split('|')[1];
      var kicker = pfc.users[msg.sender];
      var kicked = pfc.users[msg.body.target];

      if (pfc.uid == kicked.id) {
        pfc.clearUserList();

        // append message to the list
        msg.body = kicker.name + ' kicked you from ' + pfc.getNameFromCid(cid) + (msg.body.reason ? (' [ reason: ' + msg.body.reason + ']') : '');
        pfc.appendMessage(msg);
        
        // todo: close the tab
      } else {
        // update the channel operator list structure
        pfc.removeUidFromCid(kicked.id, cid);

        // append message to the list
        msg.body = kicker.name + ' kicked ' + kicked.name + (msg.body.reason ? (' [ reason: ' + msg.body.reason + ']') : '');
        pfc.appendMessage(msg);
        
        // update the users list interface
        pfc.removeUser(kicked.id);
      }
    }
  };
    
  return pfc;
}(phpFreeChat || {}, jQuery, window));
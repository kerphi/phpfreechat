/*jslint node: true, maxlen: 150, maxerr: 50, indent: 2 */
'use strict';

/**
 * phpfreechat's op/deop commands
 */
var phpFreeChat = (function (pfc, $, window, undefined) {

  pfc.commands = $.extend({}, pfc.commands);

  /**
   * op command
   */
  pfc.commands.op = {
    help:       'gives operator rights to a user on a channel',
    usage:      '/op "<username>"',
    longusage:  '/op ["#<channel>"] "<username>"',
    params:     [ 'channel', 'username' ],
    regexp:     [ /^("#(.+?)" |)"(.+?)"$/ ],
    regexp_ids: [ { 2: 'channel', 3: 'username' } ],
    send: function (cmd_arg) {
      var uid = pfc.getUidFromName(cmd_arg.username);
      $.ajax({
        type: pfc.options.use_post_wrapper ? 'POST' : 'PUT',
        url:  pfc.options.serverUrl + '/channels/' + cmd_arg.cid + '/op/' + uid,
        data: pfc.options.use_post_wrapper ? { _METHOD: 'PUT' } : null
      }).done(function (op_info) {
        //console.log(op_info);
        pfc.commands.op.receive({
          type: 'op',
          sender: pfc.uid,
          body: uid,
          recipient: 'channel|' + cmd_arg.cid
        });
      }).error(function (err) {
        console.log(err);
      });
    },
    receive: function (msg) {
      var cid    = msg.recipient.split('|')[1];
      var op     = pfc.users[msg.sender];
      var op_dst = pfc.users[msg.body];

      // update the channel operator list structure
      pfc.addUidToCidOp(op_dst.id, cid);

      // append message to the list
      msg.body = op.name + ' gave operator rights to ' + op_dst.name;
      pfc.appendMessage(msg);
      
      // update the users list interface
      pfc.removeUser(op_dst.id);
      pfc.appendUser(op_dst.id);
    }
  };
  

  /**
   * deop command
   */
  pfc.commands.deop = {
    help:       'removes operator rights to a user on a channel',
    usage:      '/deop "<username>"',
    longusage:  '/deop ["#<channel>"] "<username>"',
    params:     [ 'channel', 'username' ],
    regexp:     [ /^("#(.+?)" |)"(.+?)"$/ ],
    regexp_ids: [ { 2: 'channel', 3: 'username' } ],
    send: function (cmd_arg) {
      var uid = pfc.getUidFromName(cmd_arg.username);
      $.ajax({
        type: pfc.options.use_post_wrapper ? 'POST' : 'DELETE',
        url:  pfc.options.serverUrl + '/channels/' + cmd_arg.cid + '/op/' + uid,
        data: pfc.options.use_post_wrapper ? { _METHOD: 'DELETE' } : null
      }).done(function (op_info) {
        //console.log(op_info);
        pfc.commands.deop.receive({
          type: 'deop',
          sender: pfc.uid,
          body: uid,
          recipient: 'channel|' + cmd_arg.cid
        });
      }).error(function (err) {
        console.log(err);
      });
    },
    receive: function (msg) {
      var cid = msg.recipient.split('|')[1];
      var deop     = pfc.users[msg.sender];
      var deop_dst = pfc.users[msg.body];

      // update the channel operator list structure
      pfc.removeUidFromCidOp(deop_dst.id, cid);

      // append message to the list
      msg.body = deop.name + ' removed operator rights to ' + deop_dst.name;
      pfc.appendMessage(msg);

      // update the users list
      pfc.removeUser(deop_dst.id);
      pfc.appendUser(deop_dst.id);
    }
  };
  
  return pfc;
}(phpFreeChat || {}, jQuery, window));
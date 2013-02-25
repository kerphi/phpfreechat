/*jslint node: true, maxlen: 150, maxerr: 50, indent: 2 */
'use strict';

/**
 * phpfreechat's join/leave commands
 */
var phpFreeChat = (function (pfc, $, window, undefined) {

  pfc.commands = $.extend({}, pfc.commands);

  /**
   * join command
   */
  pfc.commands.join = {
    help:       '',
    usage:      '/join "#<channel>"',
    longusage:  '/join "#<channel>"',
    regexp:     [ /^"#([^"]+?)"$/ ],
    regexp_ids: [ { 1: 'channel' } ],
    
    send: function (cmd_arg) {
      
      // todo : POST to /channels/ route to require a cid for the channel name (cmd_arg.channel)
      cmd_arg.cid = "xxx";
      
      //console.log(cmd_arg);
      $.ajax({
        type: pfc.options.use_post_wrapper ? 'POST' : 'PUT',
        url:  pfc.options.serverUrl + '/channels/' + cmd_arg.cid + '/users/' + pfc.uid,
        data: pfc.options.use_post_wrapper ? { _METHOD: 'PUT' } : null
      }).done(function (cinfo) {
        
        pfc.channels[cmd_arg.cid] = {
          name: cmd_arg.cid,
          users: [],
          op: []
        };
        
        // store channel operators
        pfc.channels[cmd_arg.cid].op = cinfo.op;
        
        // store userdata in the cache
        // refresh the interface
        pfc.clearUserList();
        $.each(cinfo.users, function (uid, udata) {
          pfc.addUidToCid(uid, cmd_arg.cid);
          
          pfc.users[uid] = udata;
          pfc.appendUser(udata);
        });

        // display a join message for himself
        pfc.appendMessage({
          type: 'join',
          sender: pfc.uid,
          body: 'you joined the channel'
        });

      }).error(function (err) {
        pfc.displayError(err);
      });

    },
    
    receive: function (msg) {
      var cid = msg.recipient.split('|')[1];

      // store new user in the channels structure
      pfc.addUidToCid(msg.sender, cid);

      // update the channel operator list structure
      if (msg.body.op) {
        pfc.addUidToCidOp(msg.sender, cid);
      }
      
      // store new joined user data
      pfc.users[msg.sender] = msg.body.userdata;
      
      // append the user to the list
      pfc.appendUser(pfc.users[msg.sender]); 
      
      // display the join message
      pfc.appendMessage(msg);
    }
  };
  
  /**
   * leave command
   */
  pfc.commands.leave = {
    help:       '',
    usage:      '/leave ["#<channel>"]',
    longusage:  '/leave ["#<channel>"] ["reason"]',
    regexp:     [
      /^"#([^"]+?)" "([^"]+?)"$/,
      /^"#([^"]+?)"$/,
      /^"([^"]+?)"$/,
      /^$/
    ],
    regexp_ids: [
      { 1: 'channel', 2: 'reason' },
      { 1: 'channel' },
      { 1: 'reason' },
      { }
    ],
    
    send: function (cmd_arg) {
      //cid, command, channel, reason
      
      $.ajax({
        type: pfc.options.use_post_wrapper ? 'POST' : 'DELETE',
        url:  pfc.options.serverUrl + '/channels/' + cmd_arg.cid + '/users/' + pfc.uid,
        data: pfc.options.use_post_wrapper ? { _METHOD: 'DELETE' } : null
      }).done(function () {
        pfc.clearUserList();
        
        // display a leave message for himself
        pfc.appendMessage({
          type: 'leave',
          sender: pfc.uid
        });
        
        // todo: close the tab
        
      }).error(function (err) {
        pfc.displayError(err);
      });
      
    },
    
    receive: function (msg) {
      var cid = msg.recipient.split('|')[1];

      pfc.removeUidFromCid(msg.sender, cid);
      pfc.removeUser(msg.sender);
      pfc.appendMessage(msg);
    }
  };

    
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
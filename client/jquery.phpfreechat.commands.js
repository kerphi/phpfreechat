/*jslint node: true, maxlen: 150, maxerr: 50, indent: 2 */
'use strict';

/**
 * phpfreechat's commands related functions
 */
var phpFreeChat = (function (pfc, $, window, undefined) {

  // phpfreechat commands list
  pfc.commands = {
    msg:  {
      usage:      '/msg "<message>"',
      longusage:  '/msg ["#<channel>"] "<message>"',
      params:     [ 'channel', 'message' ],
      regexp:     /^("#(.+?)" |)"(.+?)"$/,
      regexp_ids: [ 2, 3 ],
      send: function (cid, command, message) {
        // post the command to the server
        $.ajax({
          type: 'POST',
          url:  pfc.options.serverUrl + '/channels/' + cid + '/msg/',
          contentType: 'application/json; charset=utf-8',
          data: JSON.stringify(message)
        }).done(function (msg) {
          pfc.commands.msg.receive(msg);
        }).error(function (err) {
          console.log(err);
        });
      },
      receive: function (msg) {
        // display the message on the chat interface
        pfc.appendMessage(msg);
      }     
    },
    
    leave: {
      help:       '',
      usage:      '/leave ["#<channel>"]',
      longusage:  '/leave ["#<channel>"] ["reason"]',
      params:     [ 'channel', 'reason' ],
      regexp:     /^("#(.+?)"|)( "(.+?)"|)$/,
      regexp_ids: [ 2, 3 ],
      send: function (cid, command, channel, reason) {
      },
      receive: function (msg) {
      }
    },
    
    kick: {
      usage:      '/kick "<username>" ["reason"]',
      longusage:  '/kick ["#<channel>"] "<username>" ["reason"]',
      params:     [ 'channel', 'username', 'reason' ],
      regexp:     /^("#(.+?)" |)"(.+?)"( "(.+?)"|)$/,
      regexp_ids: [ 2, 3, 5 ]
    },
    
    op: {
      help:       'gives operator rights to a user on a channel',
      usage:      '/op "<username>"',
      longusage:  '/op ["#<channel>"] "<username>"',
      params:     [ 'channel', 'username' ],
      regexp:     /^("#(.+?)" |)"(.+?)"$/,
      regexp_ids: [ 2, 3 ],
      send: function (cid, command, username) {
        var uid = pfc.getUidFromName(username);
        $.ajax({
          type: pfc.options.use_post_wrapper ? 'POST' : 'PUT',
          url:  pfc.options.serverUrl + '/channels/' + cid + '/op/' + uid,
          data: pfc.options.use_post_wrapper ? { _METHOD: 'PUT' } : null
        }).done(function (op_info) {
          //console.log(op_info);
          pfc.commands.op.receive({
            type: 'op',
            sender: pfc.uid,
            body: uid,
            recipient: 'channel|' + cid
          });
        }).error(function (err) {
          console.log(err);
        });
      },
      receive: function (msg) {
        // append message to the list
        var op     = pfc.users[msg.sender];
        var op_dst = pfc.users[msg.body];
        msg.body = op.name + ' gave operator rights to ' + op_dst.name;
        pfc.appendMessage(msg);

        // update the channel operator list
        var cid = msg.recipient.split('|')[1];
        pfc.channels[cid].op.push(op_dst.id);
        pfc.removeUser(op_dst.id);
        pfc.appendUser(op_dst.id);
      }
    },
    
    deop: {
      help:       'removes operator rights to a user on a channel',
      usage:      '/deop "<username>"',
      longusage:  '/deop ["#<channel>"] "<username>"',
      params:     [ 'channel', 'username' ],
      regexp:     /^("#(.+?)" |)"(.+?)"$/,
      regexp_ids: [ 2, 3 ],
      send: function (cid, command, username) {
        var uid = pfc.getUidFromName(username);
        $.ajax({
          type: pfc.options.use_post_wrapper ? 'POST' : 'DELETE',
          url:  pfc.options.serverUrl + '/channels/' + cid + '/op/' + uid,
          data: pfc.options.use_post_wrapper ? { _METHOD: 'DELETE' } : null
        }).done(function (op_info) {
          //console.log(op_info);
          pfc.commands.deop.receive({
            type: 'deop',
            sender: pfc.uid,
            body: uid,
            recipient: 'channel|' + cid
          });
        }).error(function (err) {
          console.log(err);
        });
      },
      receive: function (msg) {
        // append message to the list
        var deop     = pfc.users[msg.sender];
        var deop_dst = pfc.users[msg.body];
        msg.body = deop.name + ' removed operator rights to ' + deop_dst.name;
        pfc.appendMessage(msg);

        // update the channel operator list
        var cid = msg.recipient.split('|')[1];
        var idx = pfc.channels[cid].op.indexOf(deop_dst.id);
        if (idx !== -1) pfc.channels[cid].op.splice(idx, 1);
        pfc.removeUser(deop_dst.id);
        pfc.appendUser(deop_dst.id);
      }
    }
    
    
    
  };

  /**
   * Parse the sent message
   * Try to extract explicit commands from it
   * Returns: [ <cid>, <cmd>, <cmd-param1>, <cmd-param2>, ... ] 
   */
  pfc.parseCommand = function (raw) {
    
    var cmd     = '';
    var cmd_arg = [];
    
    // test each commands on the raw message
    $.each(pfc.commands, function (c) {
      // first of all, try to reconize a /<command> pattern
      if (new RegExp('^\/' + c + '( |$)').test(raw)) {
        cmd = c;
        // parse the rest of the command line (the end)
        var raw_end = new RegExp('^\/' + c + ' *(.*)$').exec(raw)[1];
        var cmd_arg_tmp = pfc.commands[c].regexp.exec(raw_end);
        if (cmd_arg_tmp && cmd_arg_tmp.length > 0) {
          // collect interesting values from the regexp result
          cmd_arg = [];
          $.each(pfc.commands[c].regexp_ids, function (i, id) {
            cmd_arg.push(cmd_arg_tmp[id]);
          });
        }
      }
    });
    
    // if no /<command> pattern found, considere it's a /msg command
    if (cmd === '') {
      cmd     = 'msg';
      cmd_arg = [pfc.cid, raw];
    }
    
    // return an error if the command parameters do not match
    if (cmd_arg.length === 0) {
      throw [ cmd, pfc.commands[cmd].usage ];
    }
    
    // optionaly fill channel value if user didn't indicate it
    var channel_idx = $.inArray('channel', pfc.commands[cmd].params);
    if (channel_idx >= 0) {
      if (cmd_arg[channel_idx] === undefined) {
        // no channel has been indicated, we have to used the current one
        cmd_arg[channel_idx] = pfc.cid;
      } else {
        // one channel has been indicated, we have to translate the channel name to the corresponding cid
        // todo: translate the channel name to the corresponding cid
      }
    }

    return [ cmd_arg[0], cmd ].concat(cmd_arg.slice(1));
  };
  
  return pfc;
}(phpFreeChat || {}, jQuery, window));
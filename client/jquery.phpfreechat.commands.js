/*jslint node: true, maxlen: 150, maxerr: 50, indent: 2 */
'use strict';

/**
 * phpfreechat's commands related functions
 */
var phpFreeChat = (function (pfc, $, window, undefined) {

  // phpfreechat commands list
  pfc.commands = {
    
    /**
     * msg command
     */
    msg:  {
      usage:      '/msg "<message>"',
      longusage:  '/msg ["#<channel>"] "<message>"',
      regexp:     [ /^("#(.+?)" |)"(.+?)"$/ ],
      regexp_ids: [ { 2: 'channel', 3: 'message' } ],
      send: function (cmd_arg) {
        // post the command to the server
        $.ajax({
          type: 'POST',
          url:  pfc.options.serverUrl + '/channels/' + cmd_arg.cid + '/msg/',
          contentType: 'application/json; charset=utf-8',
          data: JSON.stringify(cmd_arg.message)
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

    /**
     * join command
     */
    join: {
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
          
          // todo: move this code just after user's auth
          // start to read pending messages
          pfc.readPendingMessages(true); // true = loop

        }).error(function (err) {
          console.log(err);
        });

      },
      receive: function (msg) {
        var cid = msg.recipient.split('|')[1];

        // store new user in the channels structure
        pfc.addUidToCid(msg.sender, cid);
        
        // store new joined user data
        pfc.users[msg.sender] = msg.body;
        
        // append the user to the list
        pfc.appendUser(pfc.users[msg.sender]); 
        
        // display the join message
        pfc.appendMessage(msg);
      }
    },
    
    /**
     * leave command
     */
    leave: {
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
        }).done(function (users) {
          pfc.clearUserList();
          
          // display a leave message for himself
          pfc.appendMessage({
            type: 'leave',
            sender: pfc.uid
          });
          
          // todo: close the tab
          
        }).error(function (err) {
          console.log(err);
        });
        
      },
      receive: function (msg) {
        var cid = msg.recipient.split('|')[1];

        pfc.removeUidFromCid(msg.sender, cid);
        pfc.removeUser(msg.sender);
        pfc.appendMessage(msg);
      }
    },
    
    /**
     * kick command
     */
    kick: {
      usage:      '/kick "<username>" ["reason"]',
      longusage:  '/kick ["#<channel>"] "<username>" ["reason"]',
      params:     [ 'channel', 'username', 'reason' ],
      regexp:     [ /^("#(.+?)" |)"(.+?)"( "(.+?)"|)$/ ],
      regexp_ids: [ { 2: 'channel', 3: 'username', 5: 'reason' } ]
    },
    
    /**
     * op command
     */
    op: {
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
    },
    
    /**
     * deop command
     */
    deop: {
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
    }
    
    
    
  };

  /**
   * Parse the sent message
   * Try to extract explicit commands from it
   * Returns: [ <cid>, <cmd>, <cmd-param1>, <cmd-param2>, ... ] 
   */
  pfc.parseCommand = function (raw) {
    
    var cmd     = '';
    var cmd_arg = null;
    
    // test each commands on the raw message
    $.each(pfc.commands, function (c) {
      // first of all, try to reconize a /<command> pattern
      if (new RegExp('^\/' + c + '( |$)').test(raw)) {
        cmd = c;
        // parse the rest of the command line (the end)
        var raw_end = new RegExp('^\/' + c + ' *(.*)$').exec(raw)[1];
        $.each(pfc.commands[c].regexp, function (i, regexp) {
          var cmd_arg_tmp = regexp.exec(raw_end);
          if (cmd_arg === null && cmd_arg_tmp && cmd_arg_tmp.length > 0) {
            // collect interesting values from the regexp result
            cmd_arg = {};
            $.each(pfc.commands[c].regexp_ids[i], function (id, key) {
              cmd_arg[key] = cmd_arg_tmp[id]
            });
//             console.log("------------");
//             console.log(i);
//             console.log(regexp);
//             console.log(cmd_arg_tmp);
//             console.log(cmd_arg);
//             console.log("------------");
          }
        });
      }
    });
    
    // if no /<command> pattern found, considere it's a /msg command
    if (cmd === '') {
      cmd     = 'msg';
      cmd_arg = {
        cid:     pfc.cid,
        message: raw
      };
    }

    // return an error if the command parameters do not match
    if (cmd_arg === null) {
      throw [ cmd, pfc.commands[cmd].usage ];
    }    
    
    // optionaly fill channel value if user didn't indicate it
    if (!cmd_arg.cid) {
      if (!cmd_arg.channel) {
        // no channel has been indicated, we have to used the current one
        cmd_arg.cid = pfc.cid;
      } else {
        // one channel has been indicated, we have to translate the channel name to the corresponding cid
        cmd_arg.cid = pfc.getCidFromName(cmd_arg.channel);
              console.log(pfc.getCidFromName(cmd_arg.channel));

      }
    }

    return [ cmd, cmd_arg ];
  };
  
  return pfc;
}(phpFreeChat || {}, jQuery, window));
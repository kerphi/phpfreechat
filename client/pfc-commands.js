/*jslint node: true, maxlen: 150, maxerr: 50, indent: 2 */
'use strict';

/**
 * phpfreechat's commands related functions
 */
var phpFreeChat = (function (pfc, $, window, undefined) {

  pfc.commands = $.extend({}, pfc.commands);
  
  /**
   * msg command
   */
  pfc.commands.msg = {
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
      }
    }

    return [ cmd, cmd_arg ];
  };
  
  return pfc;
}(phpFreeChat || {}, jQuery, window));
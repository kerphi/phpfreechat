/*jslint node: true, maxlen: 150, maxerr: 50, indent: 2 */
'use strict';

/**
 * phpfreechat's commands related functions
 */
var phpFreeChat = (function (pfc, $, window, undefined) {

  pfc.commands = {
    msg:  {
      usage:      '/msg "<message>"',
      longusage:  '/msg ["#<channel>"] "<message>"',
      params:     [ 'channel', 'message' ],
      regexp:     /^("#(.+?)" |)"(.+?)"$/,
      regexp_ids: [ 2, 3, 5 ]
    },
    kick: {
      usage:      '/kick "<username>" ["reason"]',
      longusage:  '/kick ["#<channel>"] "<username>" ["reason"]',
      params:     [ 'channel', 'username', 'reason' ],
      regexp:     /^("#(.+?)" |)"(.+?)"( "(.+?)"|)$/,
      regexp_ids: [ 2, 3, 5 ]
    }
  };

  /**
   * Parse the sent message
   * Try to extract explicit commands from it
   */
  pfc.parseCommand = function (raw) {
    
    var cmd     = '';
    var cmd_arg = [];
    
    // test each commands on the raw message
    $.each(pfc.commands, function(c) {
      // first of all, try to reconize a /<command> pattern
      if (new RegExp('^\/' + c + '( |$)').test(raw)) {
        cmd = c;
        // parse the rest of the command line (the end)
        var raw_end = new RegExp('^\/' + c + ' *(.*)$').exec(raw)[1];
        var cmd_arg_tmp = pfc.commands[c].regexp.exec(raw_end);
        if (cmd_arg_tmp && cmd_arg_tmp.length > 0) {
          // collect interesting values from the regexp result
          cmd_arg = [];
          $.each(pfc.commands[c].regexp_ids, function(i, id) {
            cmd_arg.push(cmd_arg_tmp[id]);
          });
        }
      }
    });
    
    // if no /<command> pattern found, considere it's a /msg command
    if (cmd == '') {
      cmd     = 'msg';
      cmd_arg = [raw];
    }
    
    // return an error if the command parameters do not match
    if (cmd_arg.length == 0) {
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
        
    return [ cmd, cmd_arg ];
  };

  return pfc;
}(phpFreeChat || {}, jQuery, window));
/*jslint node: true, maxlen: 150, maxerr: 50, indent: 2 */
'use strict';

/**
 * phpfreechat's commands related functions
 */
var phpFreeChat = (function (pfc, $, window, undefined) {

  pfc.commands = {
    msg:  {
      usage:      '/msg ["#<channel>"] "<message>"',
      params:     [ 'channel', 'message' ],
      regexp:     /^\/msg ("#(.+?)" |)"(.+?)"$/,
      regexp_ids: [ 2, 3, 5 ]
    },
    kick: {
      usage:      '/kick ["#<channel>"] "<username>" ["reason"]',
      params:     [ 'channel', 'username', 'reason' ],
      regexp:     /^\/kick ("#(.+?)" |)"(.+?)"( "(.+?)"|)$/,
      regexp_ids: [ 2, 3, 5 ]
    }
  };

  /**
   * Parse the sent message
   * Try to extract explicit commands from it
   */
  pfc.parseCommand = function (raw) {
    
    // considere it's a /msg command by default
    var cmd     = 'msg';
    var cmd_arg = [ raw ];
    
    // test each commands on the raw message
    $.each(pfc.commands, function(c) {
      var cmd_arg_tmp = pfc.commands[c].regexp.exec(raw);
      if (cmd_arg_tmp && cmd_arg_tmp.length > 0) {
        cmd = c;
        // collect interesting values from the regexp result
        cmd_arg = [];
        $.each(pfc.commands[c].regexp_ids, function(i, id) {
          cmd_arg.push(cmd_arg_tmp[id]);
        });
      }
    });
    
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
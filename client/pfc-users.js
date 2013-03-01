/*jslint node: true, maxlen: 150, maxerr: 50, indent: 2 */
'use strict';

/**
 * phpfreechat's users related functions
 */
var phpFreeChat = (function (pfc, $, window, undefined) {

  /**
   * Returns the uid from the user's name
   */
  pfc.getUidFromName = function (name) {
    var result = null;
    $.each(pfc.users, function (uid, user) {
      if (name === user.name) {
         result = uid;
      }
    });
    return result;
  };
 
  return pfc;
}(phpFreeChat || {}, jQuery, window));
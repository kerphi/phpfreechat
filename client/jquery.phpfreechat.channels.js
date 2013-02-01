/*jslint node: true, maxlen: 150, maxerr: 50, indent: 2 */
'use strict';

/**
 * phpfreechat's channel related functions
 */
var phpFreeChat = (function (pfc, $, window, undefined) {

//  pfc.channels = {};
  
  /**
   * Returns the channel name of the given channel id
   */
  pfc.getNameFromCid = function (cid) {
    return pfc.channels[cid].name;
  };
  
  /**
   * Returns the channel id of the given channel name
   */
  pfc.getCidFromName = function (channel) {
    var result = null;
    $.each(pfc.channels, function (cid, chan) {
      if (channel === chan.name) {
        result = cid;
      }
    });
    return result;
  };
 
  /**
   * Add a user to the channel structure
   */
  pfc.addUidToCid = function (uid, cid) {
    var idx = $.inArray(uid , pfc.channels[cid].users);
    if (idx === -1) {
      pfc.channels[cid].users.push(uid);
      return true;
    } else {
      return false;
    }
  };
  
  /**
   * Remove a user from the channel structure
   */
  pfc.removeUidFromCid = function (uid, cid) {
    var idx = $.inArray(uid , pfc.channels[cid].users);
    if (idx === -1) {
      return false;
    } else {
      pfc.channels[cid].users.splice(idx, 1);
      pfc.channels[cid].op.splice(idx, 1);
      return true;
    }
  };
  
  /**
   * Add a user to the channel's operators
   */
  pfc.addUidToCidOp = function (uid, cid) {
    var idx = $.inArray(uid , pfc.channels[cid].op);
    if (idx === -1) {
      pfc.addUidToCid(uid, cid);
      pfc.channels[cid].op.push(uid);
      return true;
    } else {
      return false;
    }
  };

  /**
   * Remove a user from the channel's operators
   */
  pfc.removeUidFromCidOp = function (uid, cid) {
    var idx = $.inArray(uid , pfc.channels[cid].op);
    if (idx === -1) {
      return false;
    } else {
      pfc.channels[cid].op.splice(idx, 1);
      return true;
    }
  };

  return pfc;
}(phpFreeChat || {}, jQuery, window));
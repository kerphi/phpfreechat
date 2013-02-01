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
    $.each(pfc.channels, function (cid, chan) {
      if (channel == chan) {
        return cid;
      }
    });
  };
 
  return pfc;
}(phpFreeChat || {}, jQuery, window));
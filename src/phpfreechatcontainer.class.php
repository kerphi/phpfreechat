<?php
/**
 * phpfreechatcontainer.class.php
 *
 * Copyright © 2006 Stephane Gully <stephane.gully@gmail.com>
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful, 
 * but WITHOUT ANY WARRANTY; without even the implied warranty of 
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details. 
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the
 * Free Software Foundation, 51 Franklin St, Fifth Floor,
 * Boston, MA  02110-1301  USA
 */

/**
 * phpFreeChatContainer is an abstract class which define interface
 * to be implemented by concrete container (example: File)
 *
 * @author Stephane Gully <stephane.gully@gmail.com>
 * @abstract
 */
class phpFreeChatContainer
{
  var $c;
  function phpFreeChatContainer(&$config) { $this->c =& $config; }  
  function getDefaultConfig()     { return array(); }
  function init()                 { return array(); }  

  /**
   * Create (connect/join) the nickname into the server or the channel locations
   * Notice: this function must update all channels the users joined (use stored channel list into metadata)
   * @param $chan if NULL then create the user on the server (connect), otherwise create the user on the given channel (join)
   * @param $nick the nickname to create
   * @param $nickid is the corresponding nickname id (taken from session)
   */
  function createNick($chan, $nickname, $nickid)
  { die(_pfc("%s must be implemented", get_class($this)."::".__FUNCTION__)); }

  /**
   * Remove (disconnect/quit) the nickname from the server or from a channel
   * Notice: this function must update all channels if the user disconnect completly (use stored channel list into metadata)
   * @param $chan if NULL then remove the user on the server (disconnect) and on all the joined channels, otherwise just remove the user from the given channel (quit)
   * @param $nick the nickname to remove
   */
  function removeNick($chan, $nickname)
  { die(_pfc("%s must be implemented", get_class($this)."::".__FUNCTION__)); }

  /**
   * Store/update the alive user status somewhere
   * The default File container will just touch (update the date) the nickname file.
   * @param $chan where to update the nick, if null then update the server nick
   * @param $nick nickname to update (raw nickname)
   */
  function updateNick($chan, $nick)
  { die(_pfc("%s must be implemented", get_class($this)."::".__FUNCTION__)); }

  /**
   * Change the user' nickname
   * Notice: this function must update all channels the users joined (use stored channel list into metadata)
   * @param $newnick
   * @param $oldnick
   */
  function changeNick($newnick, $oldnick)
  { die(_pfc("%s must be implemented", get_class($this)."::".__FUNCTION__)); }
  
  /**
   * Returns the nickid, this is a unique id used to identify a user (taken from session)
   * By default this nickid is just stored into the user' metadata, same as :->getNickMeta("nickid")
   * @param $nick
   * @return string the nick id
   */
  function getNickId($nickname)
  { die(_pfc("%s must be implemented", get_class($this)."::".__FUNCTION__)); }

  /**
   * Remove (disconnect/quit) the timeouted nickname from the server or from a channel
   * Notice: this function must remove all nicknames which are not uptodate from the given channel or from the server
   * @param $chan if NULL then check obsolete nick on the server, otherwise just check obsolete nick on the given channel
   * @param $timeout
   * @return array() contains all disconnected nicknames
   */
  function removeObsoleteNick($chan, $timeout)
  { die(_pfc("%s must be implemented", get_class($this)."::".__FUNCTION__)); }

  /**
   * Returns the nickname list on the given channel or on the whole server
   * @param $chan if NULL then returns all connected user, otherwise just returns the channel nicknames
   * @return array() contains a nickname list
   */
  function getOnlineNick($chan)
  { die(_pfc("%s must be implemented", get_class($this)."::".__FUNCTION__)); }

  /**
   * Write a message to the given channel or to the server
   * Notice: a message is very generic, it can be a misc command (notice, me, ...)
   * @param $chan if NULL then write the message on the server, otherwise just write the message on the channel message pool
   * @param $nick is the sender nickname
   * @param $msg is the raw message to write
   */
  function write($chan, $nick, $msg)
  { die(_pfc("%s must be implemented", get_class($this)."::".__FUNCTION__)); }

  /**
   * Read the last posted messages from a channel or from the server
   * @param $chan if NULL then read from the server, otherwise read from the given channel
   * @param $from_id read all message with a greater id
   * @return array() contains the messages list
   */
  function read($chan, $from_id)
  { die(_pfc("%s must be implemented", get_class($this)."::".__FUNCTION__)); }

  /**
   * Returns the last message id
   * Notice: the default file container just returns the messages.index file content
   * @param $chan if NULL then read if from the server, otherwise read if from the given channel
   * @return int is the last posted message id
   */
  function getLastMsgId()
  { die(_pfc("%s must be implemented", get_class($this)."::".__FUNCTION__)); }

  /**
   * Read meta data identified by a key
   * As an example the default file container store metadata into metadata/type/subtype/hash(key)
   * @param $key is the index which identify a metadata
   * @param $type is used to "group" some metadata
   * @param $subtype is used to "group" precisely some metadata, use NULL to ignore it
   * @return mixed the value assigned to the key
   */
  function getMeta($key, $type, $subtype = NULL)
  { die(_pfc("%s must be implemented", get_class($this)."::".__FUNCTION__)); }
  
  /**
   * Write a meta data value identified by a key
   * As an example the default file container store metadata into metadata/type/subtype/hash(key)
   * @param $key is the index which identify a metadata
   * @param $value is the value associated to the key
   * @param $type is used to "group" some metadata
   * @param $subtype is used to "group" precisely some metadata, use NULL to ignore it
   */
  function setMeta($key, $value, $type, $subtype = NULL)
  { die(_pfc("%s must be implemented", get_class($this)."::".__FUNCTION__)); }
  /**
   * Remove a meta data key/value couple
   * Notice: if key is NULL then all the meta data must be removed
   * @param $key is the key to delete, use NULL to delete all the metadata
   * @param $type is used to "group" some metadata
   * @param $subtype is used to "group" precisely some metadata, use NULL to ignore it
   */
  function rmMeta($key, $type, $subtype = NULL)
  { die(_pfc("%s must be implemented", get_class($this)."::".__FUNCTION__)); }

  /**
   * Remove all created data for this server (identified by serverid)
   * Notice: for the default File container, it's just a recursive directory remove
   */
  function clear()
  { die(_pfc("%s must be implemented", get_class($this)."::".__FUNCTION__)); }
}

?>

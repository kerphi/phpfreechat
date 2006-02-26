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
 * phpFreeChatContainer is an abstract class which define interface to be implemented by concret container (example: File)
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
  function updateNick($nickname)  { die(_pfc("%s must be implemented", get_class($this)."::".__FUNCTION__)); }
  function getNickId($nickname)   { die(_pfc("%s must be implemented", get_class($this)."::".__FUNCTION__)); }
  function changeNick($newnick)   { die(_pfc("%s must be implemented", get_class($this)."::".__FUNCTION__)); }
  function removeNick($nick)      { die(_pfc("%s must be implemented", get_class($this)."::".__FUNCTION__)); }
  function removeObsoleteNick()   { die(_pfc("%s must be implemented", get_class($this)."::".__FUNCTION__)); }
  function changeMyNick($newnick) { die(_pfc("%s must be implemented", get_class($this)."::".__FUNCTION__)); }
  function getOnlineNick()        { die(_pfc("%s must be implemented", get_class($this)."::".__FUNCTION__)); }
  function writeMsg($nick, $msg)  { die(_pfc("%s must be implemented", get_class($this)."::".__FUNCTION__)); }
  function readNewMsg($from_id)   { die(_pfc("%s must be implemented", get_class($this)."::".__FUNCTION__)); }
  function getLastMsgId()         { die(_pfc("%s must be implemented", get_class($this)."::".__FUNCTION__)); }
}

?>
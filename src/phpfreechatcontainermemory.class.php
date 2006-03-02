<?php
/**
* phpfreechatcontainermemory.class.php
*
* Copyright © 2006 Bernhard J. M. Grün <Bernhard.Gruen@googlemail.com>
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

require_once dirname(__FILE__)."/phpfreechatcontainer.class.php";
require_once dirname(__FILE__)."/../lib/pear/System/SharedMemory.php";

/**
* phpFreeChatContainerMemory is a concrete container which stores data into
* shared memory using a PEAR class.
* Version: 2006-02-28#01
*
* @author Bernhard J. M. Grün <Bernhard.Gruen@googlemail.com>
*/
class phpFreeChatContainerMemory extends phpFreeChatContainer
{
  var $_users = NULL;
  var $_cache_nickid = array();
  var $_memory = NULL;

  function _connect()
  {
    $c =& $this->c;
    if ($c->container_cfg_sm_type=="auto")
      $this->_memory =& System_SharedMemory::factory();
    else
      $this->_memory =& System_SharedMemory::factory($c->container_cfg_sm_type, $c->container_cfg_sm_options);
  }

  function getDefaultConfig()
  {
    $c =& $this->c;

    $cfg = array();
    $cfg['sm_type'] = "";
    $cfg['sm_options'] = array();
    $cfg['sm_messages'] = "";
    $cfg['sm_message_index'] = "";
    $cfg['sm_nicknames'] = "";
    $cfg['sm_nicknames_time'] = "";
    return $cfg;
  }

  function init()
  {
    $c =& $this->c;
    if ($c->container_cfg_sm_type=="")
      $c->container_cfg_sm_type = "auto";
    if ($c->container_cfg_sm_options=="" || !is_array($c->container_cfg_sm_options))
      $c->container_cfg_sm_options = array();
    if ($c->container_cfg_sm_messages=="")
      $c->container_cfg_sm_messages = $c->prefix.$c->serverid.'messages';
    if ($c->container_cfg_sm_message_index=="")
      $c->container_cfg_sm_message_index = $c->prefix.$c->serverid.'message_index';
    if ($c->container_cfg_sm_nicknames=="")
      $c->container_cfg_sm_nicknames = $c->prefix.$c->serverid.'nicknames';
    if ($c->container_cfg_sm_nicknames_time=="")
      $c->container_cfg_sm_nicknames_time = $c->prefix.$c->serverid.'nicknames_time';

    $this->_connect();
    $messages=$this->_memory->get
              ($c->container_cfg_sm_messages);
    $message_index=$this->_memory->get
                   ($c->container_cfg_sm_message_index);
    $nick_array=$this->_memory->get
                ($c->container_cfg_sm_nicknames);
    $nicktime_array=$this->_memory->get
                    ($c->container_cfg_sm_nicknames_time);
    if (!isset($messages) || $messages==NULL || !is_array($messages))
    {
      $this->_memory->rm($c->container_cfg_sm_messages);
      $this->_memory->set
      ($c->container_cfg_sm_messages, array());
    }
    if (!isset($message_index) || $message_index==NULL || !is_numeric($message_index))
      $this->_memory->set
      ($c->container_cfg_sm_message_index, 0);
    if (!isset($nick_array) || $nick_array==NULL || !is_array($nick_array))
    {
      $this->_memory->rm($c->container_cfg_sm_nicknames);
      $this->_memory->set
      ($c->container_cfg_sm_nicknames, array());
    }
    if (!isset($nicktime_array) || $nicktime_array==NULL || !is_array($nicktime_array))
    {
      $this->_memory->rm($c->container_cfg_sm_nicknames_time);
      $this->_memory->set
      ($c->container_cfg_sm_nicknames_time, array());
    }
    return array();
  }

  function updateNick($nickname)
  {
    $c =& $this->c;
    $this->_connect();
    $nick_array=$this->_memory->get
                ($c->container_cfg_sm_nicknames_time);
    $nick_array[$c->nick]=time();
    $this->_memory->set
    ($c->container_cfg_sm_nicknames_time, $nick_array);
    return true;
  }

  /**
  * returns the id identifying the nickname's owner session
  */
  function getNickId($nickname)
  {
    if (!isset($this->_cache_nickid[$nickname]))
    {
      $c =& $this->c;
      $nickid = "undefined";
      $this->_connect();
      $nick_array=$this->_memory->get
                  ($c->container_cfg_sm_nicknames);
      if (isset($nick_array[$nickname]))
      {
        // write the nickid into the new nickname place
        $nickid=$nick_array[$nickname];
        if ($nickid == "")
          $nickid = "undefined";
      }
      $this->_cache_nickid[$nickname] = $nickid;
    }
    return $this->_cache_nickid[$nickname];
  }

  /**
  * create an array element containing the new nickname id
  * and delete oldnickname array element if the nickname id match
  */
  function changeNick($newnick)
  {
    $c =& $this->c;
    $nickid = $c->sessionid;
    $oldnickid = $this->getNickId($c->nick);
    $this->_connect();
    $nick_array=$this->_memory->get
                ($c->container_cfg_sm_nicknames);
    $nicktime_array=$this->_memory->get
                    ($c->container_cfg_sm_nicknames_time);
    // delete the old nickname element only if the nickid match
    if ($nickid == $oldnickid)
    {
      unset($nick_array[$c->nick]);
      unset($nicktime_array[$c->nick]);
    }
    // write the nickid into the new nicknames element
    $nick_array[$newnick]=$nickid;
    $nicktime_array[$newnick]=time();
    $this->_memory->set
    ($c->container_cfg_sm_nicknames, $nick_array);
    $this->_memory->set
    ($c->container_cfg_sm_nicknames_time, $nicktime_array);
    return $newnick;
  }

  function removeNick($nick)
  {
    $c =& $this->c;
    $this->_connect();
    $nick_array=$this->_memory->get
                ($c->container_cfg_sm_nicknames);
    $nicktime_array=$this->_memory->get
                    ($c->container_cfg_sm_nicknames_time);
    $nickid = $this->getNickId($nick);
    // don't allow to remove foreign nicknames
    if ($c->sessionid == $nickid && isset($nick_array[$nick]))
    {
      unset($nick_array[$nick]);
      unset($nicktime_array[$nick]);
      $this->_memory->set
      ($c->container_cfg_sm_nicknames, $nick_array);
      $this->_memory->set
      ($c->container_cfg_sm_nicknames_time, $nicktime_array);
      return true;
    }
    else
      return false;
  }

  function removeObsoleteNick()
  {
    $c =& $this->c;

    $deleted_user = array();
    $users = array();
    $this->_connect();
    $nicktime_array=$this->_memory->get
                    ($c->container_cfg_sm_nicknames_time);
    $nick_array=$this->_memory->get
                ($c->container_cfg_sm_nicknames);
    if (!isset($nicktime_array))
      return $deleted_user;
    $deleted=false;
    foreach ($nicktime_array as $key => $nick_time)
    {
      if (time() > ($nick_time+2+($c->refresh_delay/1000)*4) )
      {
        $deleted_user[]=$key;
        unset($nicktime_array[$key]);
        unset($nick_array[$key]);
        $deleted=true;
      }
      else
      {
        $users[]=$key;
      }
    }
    $this->_users =& $users; // _users will be used by getOnlineUserList
    if ($deleted)
    {
      $this->_memory->set
      ($c->container_cfg_sm_nicknames_time, $nicktime_array);
      $this->_memory->set
      ($c->container_cfg_sm_nicknames, $nick_array);
    }
    return $deleted_user;
  }

  function getOnlineNick()
  {
    if (is_array($this->_users) && count($this->_users)>0)
      return $this->_users;

    $c =& $this->c;
    $users = array();
    $this->_connect();
    $nicktime_array=$this->_memory->get
                    ($c->container_cfg_sm_nicknames_time);
    if (!isset($nicktime_array))
      return $users;
    foreach ($nicktime_array as $key => $nick_time)
    {
      $users[]=$key;
    }
    return $users;
  }

  /**
  * Returns the last posted message id
  */
  function getLastMsgId()
  {
    // read last message id
    $c =& $this->c;
    $this->_connect();
    $msg_id=$this->_memory->get
            ($c->container_cfg_sm_message_index);
    return isset($msg_id) ? $msg_id : 0;
  }

  function readNewMsg($from_id)
  {

    $c =& $this->c;
    $this->_connect();
    $content=$this->_memory->get
             ($c->container_cfg_sm_messages);

    // remove old messages
    $content = array_slice($content, -$c->max_msg);
    $this->_memory->set
    ($c->container_cfg_sm_messages, $content);

    // format content in order to extract only necessary information
    $formated_content = array();
    $new_from_id = $from_id;
    foreach ( $content as $line )
    {
      if ($line != "" && $line != "\n")
      {
        $formated_line = explode( "\t", $line );
        if ($from_id < $formated_line[0])
          $formated_content[] = $formated_line;
        if ($new_from_id < $formated_line[0])
          $new_from_id = $formated_line[0];
      }
    }
    return array("messages" => $formated_content, "new_from_id" => $new_from_id);
  }

  function writeMsg($nickname, $message)
  {
    // format message
    $msg_id = $this->_requestMsgId();
    $line = $msg_id."\t";
    $line .= date("d/m/Y")."\t";
    $line .= date("H:i:s")."\t";
    $line .= $nickname."\t";
    $line .= $message;

    // write it to message array
    $c =& $this->c;
    $this->_connect();
    $content=$this->_memory->get
             ($c->container_cfg_sm_messages);
    $count=count($content)+1;
    $content[$count]=$line;
    $this->_memory->set
    ($c->container_cfg_sm_messages, $content);
    return true;
  }

  /**
  * used internaly
  */
  function _requestMsgId()
  {
    // read last message id
    $c =& $this->c;
    $this->_connect();
    $msg_id=$this->_memory->get
            ($c->container_cfg_sm_message_index);
    if (!is_numeric($msg_id))
      $msg_id = 0;
    // increment message id and save it
    $msg_id++;
    $this->_memory->set
    ($c->container_cfg_sm_message_index, $msg_id);
    return $msg_id;
  }
}

?>

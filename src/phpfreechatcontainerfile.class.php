<?php
/**
 * phpfreechatcontainerfile.class.php
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

require_once dirname(__FILE__)."/phpfreechatcontainer.class.php";

/**
 * phpFreeChatContainerFile is a concret container which stock data into files
 *
 * @author Stephane Gully <stephane.gully@gmail.com>
 */
class phpFreeChatContainerFile extends phpFreeChatContainer
{
  //  var $_users = array();
  //var $_cache_nickid = array();

  function phpFreeChatContainerFile(&$config)
  {
    phpFreeChatContainer::phpFreeChatContainer(&$config);
    //    $this->loadPaths();
  }

  function loadPaths()
  {
    $c =& $this->c;
    $c->container_cfg_chat_dir            = $c->data_private_path."/chat";
    $c->container_cfg_server_dir          = $c->container_cfg_chat_dir."/s_".$c->serverid;
    $c->container_cfg_server_nickname_dir = $c->container_cfg_server_dir."/nicknames";
    $c->container_cfg_meta_dir            = $c->container_cfg_server_dir."/metadata";
    $c->container_cfg_channel_dir         = $c->container_cfg_server_dir."/channels";   
  }
  
  function getDefaultConfig()
  {
    $c =& $this->c;
    
    $cfg = array();
    $cfg["chat_dir"]            = ""; // will be generated from the other parameters into the init step
    $cfg["server_dir"]          = ""; // will be generated from the other parameters into the init step
    $cfg["server_nickname_dir"] = ""; // will be generated from the other parameters into the init step
    $cfg["meta_dir"]            = ""; // will be generated from the other parameters into the init step
    $cfg["channel_dir"]         = ""; // will be generated from the other parameters into the init step
    return $cfg;
  }
  
  function init()
  {
    $errors = array();
    $c =& $this->c;

    // generate the container parameters from other config parameters
    if ($c->container_cfg_chat_dir == "")
      $c->container_cfg_chat_dir = $c->data_private_path."/chat";
    $this->loadPaths();
   
    $errors = array_merge($errors, @test_writable_dir($c->container_cfg_chat_dir,            "container_cfg_chat_dir"));
    $errors = array_merge($errors, @test_writable_dir($c->container_cfg_server_dir,          "container_cfg_chat_dir/serverid"));
    $errors = array_merge($errors, @test_writable_dir($c->container_cfg_server_nickname_dir, "container_cfg_chat_dir/nicknames"));
    $errors = array_merge($errors, @test_writable_dir($c->container_cfg_meta_dir,            "container_cfg_chat_dir/metadata"));
    $errors = array_merge($errors, @test_writable_dir($c->container_cfg_channel_dir,         "container_cfg_chat_dir/channels"));
    
    return $errors;
  }

  /**
   * Create (connect/join) the nickname into the server or the channel locations
   * Notice: the caller must take care to update all channels the users joined (use stored channel list into metadata)
   * @param $chan if NULL then create the user on the server (connect), otherwise create the user on the given channel (join)
   * @param $nick the nickname to create
   * @param $nickid is the corresponding nickname id (taken from session)
   */
  function createNick($chan, $nick, $nickid)
  {
    $c =& $this->c;
    $nick_dir = ($chan != NULL) ?
      $c->container_cfg_channel_dir."/".$this->_encode($chan)."/nicknames" :
      $c->container_cfg_server_dir."/nicknames";

    // check the nickname directory exists
    $errors = @test_writable_dir($nick_dir, $chan."/nicknames/".$nick);
    if ($c->debug)
    {
      if (count($errors)>0)
        pxlog("createNick(".$nick.", ".$nickid.") - Error: ".var_export($errors), "chat", $c->getId());
    }
    
    $nick_filename = $nick_dir."/".$this->_encode($nick);

    // check the if the file exists only in debug mode!
    if ($c->debug)
    {
      if (file_exists($nick_filename))
        pxlog("createNick(".$nick.", ".$nickid.") - Error: another nickname data file exists, we are overwriting it (nickname takeover)!", "chat", $c->getId());
    }
    
    // trust the caller : this nick is not used
    $fp = fopen($nick_filename, "w");
    flock ($fp, LOCK_EX); // lock
    fwrite($fp, $nickid);
    flock ($fp, LOCK_UN); // unlock
    fclose($fp);


    /**
     * @todo: this is not the container' job to keep synchronized the user' metadatas !
     */

    // update the user's metadata (channels)
    if ($chan != NULL)
    {
      $userchan = $this->getMeta("channels", "nickname", $nick);
      $userchan = $userchan != NULL ? unserialize($userchan) : array();
      if (!in_array($chan, $userchan))
      {
        $userchan[] = $chan;
        $this->setMeta(serialize($userchan), "channels", "nickname", $nick);
      }
    }
    
    //    if (!in_array($nickname, $this->_users))
    //      $this->_users[] = $nickname; // _users will be used by getOnlineUserList

    return true;
  }

  /**
   * Remove (disconnect/quit) the nickname from the server or from a channel
   * Notice: The caller must take care to update all joined channels.
   * @param $chan if NULL then remove the user on the server (disconnect), otherwise just remove the user from the given channel (quit)
   * @param $nick the nickname to remove
   */
  function removeNick($chan, $nick)
  {
    $c =& $this->c;
    $nick_dir = ($chan != NULL) ?
      $c->container_cfg_channel_dir."/".$this->_encode($chan)."/nicknames" :
      $c->container_cfg_server_dir."/nicknames";
    $nick_filename = $nick_dir."/".$this->_encode($nick);

    if ($c->debug)
    {
      // @todo: check if the removed nick is mine in debug mode!
      
      // check the nickname file really exists
      if (!file_exists($nick_filename))
        pxlog("removeNick(".$nick.") - Error: the nickname data file to remove doesn't exists", "chat", $c->getId());
    }

    @unlink($nick_filename);



    /**
     * @todo: this is not the container' job to keep synchronized the user' metadatas !
     */
    
    // update the user's metadata (channels)
    if ($chan != NULL)
    {
      // the user just disconnect from a channel
      $userchan = $this->getMeta("channels", "nickname", $nick);
      $userchan = $userchan != NULL ? unserialize($userchan) : array();
      if (in_array($chan, $userchan))
      {
        $key = array_search($chan, $userchan);
        unset($userchan[$key]);
        $this->setMeta(serialize($userchan), "channels", "nickname", $nick);
      }
    }
    else
    {
      // the user disconnect from the whole server
      $this->rmMeta("channels", "nickname", $nick);
    }

    /*
    // remove the nickname from the cache list
    if (in_array($nick, $this->_users))
    {
      $key = array_search($nick, $this->_users);
      unset($this->_users[$key]);
    }
    */
    
    return true;
  }

  /**
   * Store/update the alive user status somewhere
   * The default File container will just touch (update the date) the nickname file.
   * @param $chan where to update the nick, if null then update the server nick
   * @param $nick nickname to update (raw nickname)
   */
  function updateNick($chan, $nick)
  {
    $c =& $this->c;
    $there = false;
    
    $nick_dir = ($chan != NULL) ?
      $c->container_cfg_channel_dir."/".$this->_encode($chan)."/nicknames" :
      $c->container_cfg_server_dir."/nicknames";
    
    // update my online status file
    $nick_filename = $nick_dir."/".$this->_encode($nick);
    if (file_exists($nick_filename)) $there = true;
    @touch($nick_filename);
    @chmod($nick_filename, 0777); 
    
    return $there;
  }

  /**
   * Change the user' nickname
   * Notice: this call must take care to update all channels the user joined
   * @param $chan where to update the nick, if null then update the server nick
   * @param $newnick
   * @param $oldnick
   */
  function changeNick($chan, $newnick, $oldnick)
  {
    $c =& $this->c;

    $nick_dir = ($chan != NULL) ?
      $c->container_cfg_channel_dir."/".$this->_encode($chan)."/nicknames" :
      $c->container_cfg_server_dir."/nicknames";
    $newnick_filename = $nick_dir."/".$this->_encode($newnick);
    $oldnick_filename = $nick_dir."/".$this->_encode($oldnick);

    $ok = @rename($oldnick_filename, $newnick_filename);
    
    return $ok;
  }

  /**
   * Returns the nickid, this is a unique id used to identify a user (taken from session)
   * By default this nickid is just stored into the user' metadata, same as :->getNickMeta("nickid")
   * @param $nick
   * @return string the nick id
   */
  function getNickId($nickname)
  {
    //if (!isset($this->_cache_nickid[$nickname]))
    //{
    $c =& $this->c;
    $nickid = "undefined";
    
    $nick_dir = $c->container_cfg_server_dir."/nicknames";
    $nick_filename = $nick_dir."/".$this->_encode($nickname);
    
    if (file_exists($nick_filename))
    {
      $fsize = filesize($nick_filename);
      if ($fsize>0)
      {
        // write the nickid into the new nickname file
        $fp = fopen($nick_filename, "r");
        $nickid = fread($fp, $fsize);
        if ($nickid == "") $nickid = "undefined";
        fclose($fp);
      }
    }
    //$this->_cache_nickid[$nickname] = $nickid;
    //if ($c->debug) pxlog("getNickId[".$c->sessionid."]: nickname=".$nickname." nickid=".$nickid, "chat", $c->getId());
    //}
    return $nickid; //$this->_cache_nickid[$nickname];
  }


  /**
   * Remove (disconnect/quit) the timeouted nickname from the server or from a channel
   * Notice: this function must remove all nicknames which are not uptodate from the given channel or from the server
   * @param $chan if NULL then check obsolete nick on the server, otherwise just check obsolete nick on the given channel
   * @param $timeout
   * @return array() contains all disconnected nicknames
   */
  function removeObsoleteNick($chan, $timeout)
  {
    $c =& $this->c;

    $nick_dir = ($chan != NULL) ?
      $c->container_cfg_channel_dir."/".$this->_encode($chan)."/nicknames" :
      $c->container_cfg_server_dir."/nicknames";
    // check the nickname directory exists
    $errors = @test_writable_dir($nick_dir, $chan."/nicknames");
    
    $deleted_user = array();
    $users = array();
    $dir_handle = opendir($nick_dir);
    while (false !== ($file = readdir($dir_handle)))
    {
      if ($file == "." || $file == "..") continue; // skip . and .. generic files
      if (time() > (filemtime($nick_dir."/".$file)+$timeout/1000) ) // user will be disconnected after 'timeout' secondes of inactivity
      {
        $deleted_user[] = $this->_decode($file);
        unlink($nick_dir."/".$file); // disconnect expired user
      }
      else
      {
        // optimisation: cache user list for next getOnlineUserList call
        $users[] = $this->_decode($file);
      }
    }

    //    $this->_users =& $users; // _users will be used by getOnlineUserList
    
    return $deleted_user;
  }

  /**
   * Returns the nickname list on the given channel or on the whole server
   * @param $chan if NULL then returns all connected user, otherwise just returns the channel nicknames
   * @return array() contains a nickname list
   */
  function getOnlineNick($chan)
  {
    //    if (is_array($this->_users))
    //      return $this->_users;
   
    $c =& $this->c;

    $nick_dir = ($chan != NULL) ?
      $c->container_cfg_channel_dir."/".$this->_encode($chan)."/nicknames" :
      $c->container_cfg_server_dir."/nicknames";

    $users = array();
    $dir_handle = opendir($nick_dir);
    while (false !== ($file = readdir($dir_handle)))
    {
      if ($file == "." || $file == "..") continue; // skip . and .. generic files
      $users[] = $this->_decode($file);
    }
    return $users;
  }

  /**
   * Write a command to the given channel or to the server
   * Notice: a message is very generic, it can be a misc command (notice, me, ...)
   * @param $chan if NULL then write the message on the server, otherwise just write the message on the channel message pool
   * @param $nick is the sender nickname
   * @param $cmd is the command name (ex: "send", "nick", "kick" ...)
   * @param $param is the command' parameters (ex: param of the "send" command is the message)
   * @return $msg_id the created message identifier
   */
  function write($chan, $nick, $cmd, $param)
  {            
    $c =& $this->c;

    $msg_dir = ($chan != NULL) ?
      $c->container_cfg_channel_dir."/".$this->_encode($chan)."/messages" :
      $c->container_cfg_server_dir."/messages";
    // check the messages directory exists
    $errors = @test_writable_dir($msg_dir, $chan."/messages");

    // request a unique id for this new message
    $msg_id = $this->_requestMsgId($chan);
    $msg_filename = $msg_dir."/".$msg_id;

    // format message
    $data = "\n";
    $data .= $msg_id."\t";
    $data .= date("d/m/Y")."\t";
    $data .= date("H:i:s")."\t";
    $data .= $nick."\t";
    $data .= $cmd."\t";
    $data .= $param;

    // write message
    file_put_contents($msg_filename, $data);
    
    return $msg_id;
  }

  /**
   * Read the last posted commands from a channel or from the server
   * @param $chan if NULL then read from the server, otherwise read from the given channel
   * @param $from_id read all message with a greater id
   * @return array() contains the command list
   * @todo use one file (filename = msgid) for one message
   */
  function read($chan, $from_id)
  {
    $c =& $this->c;
    
    $msg_dir = ($chan != NULL) ?
      $c->container_cfg_channel_dir."/".$this->_encode($chan)."/messages" :
      $c->container_cfg_server_dir."/messages";
    // check the messages directory exists
    $errors = @test_writable_dir($msg_dir, $chan."/messages");

    
    // read the files into the directory
    // sort it by filename order (id order)
    // then take only the > $from_id messages
    $newmsg      = array();
    $new_from_id = $from_id;   
    $dir_handle  = opendir($msg_dir);
    while (false !== ($file = readdir($dir_handle)))
    {
      if ($file == "." || $file == "..") continue; // skip . and .. generic files
      if ($file>$from_id)
      {
        $new_from_id = $file;
        $newmsg[]    = $file;
      }
    }
    
    // format content
    $datalist = array();
    foreach ( $newmsg as $m )
    {
      $line = file_get_contents($msg_dir."/".$m);
      if ($line != "" && $line != "\n")
      {
        $formated_line = explode( "\t", $line );
        $data = array();
        $data["id"]    = trim($formated_line[0]);
        $data["date"]  = $formated_line[1];
        $data["time"]  = $formated_line[2];
        $data["sender"]= $formated_line[3];
        $data["cmd"]   = $formated_line[4];
        $data["param"] = $formated_line[5];
        $datalist[] = $data;
      }
    }

    return array("data" => $datalist,
                 "new_from_id" => $new_from_id );
  }

  /**
   * Returns the last message id
   * Notice: the default file container just returns the messages.index file content
   * @param $chan if NULL then read if from the server, otherwise read if from the given channel
   * @return int is the last posted message id
   */
  function getLastId($chan)
  {
    $c =& $this->c;
    
    // calculate the messages.index location
    $chan_dir = ($chan != NULL) ?
      $c->container_cfg_channel_dir."/".$this->_encode($chan) :
      $c->container_cfg_server_dir;
    $index_filename = $chan_dir . "/messages.index";

    // read last message id
    $lastid = trim(@file_get_contents($index_filename));
    if (!is_numeric($lastid)) $lastid = 0;
    
    return $lastid;
  }


  /**
   * Read meta data identified by a key
   * As an example the default file container store metadata into metadata/type/subtype/hash(key)
   * @param $key is the index which identify a metadata
   * @param $type is used to "group" some metadata
   * @param $subtype is used to "group" precisely some metadata, use NULL to ignore it
   * @return mixed the value assigned to the key
   */
  function getMeta($key, $type, $subtype = NULL)
  {
    $c =& $this->c;
    
    // encode parameters
    $enc_key     = $this->_encode($key);
    $enc_type    = $this->_encode($type);
    $enc_subtype = ($subtype == NULL) ? "" : $this->_encode($subtype);

    // read data from metadata file
    $dir_base = $c->container_cfg_meta_dir;
    $dir = $dir_base."/".$enc_type.($enc_subtype == "" ? "" : "/".$enc_subtype);
    $filename = $dir."/".$enc_key;
    $ret = @file_get_contents($filename);
    if ($ret == false)
      return NULL;
    else
      return $ret;
  }
  
  /**
   * Write a meta data value identified by a key
   * As an example the default file container store metadata into metadata/type/subtype/hash(key)
   * @param $key is the index which identify a metadata
   * @param $value is the value associated to the key
   * @param $type is used to "group" some metadata
   * @param $subtype is used to "group" precisely some metadata, use NULL to ignore it
   */
  function setMeta($value, $key, $type, $subtype = NULL)
  {
    $c =& $this->c;
    
    // encode parameters
    $enc_key     = $this->_encode($key);
    $enc_type    = $this->_encode($type);
    $enc_subtype = ($subtype == NULL) ? "" : $this->_encode($subtype);

    // create directories
    $dir_base = $c->container_cfg_meta_dir;
    $dir = $dir_base."/".$enc_type.($enc_subtype == "" ? "" : "/".$enc_subtype);
    if (!is_dir($dir)) mkdir_r($dir);

    // create or replace metadata file
    $filename = $dir."/".$enc_key;
    $ret = @file_put_contents($filename, $value);
    if ($ret == false)
      return false;
    else
      return true;
  }

  /**
   * Remove a meta data key/value couple
   * Notice: if key is NULL then all the meta data must be removed
   * @param $key is the key to delete, use NULL to delete all the metadata
   * @param $type is used to "group" some metadata
   * @param $subtype is used to "group" precisely some metadata, use NULL to ignore it
   */
  function rmMeta($key, $type, $subtype = NULL)
  {
    $c =& $this->c;
    
    // encode parameters
    $enc_key     = ($key == NULL) ? "" : $this->_encode($key);
    $enc_type    = $this->_encode($type);
    $enc_subtype = ($subtype == NULL) ? "" : $this->_encode($subtype);

    // rm data from metadata file
    $dir_base = $c->container_cfg_meta_dir;
    $dir = $dir_base."/".$enc_type.($enc_subtype == "" ? "" : "/".$enc_subtype);
    $ret = true;
    if ($enc_key == "")
    {
      // remove all keys (the complete directory)
      @rm_r($dir);
    }
    else
    {
      // just remove one key
      $filename = $dir."/".$enc_key;
      $ret = @unlink($filename);
    }

    return $ret;
  }

  /**
   * Remove all created data for this server (identified by serverid)
   * Notice: for the default File container, it's just a recursive directory remove
   */
  function clear()
  {
    $c =& $this->c;
    // remove the created files and directories
    $dir = $c->container_cfg_server_dir;
    rm_r($dir);
  }
  
  /**
   * Return a unique id. Each time this function is called, the last id is incremented.
   * used internaly
   */ 
  function _requestMsgId($chan)
  {
    $c =& $this->c;

    // calculate the messages.index location
    $chan_dir = ($chan != NULL) ?
      $c->container_cfg_channel_dir."/".$this->_encode($chan) :
      $c->container_cfg_server_dir;
    // check the directory exists
    $errors = @test_writable_dir($chan_dir, $chan_dir);
    
    $index_filename = $chan_dir . "/messages.index";
    
    // read last message id
    $msg_id = 0;
    if (!file_exists($index_filename))
      file_put_contents($index_filename, "0");
    $fp = fopen($index_filename, "rw+");
    if (is_resource($fp))
    {
      flock ($fp, LOCK_EX);
      $msg_id = fread($fp, filesize($index_filename));
      if (!is_numeric($msg_id)) $msg_id = 0;
      // increment message id and save it
      $msg_id++;
      ftruncate($fp, 0);
      fseek($fp, 0);
      fwrite($fp, $msg_id);
      flock ($fp, LOCK_UN);
      fclose($fp);
    }

    return $msg_id;
  }
  
  function _encode($str)
  {
    return base64_encode(urlencode($str));
  }
  
  function _decode($str)
  {
    return urldecode(base64_decode($str));
  }
}

?>
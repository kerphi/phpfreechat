<?php
/**
 * pfccontainer_file.class.php
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

require_once dirname(__FILE__)."/../pfccontainer.class.php";

/**
 * pfcContainer_File is a concret container which stock data into files
 *
 * @author Stephane Gully <stephane.gully@gmail.com>
 */
class pfcContainer_File extends pfcContainer
{
  var $_users = array("nickid"    => array(),
                      "timestamp" => array());
  var $_meta = array();
  
  function pfcContainer_File(&$config)
  {
    pfcContainer::pfcContainer($config);
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

    // store nickid -> nickname and nickname -> nickid correspondance
    $this->setMeta($nick, "nickname", "fromnickid", $nickid);
    $this->setMeta($nickid, "nickid", "fromnickname", $nick);

    $this->_registerUserMeta($nickid, $chan);

    if ($c->debug) pxlog("createNick - nickname metadata created: chan=".($chan==NULL?"SERVER":$chan)." nickid=".$nickid, "chat", $c->getId());

    /*
    // increment the nick references (used to know when the nick is really disconnected)
    $nick_ref = $this->getMeta("references", $nickid);
    if ($nick_ref == NULL || !is_numeric($nick_ref)) $nick_ref = 0;
    $nick_ref++;
    $this->setMeta($nick_ref, "references", $nickid);
    */

    
    $c =& $this->c;
    $nick_dir = ($chan != NULL) ?
      $c->container_cfg_channel_dir."/".$this->_encode($chan)."/nicknames" :
      $c->container_cfg_server_dir."/nicknames";

    // check the nickname directory exists
    $errors = @test_writable_dir($nick_dir, $chan."/nicknames/".$nick);
    if ($c->debug)
    {
      if (count($errors)>0)
        pxlog("createNick(".$nick.", ".$nickid.") - Error: ".var_export($errors,true), "chat", $c->getId());
    }
    
    $nickid_filename = $nick_dir."/".$nickid; //$this->_encode($nick);

    // check the if the file exists only in debug mode!
    if ($c->debug)
    {
      /*
      if (file_exists($nickid_filename))
        pxlog("createNick(".$nick.", ".$nickid.") - Error: another nickname data file exists, we are overwriting it (nickname takeover)!: ".$nickid_filename, "chat", $c->getId());
      else
        pxlog("createNick - nickname file created: chan=".($chan==NULL?"SERVER":$chan)." nickid=".$nickid, "chat", $c->getId());
      */
    }
    
    // trust the caller : this nick is not used
    touch($nickid_filename);
    
    // append the nickname to the cached nickname list
    $id = $this->isNickOnline($chan, $nick);
    $_chan = ($chan == NULL) ? "SERVER" : $chan;
    if ($id<0)
    {
      $this->_users[$_chan]["nickid"][]    = $nickid;
      $this->_users[$_chan]["timestamp"][] = filemtime($nickid_filename);
    }

    return true;
  }

  /**
   * Remove (disconnect/quit) the nickname from the server or from a channel
   * Notice: The caller must take care to update all joined channels.
   * @param $chan if NULL then remove the user on the server (disconnect), otherwise just remove the user from the given channel (quit)
   * @param $nick the nickname to remove
   * @return true if the nickname was correctly removed
   */
  function removeNick($chan, $nick)
  {
    // retrive the nickid to remove
    $nickid = $this->getNickId($nick);
    if ($nickid == "undefined") return false;

    $c =& $this->c;
    $nick_dir = ($chan != NULL) ?
      $c->container_cfg_channel_dir."/".$this->_encode($chan)."/nicknames" :
      $c->container_cfg_server_dir."/nicknames";
    $nickid_filename = $nick_dir."/".$nickid; //$this->_encode($nick);

    if ($c->debug)
    {
      // @todo: check if the removed nick is mine in debug mode!
      
      // check the nickname file really exists
      if (!file_exists($nickid_filename))
        pxlog("removeNick(".$nick.") - Error: the nickname data file to remove doesn't exists: ".$nickid_filename, "chat", $c->getId());
      else
        pxlog("removeNick - nickname file removed: chan=".($chan==NULL?"SERVER":$chan)." nickid=".$nickid, "chat", $c->getId());
    }

    $ok = @unlink($nickid_filename);

    // remove the user metadata if he is disconnected from the server

    $this->_unregisterUserMeta($nickid, $chan);

    /*
    // decrement the nick references and kill the metadata if not more references is found
    // (used to know when the nick is really disconnected)
    $nick_ref = $this->getMeta("references", $nickid);
    if ($nick_ref == NULL || !is_numeric($nick_ref)) $nick_ref = 0;
    $nick_ref--;
    if ($nick_ref <= 0)
    {
      $this->rmMeta("nickid", "fromnickname", $nick);
      $this->rmMeta("nickname", "fromnickid", $nickid);
      $this->rmMeta("references", $nickid); // destroy also the reference counter (by default its value is 0)
    }
    else
      $this->setMeta($nick_ref, "references", $nickid);
    */
    
    if ($c->debug)
    {
      // check the nickname file is correctly deleted
      if (file_exists($nickid_filename))
        pxlog("removeNick(".$nick.") - Error: the nickname data file yet exists", "chat", $c->getId());
    }

    // remove the nickname from the cache list
    $id = $this->isNickOnline($chan, $nick);
    $_chan = ($chan == NULL) ? "SERVER" : $chan;
    if ($id >= 0)
    {
      unset($this->_users[$_chan]["nickid"][$id]);
      unset($this->_users[$_chan]["timestamp"][$id]);
    }
    
    return $ok;
  }

  /**
   * Store/update the alive user status somewhere
   * The default File container will just touch (update the date) the nickname file.
   * @param $chan where to update the nick, if null then update the server nick
   * @param $nick nickname to update (raw nickname)
   */
  function updateNick($chan, $nick)
  {
    // retrive the nickid to update
    $nickid = $this->getNickId($nick);
    if ($nickid == "undefined") return false;

    // update the user metadata
    $this->_registerUserMeta($nickid, $chan);
    
    $c =& $this->c;
    $there = false;
    
    $nick_dir = ($chan != NULL) ?
      $c->container_cfg_channel_dir."/".$this->_encode($chan)."/nicknames" :
      $c->container_cfg_server_dir."/nicknames";
    if (!is_dir($nick_dir)) mkdir_r($nick_dir);
    
    // update my online status file
    $nickid_filename = $nick_dir."/".$nickid; //$this->_encode($nick);
    if (file_exists($nickid_filename)) $there = true;
    @touch($nickid_filename);
    @chmod($nickid_filename, 0700); 

    if ($c->debug) pxlog("updateNick - nickname file updated: chan=".($chan==NULL?"SERVER":$chan)." nickid=".$nickid, "chat", $c->getId());        
    
    // append the nickname to the cache list
    $_chan = ($chan == NULL) ? "SERVER" : $chan;
    $id = $this->isNickOnline($chan, $nick);
    if ($id < 0)
    {
      $this->_users[$_chan]["nickid"][]    = $nickid;
      $this->_users[$_chan]["timestamp"][] = filemtime($nickid_filename);
    }
    else
    {
      // just update the timestamp if the nickname is allready present in the cached list
      $this->_users[$_chan]["timestamp"][$id] = filemtime($nickid_filename);
    }
    
    return $there;
  }

  /**
   * Change the user' nickname
   * Notice: the caller will just call this function one time, this function must take care to update if necessary all channels the user joined
   * @param $newnick
   * @param $oldnick
   * @return true on success, false on failure
   */
  function changeNick($newnick, $oldnick)
  {
    $oldnickid = $this->getNickId($oldnick);
    $newnickid = $this->getNickId($newnick);
    if ($oldnickid == "undefined") return false; // the oldnick must be connected
    if ($newnickid != "undefined") return false; // the newnick must not be inuse

    $this->rmMeta("nickid", "fromnickname", $oldnick); // remove the oldnickname -> oldnickid association
    $this->setMeta($newnick, "nickname", "fromnickid", $oldnickid);
    $this->setMeta($oldnickid, "nickid", "fromnickname", $newnick);

    /*
    $c =& $this->c;
    $nick_dir = ($chan != NULL) ?
      $c->container_cfg_channel_dir."/".$this->_encode($chan)."/nicknames" :
      $c->container_cfg_server_dir."/nicknames";
    //    $newnickid_filename = $nick_dir."/".$this->_encode($newnick);
    $oldnickid_filename = $nick_dir."/".$oldnickid; //$this->_encode($oldnick);
        
    $ok = @rename($oldnick_filename, $newnick_filename);
    */

    // update the nick cache list
    
    //if($ok)
    /*
    {
      $_chan = ($chan == NULL) ? "SERVER" : $chan;
      $id = $this->isNickOnline($chan, $oldnick);
      if ($id >= 0)
      {
        $this->_users[$_chan][$id]["nick"]      = $newnick;
        $this->_users[$_chan][$id]["timestamp"] = filemtime($newnick_filename);
      }
    }
    */

    return true;
  }

  /**
   * Returns the nickid corresponding to the given nickname
   * The nickid is a unique id used to identify a user (generated from the browser sessionid)
   * @param $nick
   * @return string the nick id
   */
  function getNickId($nick)
  {
    $nickid = $this->getMeta("nickid", "fromnickname", $nick);
    if ($nickid == NULL) $nickid = "undefined";
    return $nickid;
  }

  /**
   * Returns the nickname corresponding the the given nickid
   * @param $nickid
   * @return string the corresponding nickname
   */
  function getNickname($nickid)
  {
    $nick = $this->getMeta("nickname", "fromnickid", $nickid);
    if ($nick == NULL) $nick = "";
    return $nick;
  }

  /**
   * Remove (disconnect/quit) the timeouted nickname from the server or from a channel
   * Notice: this function must remove all nicknames which are not uptodate from the given channel or from the server
   * @param $chan if NULL then check obsolete nick on the server, otherwise just check obsolete nick on the given channel
   * @param $timeout
   * @return array("nickid"=>array("nickid1", ...),"timestamp"=>array(timestamp1, ...)) contains all disconnected nickids and there timestamp
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
    $online_user  = array();
    $dir_handle = opendir($nick_dir);
    while (false !== ($file = readdir($dir_handle)))
    {
      if ($file == "." || $file == "..") continue; // skip . and .. generic files
      $f_time = filemtime($nick_dir."/".$file);
      if (time() > ($f_time+$timeout/1000) ) // user will be disconnected after 'timeout' secondes of inactivity
      {
        $deleted_user["nick"][]      = $this->getNickname($file);
        $deleted_user["nickid"][]    = $file;
        $deleted_user["timestamp"][] = $f_time;
        @unlink($nick_dir."/".$file); // disconnect expired user
      }
      else
      {
        // optimisation: cache user list for next getOnlineNick call
        $online_user["nickid"][]    = $file;
        $online_user["timestamp"][] = $f_time;
      }
    }

    // remove the user metadata if he is disconnected from the server
    if (isset($deleted_user["nickid"]) && count($deleted_user["nickid"])>0)
    {
      foreach($deleted_user["nickid"] as $du_nid)
      {
	$du_nickid = $du_nid;
	$du_nickname = $this->getNickname($du_nid);

        $this->_unregisterUserMeta($du_nickid, $chan);

        /*
	// decrement the nick references and kill the metadata if not more references is found
	// (used to know when the nick is really disconnected)
	$nick_ref = $this->getMeta("references", $du_nickid);
	if ($nick_ref == NULL || !is_numeric($nick_ref)) $nick_ref = 0;
	$nick_ref--;
	if ($nick_ref <= 0)
	{
	  $this->rmMeta("nickid",   "fromnickname", $du_nickname);
	  $this->rmMeta("nickname", "fromnickid",   $du_nickid);
	  $this->rmMeta("references", $du_nickid); // destroy also the reference counter (by default its value is 0)
	}
	else
	  $this->setMeta($nick_ref, "references", $du_nickid);
        */
      }
    }
    
    // cache the updated user list
    $_chan = ($chan == NULL) ? "SERVER" : $chan;
    $this->_users[$_chan] =& $online_user;
    
    return $deleted_user;
  }

  /**
   * Returns the nickname list on the given channel or on the whole server
   * @param $chan if NULL then returns all connected user, otherwise just returns the channel nicknames
   * @return array("nickid"=>array("nickid1", ...),"timestamp"=>array(timestamp1, ...)) contains the nickid list with the associated timestamp (laste update time)
   */
  function getOnlineNick($chan)
  {
    // return the cached user list if it exists
    $_chan = ($chan == NULL) ? "SERVER" : $chan;
    if (isset($this->_users[$_chan]) && is_array($this->_users[$_chan]))
      return $this->_users[$_chan];
   
    $c =& $this->c;

    $nick_dir = ($chan != NULL) ?
      $c->container_cfg_channel_dir."/".$this->_encode($chan)."/nicknames" :
      $c->container_cfg_server_dir."/nicknames";
    if (!is_dir($nick_dir)) mkdir_r($nick_dir);
    
    $online_user = array();
    $dir_handle = opendir($nick_dir);
    while (false !== ($file = readdir($dir_handle)))
    {
      if ($file == "." || $file == "..") continue; // skip . and .. generic files
      $online_user["nickid"][]    = $file;
      $online_user["timestamp"][] = filemtime($nick_dir."/".$file);
    }

    // cache the user list
    $this->_users[$_chan] =& $online_user;

    return $this->_users[$_chan];
  }
  
  /**
   * Returns returns a positive number if the nick is online
   * @param $chan if NULL then check if the user is online on the server, otherwise check if the user has joined the channel
   * @return -1 if the user is off line, a positive (>=0) if the user is online
   */
  function isNickOnline($chan, $nick)
  {
    // @todo optimise with this piece of code
    /*
    $nickid = $this->getNickId($nick);
    if ($nickid == "undefined") return false;

    $nick_dir = ($chan != NULL) ?
      $c->container_cfg_channel_dir."/".$this->_encode($chan)."/nicknames" :
      $c->container_cfg_server_dir."/nicknames";
    if (!is_dir($nick_dir)) mkdir_r($nick_dir);

    return file_exists($nick_dir."/".$nickid);
    */

    $nickid = $this->getNickId($nick);
    
    // get the nickname list
    $_chan = ($chan == NULL) ? "SERVER" : $chan;
    $online_user = isset($this->_users[$_chan]) ? $this->_users[$_chan] : $this->getOnlineNick($chan);
    
    $uid = 0;
    $isonline = false;
    if (!isset($online_user["nickid"])) return -1;
    while($uid < count($online_user["nickid"]) && !$isonline)
    {
      if ($online_user["nickid"][$uid] == $nickid)
        $isonline = true;
      else
        $uid++;
    }
    if ($isonline)
      return $uid;
    else
      return -1;
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
    if (count($errors) > 0) return $errors; // an error occurs ?

    // request a unique id for this new message
    $msg_id = $this->_requestMsgId($chan);
    if (is_array($msg_id)) return $msg_id; // an error occurs ?
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

    // delete the obsolete message
    $old_msg_id = $msg_id - $c->max_msg - 20;
    if ($old_msg_id > 0 && file_exists($msg_dir."/".$old_msg_id))
      @unlink($msg_dir."/".$old_msg_id);
    
    return $msg_id;
  }

  /**
   * Read the last posted commands from a channel or from the server
   * Notice: the returned array must be ordered by id
   * @param $chan if NULL then read from the server, otherwise read from the given channel
   * @param $from_id read all message with a greater id
   * @return array() contains the command list
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
        if ($file > $new_from_id)
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
        $datalist[$data["id"]] = $data;
      }
    }
    ksort($datalist);
    
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
   * @return mixed the value assigned to the key, NULL if not found
   */
  function getMeta($key, $type, $subtype = NULL)
  {
    // encode parameters
    $enc_key     = $this->_encode($key);
    $enc_type    = $this->_encode($type);
    $enc_subtype = ($subtype == NULL) ? "NULL" : $this->_encode($subtype);
    if (isset($this->_meta[$enc_type][$enc_subtype][$enc_key]))
      return $this->_meta[$enc_type][$enc_subtype][$enc_key];
    
    // read data from metadata file
    $c =& $this->c;
    $dir_base = $c->container_cfg_meta_dir;
    $dir = $dir_base."/".$enc_type.($enc_subtype == "NULL" ? "" : "/".$enc_subtype);
    $filename = $dir."/".$enc_key;
    $ret = @file_get_contents($filename);
    if ($ret == false) $ret = NULL;

    // store the result in the cache
    $this->_meta[$enc_type][$enc_subtype][$enc_key] = $ret;
    
    return $ret;
  }
  
  /**
   * Write a meta data value identified by a key
   * As an example the default file container store metadata into metadata/type/subtype/hash(key)
   * @param $key is the index which identify a metadata
   * @param $value is the value associated to the key
   * @param $type is used to "group" some metadata
   * @param $subtype is used to "group" precisely some metadata, use NULL to ignore it
   * @return true on success, false on error
   */
  function setMeta($value, $key, $type, $subtype = NULL)
  {
    // encode parameters
    $enc_key     = $this->_encode($key);
    $enc_type    = $this->_encode($type);
    $enc_subtype = ($subtype == NULL) ? "NULL" : $this->_encode($subtype);
    
    // create directories
    $c =& $this->c;
    $dir_base = $c->container_cfg_meta_dir;
    $dir = $dir_base."/".$enc_type.($enc_subtype == "NULL" ? "" : "/".$enc_subtype);
    if (!is_dir($dir)) mkdir_r($dir);

    // create or replace metadata file
    $filename = $dir."/".$enc_key;
    $ret = @file_put_contents($filename, $value);

    // store the value in the cache
    if ($ret) $this->_meta[$enc_type][$enc_subtype][$enc_key] = $value;

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
   * @return true on success, false on error
   */
  function rmMeta($key, $type, $subtype = NULL)
  {
    $c =& $this->c;
    
    // encode parameters
    $enc_key     = ($key == NULL) ? "NULL" : $this->_encode($key);
    $enc_type    = $this->_encode($type);
    $enc_subtype = ($subtype == NULL) ? "NULL" : $this->_encode($subtype);

    // rm data from metadata file
    $dir_base = $c->container_cfg_meta_dir;
    $dir = $dir_base."/".$enc_type.($enc_subtype == "NULL" ? "" : "/".$enc_subtype);
    $ret = true;
    if ($enc_key == "NULL")
    {
      // remove all keys (the complete directory)
      @rm_r($dir);

      // remove the cached data
      unset($this->_meta[$enc_type][$enc_subtype]);
    }
    else
    {
      // just remove one key
      $filename = $dir."/".$enc_key;
      $ret = @unlink($filename);
      
      // remove the cached data
      if (isset($this->_meta[$enc_type][$enc_subtype][$enc_key]))
        unset($this->_meta[$enc_type][$enc_subtype][$enc_key]);
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
    @rm_r($dir);
    // empty the cache
    $this->_meta = array();
    $this->_users = array("nickid"    => array(),
                          "timestamp" => array());
  }

  function _registerUserMeta($nickid, $chan)
  {
    $c =& $this->c;
    // create or update the nickname references (used to know when the nick is really disconnected)
    if ($chan == NULL) $chan = "SERVER";
    $ref = $this->getMeta("references", $nickid);
    if ($ref == NULL)
      $ref = array();
    else
      $ref = explode(';',$ref);
    if ($c->debug) pxlog("registerUserMeta -> ref=".implode(';',$ref), "chat", $c->getId());
    if (in_array($chan,$ref))
      return;
    else
      $ref[] = $chan;
    $ref = implode(';',$ref);
    $this->setMeta($ref, "references", $nickid);
  }

  function _unregisterUserMeta($nickid, $chan)
  {
    $c =& $this->c;
    // decrement the nick references and kill the metadata if not more references is found
    // (used to know when the nick is really disconnected)
    if ($chan == NULL) $chan = "SERVER";
    $nickname = $this->getNickname($nickid);
    $ref = $this->getMeta("references", $nickid);
    if ($ref == NULL) $ref = '';
    $ref = explode(';',$ref);
    $ref = array_diff($ref, array($chan));
    if (count($ref) == 0)
    {
      $this->rmMeta("nickid",   "fromnickname", $nickname);
      $this->rmMeta("nickname", "fromnickid",   $nickid);
      $this->rmMeta("references", $nickid); // destroy also the reference counter
      if ($c->debug) pxlog("_unregisterUserMeta -> destroy!", "chat", $c->getId());
    }
    else
    { 
      $ref = implode(';',$ref);
      $this->setMeta($ref, "references", $nickid);
      if ($c->debug) pxlog("_unregisterUserMeta -> ref=".$ref, "chat", $c->getId());
    }
  }

  
  /**
   * Return a unique id. Each time this function is called, the last id is incremented.
   * used internaly
   * @private
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
    if (count($errors) > 0) return $errors;
    
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

  /**
   * Used to encode UTF8 strings to ASCII filenames
   * @private
   */  
  function _encode($str)
  {
    return base64_encode(urlencode($str));
  }
  
  /**
   * Used to decode ASCII filenames to UTF8 strings
   * @private
   */  
  function _decode($str)
  {
    return urldecode(base64_decode($str));
  }
}

?>
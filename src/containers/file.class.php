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

    if ($chan == NULL) $chan = 'SERVER';

    $this->setMeta2("nickid-to-metadata",  $nickid, 'nick', $nick);
    $this->setMeta2("metadata-to-nickid",  'nick', $this->_encode($nick), $nickid);

    $this->setMeta2("nickid-to-channelid", $nickid, $this->_encode($chan));
    $this->setMeta2("channelid-to-nickid", $this->_encode($chan), $nickid);

    // update the SERVER channel
    $this->updateNick($nickid);
    
    return true;
  }

  /**
   * Remove (disconnect/quit) the nickname from the server or from a channel
   * Notice: The caller must take care to update all joined channels.
   * @param $chan if NULL then remove the user on the server (disconnect), otherwise just remove the user from the given channel (quit)
   * @param $nick the nickname to remove
   * @return true if the nickname was correctly removed
   */
  function removeNick($chan, $nickid)
  {
    if ($chan == NULL) $chan = 'SERVER';

    $ret = $this->getMeta2("channelid-to-nickid", $this->_encode('SERVER'), $nickid);
    $timestamp = $ret["timestamp"][0];
    
    $deleted_user = array();
    $deleted_user["nick"][]      = $this->getNickname($nickid);
    $deleted_user["nickid"][]    = $nickid;
    $deleted_user["timestamp"][] = $timestamp;


    // @todo ne supprimer l'utilisateur que du channel donne en parametres
    //       car la commande /leave va simplement supprimer l'utilisateur du channel courant
    //       il faut par contre faire un test sur les channels de l'utilisateur et dans le cas ou l'utilisateur
    //       est deconnecte du dernier channel (il se peut que ce soit SERVER) alors on supprime ses metadata.

    //       il faudrait egalement adapter removeObsoleteNick pour qu'elle appel N fois removeNick
    //       N etant le nombre de channel de l'utilisateur. Ainsi l'utilisateur dera vraiment deconnecte


    
    // get the user's disconnected channels
    $channels = array();
    $ret2 = $this->getMeta2("nickid-to-channelid",$nickid);
    foreach($ret2["value"] as $v)
      $channels[] = $this->_decode($v);
    $deleted_user["channels"][]  = $channels;

    // get the user nickname
    $nick = $this->getNickname($nickid);
    // loop on user channels
    foreach($channels as $ch)
    {
      // remove the nickname to nickid correspondance
      $this->rmMeta2("metadata-to-nickid", 'nick', $this->_encode($nick));
      // remove disconnected nickname metadata
      $this->rmMeta2("nickid-to-metadata", $nickid);
      // remove the nickid from the channel list
      $this->rmMeta2("channelid-to-nickid", $this->_encode($ch), $nickid);
    }
    return $deleted_user;
  }

  /**
   * Store/update the alive user status somewhere
   * The default File container will just touch (update the date) the nickname file.
   * @param $chan where to update the nick, if null then update the server nick
   * @param $nick nickname to update (raw nickname)
   */
  function updateNick($nickid)
  {
    $c =& $this->c;

    $chan = 'SERVER';

    $this->setMeta2("nickid-to-channelid", $nickid, $this->_encode($chan));
    $this->setMeta2("channelid-to-nickid", $this->_encode($chan), $nickid);
    return true;
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
    $c =& $this->c;

    $oldnickid = $this->getNickId($oldnick);
    $newnickid = $this->getNickId($newnick);
    if ($oldnickid == "") return false; // the oldnick must be connected
    if ($newnickid != "") return false; // the newnick must not be inuse
    
    // remove the oldnick to oldnickid correspondance
    $this->rmMeta2("metadata-to-nickid", 'nick', $this->_encode($oldnick));

    // update the nickname
    $this->setMeta2("nickid-to-metadata", $oldnickid, 'nick', $newnick);
    $this->setMeta2("metadata-to-nickid", 'nick', $this->_encode($newnick), $oldnickid);
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
    $nickid = $this->getMeta2("metadata-to-nickid", 'nick', $this->_encode($nick), true);
    $nickid = isset($nickid["value"][0]) ? $nickid["value"][0] : "";
    return $nickid;
  }

  /**
   * Returns the nickname corresponding the the given nickid
   * @param $nickid
   * @return string the corresponding nickname
   */
  function getNickname($nickid)
  {
    $nick = $this->getMeta2("nickid-to-metadata", $nickid, 'nick', true);
    $nick = isset($nick["value"][0]) ? $nick["value"][0] : "";
    return $nick;
  }

  /**
   * Remove (disconnect/quit) the timeouted nickname from the server or from a channel
   * Notice: this function must remove all nicknames which are not uptodate from the given channel or from the server
   * @param $chan if NULL then check obsolete nick on the server, otherwise just check obsolete nick on the given channel
   * @param $timeout
   * @return array("nickid"=>array("nickid1", ...),"timestamp"=>array(timestamp1, ...)) contains all disconnected nickids and there timestamp
   */
  function removeObsoleteNick($timeout)
  {
    $c =& $this->c;

    $chan = 'SERVER';

    $deleted_user = array('nick'=>array(),
                          'nickid'=>array(),
                          'timestamp'=>array(),
                          'channels'=>array());
    $ret = $this->getMeta2("channelid-to-nickid", $this->_encode($chan));
    for($i = 0; $i<count($ret['timestamp']); $i++)
    {
      $timestamp = $ret['timestamp'][$i];
      $nickid    = $ret['value'][$i];
      if (time() > ($timestamp+$timeout/1000) ) // user will be disconnected after 'timeout' secondes of inactivity
      {
        $du = $this->removeNick($nickid);
        $deleted_user["nick"]      = array_merge($deleted_user["nick"],      $du["nick"]);
        $deleted_user["nickid"]    = array_merge($deleted_user["nickid"],    $du["nickid"]);
        $deleted_user["timestamp"] = array_merge($deleted_user["timestamp"], $du["timestamp"]);
        $deleted_user["channels"]  = array_merge($deleted_user["channels"],  $du["channels"]);
      }
    }

    return $deleted_user;
  }

  /**
   * Returns the nickname list on the given channel or on the whole server
   * @param $chan if NULL then returns all connected user, otherwise just returns the channel nicknames
   * @return array("nickid"=>array("nickid1", ...),"timestamp"=>array(timestamp1, ...)) contains the nickid list with the associated timestamp (laste update time)
   */
  function getOnlineNick($chan)
  {
    $c =& $this->c;
    
    if ($chan == NULL) $chan = 'SERVER';

    $online_user = array();
    $ret = $this->getMeta2("channelid-to-nickid", $this->_encode($chan));
    for($i = 0; $i<count($ret['timestamp']); $i++)
    {
      $nickid = $ret['value'][$i];

      // get timestamp from the SERVER channel
      $timestamp = $this->getMeta2("channelid-to-nickid", $this->_encode('SERVER'), $nickid);
      $timestamp = $timestamp['timestamp'][0];

      $online_user["nick"][]      = $this->getNickname($nickid);
      $online_user["nickid"][]    = $nickid;
      $online_user["timestamp"][] = $timestamp;
    }
    return $online_user;
  }
  
  /**
   * Returns returns a positive number if the nick is online
   * @param $chan if NULL then check if the user is online on the server, otherwise check if the user has joined the channel
   * @return -1 if the user is off line, a positive (>=0) if the user is online
   */
  function isNickOnline($chan, $nickid)
  {
    if ($chan == NULL) $chan = 'SERVER';

    $ret = $this->getMeta2("channelid-to-nickid", $this->_encode($chan));
    for($i = 0; $i<count($ret['timestamp']); $i++)
    {
      if ($ret['value'][$i] == $nickid) return $i;
    }
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
    if ($chan == NULL) $chan = 'SERVER';
    
    $msgid = $this->_requestMsgId($chan);

    // format message
    $data = "\n";
    $data .= $msgid."\t";
    $data .= date("d/m/Y")."\t";
    $data .= date("H:i:s")."\t";
    $data .= $nick."\t";
    $data .= $cmd."\t";
    $data .= $param;

    // write message
    $this->setMeta2("channelid-to-msg", $this->_encode($chan), $msgid, $data);

    // delete the obsolete message
    $old_msgid = $msgid - $c->max_msg - 20;
    if ($old_msgid > 0)
      $this->rmMeta2("channelid-to-msg", $this->_encode($chan), $old_msgid);

    return $msgid;
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
    if ($chan == NULL) $chan = 'SERVER';

    // read new messages id
    $new_msgid_list = array();
    $new_from_id = $from_id;   
    $msgid_list = $this->getMeta2("channelid-to-msg", $this->_encode($chan));
    for($i = 0; $i<count($msgid_list["value"]); $i++)
    {
      $msgidtmp = $msgid_list["value"][$i];
      
      if ($msgidtmp > $from_id)
      {
        if ($msgidtmp > $new_from_id) $new_from_id = $msgidtmp;
        $new_msgid_list[] = $msgidtmp;
      }
    }

    // read messages content and parse content
    $datalist = array();
    foreach ( $new_msgid_list as $mid )
    {
      $line = $this->getMeta2("channelid-to-msg", $this->_encode($chan), $mid, true);
      $line = $line["value"][0];
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
    if ($chan == NULL) $chan = 'SERVER';
    
    $lastmsgid = $this->getMeta2("channelid-to-msgid", $this->_encode($chan), 'lastmsgid', true);
    if (count($lastmsgid["value"]) == 0)
      $lastmsgid = 0;
    else
      $lastmsgid = $lastmsgid["value"][0];
    return $lastmsgid;
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
    //    $this->_meta = array();
    //    $this->_users = array("nickid"    => array(),
    //                          "timestamp" => array());
  }

  
  /**
   * Return a unique id. Each time this function is called, the last id is incremented.
   * used internaly
   * @private
   */ 
  function _requestMsgId($chan)
  {
    if ($chan == NULL) $chan = 'SERVER';
    
    $lastmsgid = $this->getLastId($chan);
    $lastmsgid++;
    $this->setMeta2("channelid-to-msgid", $this->_encode($chan), 'lastmsgid', $lastmsgid);
    
    return $lastmsgid;
  }

  /**
   * Used to encode UTF8 strings to ASCII filenames
   * @private
   */  
  function _encode($str)
  {
    return urlencode($str);
    return base64_encode(urlencode($str));
  }
  
  /**
   * Used to decode ASCII filenames to UTF8 strings
   * @private
   */  
  function _decode($str)
  {
    return urldecode($str);
    return urldecode(base64_decode($str));
  }













  /**
   * Write a meta data value identified by a group / subgroup / leaf [with a value]
   * @return 1 if the leaf allready existed, 0 if the leaf has been created
   */
  function setMeta2($group, $subgroup, $leaf, $leafvalue = NULL)
    //                    value, $key, $type, $subtype = NULL)
  {
    // create directories
    $c =& $this->c;
    $dir_base = $c->container_cfg_meta_dir;
    $dir = $dir_base.'/'.$group.'/'.$subgroup;
    if (!is_dir($dir)) mkdir_r($dir);
    
    // create or replace metadata file
    $leaffilename = $dir."/".$leaf;
    $leafexists = file_exists($leaffilename);
    if ($leafvalue == NULL)
    {
      if (file_exists($leaffilename) &&
	  filesize($leaffilename)>0) unlink($leaffilename);
      touch($leaffilename);
    }
    else
    {
      file_put_contents($leaffilename, $leafvalue);
    }

    // store the value in the memory cache
    //@todo
    //    $this->_meta[$enc_type][$enc_subtype][$enc_key] = $value;

    if ($leafexists)
      return 1; // value overwritten
    else
      return 0; // value created
  }

  
  /**
   * Read meta data identified by a group [/ subgroup [/ leaf]]
   * @return ...
   */
  function getMeta2($group, $subgroup = null, $leaf = null, $withleafvalue = false)
  //($key, $type, $subtype = NULL)
  {
    // @todo read the value from the memory cache
    //if (isset($this->_meta[$enc_type][$enc_subtype][$enc_key]))
    //      return $this->_meta[$enc_type][$enc_subtype][$enc_key];
    
    // read data from metadata file
    $ret = array();
    $ret["timestamp"] = array();
    $ret["value"]     = array();
    $c =& $this->c;
    $dir_base = $c->container_cfg_meta_dir;

    $dir = $dir_base.'/'.$group;

    if ($subgroup == NULL)
    {
      if (is_dir($dir))
      {
        $dh = opendir($dir);
        while (false !== ($file = readdir($dh)))
        {
          if ($file == "." || $file == "..") continue; // skip . and .. generic files
          $ret["timestamp"][] = filemtime($dir.'/'.$file);
          $ret["value"][]     = $file;
        }
      }
      return $ret;
    }
    
    $dir .= '/'.$subgroup;

    if ($leaf == NULL)
    {
      if (is_dir($dir))
      {
        $dh = opendir($dir);
        $ret = array();
        while (false !== ($file = readdir($dh)))
        {
          if ($file == "." || $file == "..") continue; // skip . and .. generic files
          $ret["timestamp"][] = filemtime($dir.'/'.$file);
          $ret["value"][]     = $file;
        }
      }
      return $ret;
    }
    
    $leaffilename = $dir."/".$leaf;

    if (!file_exists($leaffilename)) return $ret;
    if ($withleafvalue)
    {
      $ret["value"][] = file_get_contents($leaffilename);
    }
    $ret["timestamp"][] = filemtime($leaffilename);

    // @todo
    // store the result in the memory cache
    //$this->_meta[$enc_type][$enc_subtype][$enc_key] = $ret;
    
    return $ret;
  }

  
  /**
   * Remove a meta data
   * @return ...
   */
  function rmMeta2($group, $subgroup = null, $leaf = null)
  //($key, $type, $subtype = NULL)
  {
    $c =& $this->c;



    // read data from metadata file
    $c =& $this->c;
    $dir_base = $c->container_cfg_meta_dir;

    $dir = $dir_base.'/'.$group;

    if ($subgroup == NULL)
    {
      rm_r($dir);
      return true;
    }
    
    $dir .= '/'.$subgroup;

    if ($leaf == NULL)
    {
      rm_r($dir);
      return true;
    }
    
    $leaffilename = $dir."/".$leaf;
    
    if (!file_exists($leaffilename)) return false;
    unlink($leaffilename);
    return true;
  }

  
}

?>
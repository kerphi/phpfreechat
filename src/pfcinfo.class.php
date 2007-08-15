<?php

require_once dirname(__FILE__)."/pfcglobalconfig.class.php";
require_once dirname(__FILE__)."/pfci18n.class.php";
require_once dirname(__FILE__)."/commands/join.class.php";

class pfcInfo
{
  var $c = null;
  var $errors = array();
  
  function pfcInfo( $serverid, $data_private_path = "" )
  {
    // check if the cache already exists
    // if it doesn't exists, just stop the process
    // because we can't initialize the chat from the external API
    if ($data_private_path == "") $data_private_path = dirname(__FILE__)."/../data/private";
    $cachefile = pfcGlobalConfig::_GetCacheFile( $serverid, $data_private_path );
    if (!file_exists($cachefile))
    {
      $this->errors[] = _pfc("Error: the cached config file doesn't exists");
      return;
    }
    // then intitialize the pfcglobalconfig
    $params = array();
    $params["serverid"]          = $serverid;
    $params["data_private_path"] = $data_private_path;
    $this->c =& pfcGlobalConfig::Instance($params);    
  }
  
  function free()
  {
    // free the pfcglobalconfig instance
    pfcGlobalConfig::Instance(array(), true);
  }
  
  /**
   * @return array(string) a list of errors
   */
  function getErrors()
  {
    if ($this->c != null)
      return array_merge($this->errors, $this->c->getErrors());
    else
      return $this->errors;
  }
  function hasErrors()
  {
    return count($this->getErrors()) > 0;
  }
  
  /**
   * @param $channel the returned list is the list of nicknames present on the given channel (NULL for the whole server)
   * @param $timeout is the time to wait before a nickname is considered offline
   * @return array(string) a list of online nicknames
   */
  function getOnlineNick($channel = NULL, $timeout = 20)
  {
    if ($this->hasErrors()) return array();
    
    $ct =& pfcContainer::Instance();
    
    if ($channel != NULL) $channel = pfcCommand_join::GetRecipient($channel);
    
    $res = $ct->getOnlineNick($channel);
    $users = array();
    if (isset($res["nickid"]))
    {
      for($i = 0; $i < count($res["nickid"]); $i++)
      {
        if (time()-$timeout < $res["timestamp"][$i])
          $users[] = $ct->getNickname($res["nickid"][$i]);
      }
    }
    return $users;
  }

  /**
   * Return the last $nb message from the $channel room.
   * The messages format is very basic, it's raw data (it will certainly change in future)
   */
  function getLastMsg($channel, $nb)
  {
    if ($this->hasErrors()) return array();

    // to be sure the $nb params is a positive number
    if ( !( $nb >= 0 ) ) $nb = 10;
    
    // to get the channel recipient name
    // @todo must use another function to get a private message last messages
    $channel = pfcCommand_join::GetRecipient($channel);
    
    $ct          =& pfcContainer::Instance();
    $lastmsg_id  = $ct->getLastId($channel);
    $lastmsg_raw = $ct->read($channel, $lastmsg_id-$nb);
    return $lastmsg_raw;
  }

  /**
   * Rehash the server config (same as /rehash command)
   * Usefull to take into account new server's parameters
   */
  function rehash()
  {
    if ($this->hasErrors()) return false;

    $destroyed = $this->c->destroyCache();
    return $destroyed;
  }
}

?>
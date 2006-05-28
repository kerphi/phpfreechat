<?php

require_once dirname(__FILE__)."/pfcglobalconfig.class.php";

class pfcUserConfig
{
  var $nick;
  var $channels;
  var $privmsg;
  var $active;
  
  var $timeout;
  var $sessionid;
  
  //  var $is_init = false; // used internaly to know if the chat config is initialized
  //  var $errors = array();
  
  function pfcUserConfig()
  {
    // start the session : session is used for locking purpose and cache purpose
    session_name( "phpfreechat" );
    if (isset($_GET["init"])) unset($_COOKIE[session_name()]);
    if(session_id() == "") session_start();
    
    //    echo "pfcUserConfig()<br>";

    $this->sessionid = session_id();

    // user parameters are cached in sessions
    $this->_getParam("nick");
    if (!isset($this->nick)) $this->_setParam("nick","");
    $this->_getParam("active");
    if (!isset($this->active)) $this->_setParam("active",false);   
    $this->_getParam("channels");
    if (!isset($this->channels)) $this->_setParam("channels",array());
    $this->_getParam("privmsg");
    if (!isset($this->privmsg)) $this->_setParam("privmsg",array());
  }

  function &_getParam($p)
  {
    if (!isset($this->$p))
    {
      $c =& pfcGlobalConfig::Instance();
      $sessionid       = "pfcuserconfig_".$c->getId();
      $sessionid_param = $sessionid."_".$p;
      if (isset($_SESSION[$sessionid_param]))
        $this->$p = $_SESSION[$sessionid_param];
      else
        $this->$p = $c->$p; // take the default parameter from the global config
    }
    return $this->$p;
  }

  function _setParam($p, $v)
  {
    $c =& pfcGlobalConfig::Instance();
    $sessionid       = "pfcuserconfig_".$c->getId();
    $sessionid_param = $sessionid."_".$p;
    $_SESSION[$sessionid_param] = $v;
    $this->$p = $v;
  }
  
  function &Instance()
  {
    static $i;
    
    if (!isset($i))
    {
      $i = new pfcUserConfig();
    }
    return $i;
  }
  /*
  function init()
  {
    //    echo "init()<br>";
    $ok = true;
    
    $c =& pfcGlobalConfig::Instance();
    if ($c)
    {
      $this->nick    = $c->nick;
      $this->timeout = $c->timeout;
    }
    else
    {
      $this->errors[] = "pfcGlobalConfig must be instanciated first";
      $ok = false;
    }
    
    $this->is_init = $ok;
  }

  function isInit()
  {
    return $this->is_init;
  }
  
  function &getErrors()
  {
    return $this->errors;
  }
  */

  /*
  function getCacheFile()
  {
    $c =& pfcGlobalConfig::Instance();
    $cachefile = "";
    if ($this->nick != "")
      $cachefile = $c->data_private_path."/cache/".$c->prefix."userconfig_".$c->getId()."_".md5($this->nick);
    //    echo "getCacheFile() = '$cachefile'<br>";
    return $cachefile;
  }
  */
  /**
   * save the pfcUserConfig object into cache if it doesn't exists yet
   * else restore the old pfcConfig object
   */
  /*
  function synchronizeWithCache()
  {
    //    echo "synchronizeWithCache()<br>";
    $c =& pfcGlobalConfig::Instance();
    $cachefile = $this->getCacheFile();
    if ($c->debug) pxlog("pfcUserConfig::synchronizeWithCache: cachefile=".$cachefile, "chatconfig", $c->getId());
    if (file_exists($cachefile))
    {
      echo "synchronizeWithCache():exists<br>";
      $pfc_configvar = unserialize(file_get_contents($cachefile));
      foreach($pfc_configvar as $key => $val)
	$this->$key = $val;
      if ($c->debug) pxlog("pfcUserConfig::synchronizeWithCache: restore pfcUserConfig from cache", "chatconfig", $c->getId());
    }
    else
    {
      //      echo "synchronizeWithCache():!exists<br>";
      if (!$this->isInit())
        $this->init();
      $errors =& $this->getErrors();
      if (count($errors) > 0)
      {
        echo "<ul>"; foreach( $errors as $e ) echo "<li>".$e."</li>"; echo "</ul>";
        exit;
      }
      // save the validated config in cache
      $this->saveInCache();
    }
  }
  */
  
  function saveInCache()
  {
    //    echo "saveInCache()<br>";
    $c =& pfcGlobalConfig::Instance();

    // do not save anything as long as nickname is not assigned
    if ($this->active && $this->nick != "")
    {
      $this->_setParam("nick",$this->nick);
      $this->_setParam("active",$this->active);
      $this->_setParam("channels",$this->channels);
      $this->_setParam("privmsg",$this->privmsg);

      /*

      // save nickname and active status into sessions
      $sessionid        = $c->prefix."pfcuserconfig_".$c->getId();
      $sessionid_nick   = $sessionid."_nick";
      $sessionid_active = $sessionid."_active";
      $_SESSION[$sessionid_nick] = $this->nick;
      $_SESSION[$sessionid_active] = $this->active;
      */
      
      
      // @todo
      // save the whole object into cache
      /*
      $cachefile = $this->getCacheFile();
      if ($cachefile == "")
        die("Error: cachefile should not be null!");
      file_put_contents($cachefile, serialize(get_object_vars($this)));
      if ($c->debug) pxlog("pfcUserConfig::saveInCache: nick=".$this->nick, "chatconfig", $c->getId());
      */
    }
  }
}

?>

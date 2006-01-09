<?php

require_once dirname(__FILE__)."/../debug/log.php";
require_once dirname(__FILE__)."/phpxchattools.class.php";

class phpXChatConfig
{
  var $nick           = "";
  var $id             = 0;
  var $default_params = array();
  var $errors         = array();
  var $is_init        = false;
  var $smileys        = array();
  
  function phpXChatConfig( $params = array() )
  {
    $this->default_params["title"]               = "My phpXChat";
    $this->default_params["channel"]             = preg_replace("/[^a-z0-9]*/","",strtolower($this->default_params["title"]));
    $this->default_params["nick"]                = "";
    $this->default_params["frozen_nick"]         = false;
    $this->default_params["skip_optional_check"] = true;
    $this->default_params["max_nick_len"]        = 15;
    $this->default_params["max_text_len"]        = 250;
    $this->default_params["refresh_delay"]       = 5000; // in mili-seconds (5 seconds)
    $this->default_params["max_msg"]             = 20;
    $this->default_params["height"]              = "440px";
    $this->default_params["width"]               = "";
    $this->default_params["css_file"]            = "";
    $this->default_params["server_script"]       = "";
    $this->default_params["data_private"]        = dirname(__FILE__)."/../data/private";
    $this->default_params["data_public"]         = dirname(__FILE__)."/../data/public";
    $this->default_params["shownotice"]          = true;
    $this->default_params["debug"]               = false;
    $this->default_params["connect"]             = true;
    $this->default_params["smileytheme"]         = "default";
    $this->default_params["prefix"]              = "phpxchat_";
    $this->default_params["container_type"]      = (isset($params["container_type"]) && $params["container_type"]!="") ? $params["container_type"] : "File";

    // set defaults values
    foreach ( $this->default_params as $k => $v ) $this->$k = $v;
    
    // set user's values
    foreach ( $params as $k => $v ) $this->$k = $v;

    // choose a auto-generated channel name if user choose a title but didn't choose a channel name
    if ( !isset($params["channel"]) && isset($params["title"]) )
      $this->channel = preg_replace("/[^a-z0-9]*/","",strtolower($this->title));
    else
      $this->channel = preg_replace("/[^a-z0-9]*/","",strtolower($this->channel));

    // load default container's config
    $container =& $this->getContainerInstance();
    $container_cfg = $container->getDefaultConfig();
    foreach( $container_cfg as $k => $v )
    {
      $attr = "container_cfg_".$k;
      if (!isset($this->$attr))
        $this->$attr = $v;
    }

    $this->synchronizeWithSession();
  }

  function &Instance( $params = array() )
  {
    static $i;
    
    if (!isset($i))
      $i = new phpXChatConfig( $params );
    return $i;
  }
  
  function &getContainerInstance()
  {
    $container_classname = "phpXChat_Container_".$this->container_type;
    require_once dirname(__FILE__)."/".strtolower($container_classname).".class.php";
    $container = new $container_classname($this);
    return $container;
  }
  
  function init()
  {
    $this->errors = array();
    $ok = true;
    
    // ---
    // test data_public directory
    if ($ok && $this->data_public == "")
    {
      $ok = false;
      $this->errors[] = "cache directory must be specified";
    }
    if ($ok && is_file($this->data_public))
    {
      $ok = false;
      $this->errors[] = $this->data_public." must be a directory";
    }      
    if ($ok && !is_dir($this->data_public))
      @phpXChatTools::RecursiveMkdir($this->data_public);
    if ($ok && !is_dir(dirname($this->data_public)))
    {
      $ok = false;
      $this->errors[] = dirname($this->data_public)." can't be created";
    }      
    if ($ok && !is_writeable($this->data_public))
    {
      $ok = false;
      $this->errors[] = $this->data_public." is not writeable";
    }
    if ($ok && !is_readable($this->data_public))
    {
      $ok = false;
      $this->errors[] = $this->data_public." is not readable";
    }

    // ---
    // test data_private directory
    if ($ok && $this->data_private == "")
    {
      $ok = false;
      $this->errors[] = "cache directory must be specified";
    }
    if ($ok && is_file($this->data_private))
    {
      $ok = false;
      $this->errors[] = $this->data_private." must be a directory";
    }      
    if ($ok && !is_dir($this->data_private))
      @phpXChatTools::RecursiveMkdir($this->data_private);
    if ($ok && !is_dir(dirname($this->data_private)))
    {
      $ok = false;
      $this->errors[] = dirname($this->data_private)." can't be created";
    }      
    if ($ok && !is_writeable($this->data_private))
    {
      $ok = false;
      $this->errors[] = $this->data_private." is not writeable";
    }
    if ($ok && !is_readable($this->data_private))
    {
      $ok = false;
      $this->errors[] = $this->data_private." is not readable";
    }
    /* templates_c directory for smarty */
    $dir = $this->data_private."/templates_c";
    if ($ok && !is_dir($dir))
      @phpXChatTools::RecursiveMkdir($dir);
    if ($ok && !is_dir(dirname($dir)))
    {
      $ok = false;
      $this->errors[] = dirname($dir)." can't be created";
    }      
    if ($ok && !is_writeable($dir))
    {
      $ok = false;
      $this->errors[] = $dir." is not writeable";
    }
    if ($ok && !is_readable($dir))
    {
      $ok = false;
      $this->errors[] = $dir." is not readable";
    }
    
    // ---
    // test server file
    if ($ok && $this->server_file != "" && !file_exists($this->server_file))
    {
      $ok = false;
      $this->errors[] = $this->server_file." doesn't exist";
    }
    
    // ---
    // test container config
    if ($ok)
    {
      $container_classname = "phpXChat_Container_".$this->default_params["container_type"];
      require_once dirname(__FILE__)."/".strtolower($container_classname).".class.php";
      $container = new $container_classname($this);
      $container_errors = $container->init();
      if (count($container_errors)>0)
      {
        $this->errors = array_merge($this->errors, $container_errors);
        $ok = false;
      }
    }

    // load smileys from file
    if ($ok)
      $this->loadSmileyTheme();
    
    // do not froze nickname if it has not be specified
    if ($this->nick == "" && $this->frozen_nick)
      $this->frozen_nick = false;

    $this->is_init = $ok;
  }
  
  function isInit()
  {
    return $this->is_init;
  }
  
  function getErrors()
  {
    return $this->errors;
  }

  function loadSmileyTheme()
  {
    $theme = file(dirname(__FILE__)."/../smileys/".$this->smileytheme."/theme");
    $result = array();
    foreach($theme as $line)
    {
      if (preg_match("/^#.*/",$line))
        continue;
      else if (preg_match("/^([a-z_]*(\.gif|\.png))(.*)$/i",$line,$res))
      {
        $smiley_file = phpXChatTools::RelativePath(dirname($_SERVER["PATH_TRANSLATED"]), dirname(__FILE__).'/../smileys/'.$this->smileytheme.'/'.$res[1]);
        $smiley_str = trim($res[3])."\n";
        $smiley_str = str_replace("\n", "", $smiley_str);
        $smiley_str = str_replace("\t", " ", $smiley_str);
        $smiley_str_tab = explode(" ", $smiley_str);
        foreach($smiley_str_tab as $str)
          $result[$smiley_file][] = htmlspecialchars(addslashes($str));
      }
    }
    $this->smileys =& $result;
  }
  
  function assignToSmarty( &$smarty )
  {
    foreach ( $this->default_params as $p_k => $p_v )
      $smarty->assign($p_k, $this->$p_k);
    $smarty->assign("id", $this->getId());
    $smarty->assign("smileys", $this->smileys);
  }

  function getId()
  {
    // calculate the chat id
    if ($this->id == 0)
    {
      $spotted_atr = array();
      $spotted_atr[] = $this->title;
      $spotted_atr[] = $this->channel;
      $spotted_atr[] = $this->prefix;
      $spotted_atr[] = $this->debug;
      $spotted_atr[] = $this->connect;
      $spotted_atr[] = $this->data_public; 
      $spotted_atr[] = $this->data_private;
      $spotted_atr[] = $this->container_type;
      $spotted_atr[] = $this->smileytheme;
      $this->id = md5(serialize($spotted_atr));
    }
    return $this->id;
  }  

  /**
   * save the phpxchatconfig object into sessions if necessary
   * else restore the old phpxchatconfig object
   */
  function synchronizeWithSession()
  {
    $session_id = $this->prefix."chatconfig_".$this->getId();
    if (isset($_SESSION[$session_id]))
    {
      $chatconfig =& unserialize($_SESSION[$session_id]); // restore $chatconfig var
      $classvar = get_class_vars(get_class($this));
      foreach( $classvar as $cv_name => $cv_val )
        $this->$cv_name = $chatconfig->$cv_name;      
    }
    else
    {
      if (!$this->isInit())
        $this->init();
      if (!$this->isInit())
      {
        $errors = $this->getErrors();
        echo "<ul>"; foreach( $errors as $e ) echo "<li>".$e."</li>"; echo "</ul>";
        exit;
      }
      // save the validated config in session
      $this->saveInSession();
    }
  }

  function saveInSession()
  {
    $session_id = $this->prefix."chatconfig_".$this->getId();
    //    $_SESSION[$session_id] = serialize($this);
    $chatconfig =& $this;
    $_SESSION[$session_id] = serialize(&$chatconfig);
    if ($this->debug) pxlog($this, "chatconfig", $this->getId());
  }
}

?>

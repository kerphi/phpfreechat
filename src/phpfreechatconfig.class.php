<?php
/**
 * phpfreechatconfig.class.php
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

require_once dirname(__FILE__)."/../debug/log.php";
require_once dirname(__FILE__)."/phpfreechattools.class.php";

/**
 * phpFreeChatConfig stock configuration data into sessions and initialize some stuff
 *
 * @author Stephane Gully <stephane.gully@gmail.com>
 */
class phpFreeChatConfig
{
  var $nick           = "";
  var $id             = 0;
  var $default_params = array();
  var $errors         = array();
  var $is_init        = false;
  var $smileys        = array();
  var $version        = "";
  var $debugpath      = "";
  var $active         = true;
  
  function phpFreeChatConfig( $params = array() )
  {
    $this->default_params["title"]               = "My phpFreeChat";
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
    $this->default_params["useie7"]              = true;
    $this->default_params["smartypath"]          = dirname(__FILE__)."/../lib/Smarty-2.6.7";
    $this->default_params["xajaxpath"]           = dirname(__FILE__)."/../lib/xajax_0.2_stable";
    $this->default_params["ie7path"]             = dirname(__FILE__)."/../data/public/IE7_0_9";
    $this->default_params["data_private"]        = dirname(__FILE__)."/../data/private";
    $this->default_params["data_public"]         = dirname(__FILE__)."/../data/public";
    $this->default_params["shownotice"]          = true;
    $this->default_params["debug"]               = false;
    $this->default_params["connect"]             = true;
    $this->default_params["smileytheme"]         = "default";
    $this->default_params["prefix"]              = "phpfreechat_";
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
      $i = new phpFreeChatConfig( $params );
    return $i;
  }
  
  function &getContainerInstance()
  {
    $container_classname = "phpFreeChatContainer".$this->container_type;
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
      @phpFreeChatTools::RecursiveMkdir($this->data_public);
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
      @phpFreeChatTools::RecursiveMkdir($this->data_private);
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
      @phpFreeChatTools::RecursiveMkdir($dir);
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
    // test xajax lib existance
    $dir = $this->xajaxpath;
    if ($ok && !is_dir($dir))
    {
      $ok = false;
      $this->errors[] = $dir." doesn't exists, xajax library can't be found.";
    }
    if ($ok && !file_exists($dir."/xajax.inc.php"))
    {
      $ok = false;
      $this->errors[] = "xajax.inc.php not found, xajax library can't be found.";
    }
    // copy public xajax js to phpfreechat public directory
    if ($ok)
    {
      @phpFreeChatTools::RecursiveMkdir($this->data_public."/xajax_js/");
      $ok &= copy( $this->xajaxpath."/xajax_js/xajaxCompress.php", $this->data_public."/xajax_js/xajaxCompress.php" );
      $ok &= copy( $this->xajaxpath."/xajax_js/xajax_uncompressed.js", $this->data_public."/xajax_js/xajax_uncompressed.js" );
    }

    // ---
    // test smarty lib
    $dir = $this->smartypath;
    if ($ok && !is_dir($dir))
    {
      $ok = false;
      $this->errors[] = $dir." doesn't exists, smarty library can't be found.";
    }
    if ($ok && !file_exists($dir."/libs/Smarty.class.php"))
    {
      $ok = false;
      $this->errors[] = "Smarty.class.php not found, smarty library can't be found.";
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
      $container_classname = "phpFreeChatContainer".$this->default_params["container_type"];
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

    // load debug path if necessary
    if ($this->debug)
      $this->debugpath = phpFreeChatTools::RelativePath(dirname($_SERVER["SCRIPT_FILENAME"]), dirname(__FILE__).'/../debug/');
    
    // load version number from file
    $this->version = file_get_contents(dirname(__FILE__)."/../version");
    
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
        $smiley_file = phpFreeChatTools::RelativePath(dirname($_SERVER["SCRIPT_FILENAME"]), dirname(__FILE__).'/../smileys/'.$this->smileytheme.'/'.$res[1]);
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
    $smarty->assign("version", $this->version);
    $smarty->assign("smileys", $this->smileys);
    $smarty->assign("debugpath", $this->debugpath);
  }

  function getId()
  {
    // calculate the chat id
    if ($this->id == 0)
    {
      $spotted_atr = array();
//      $spotted_atr[] = $_SERVER["SCRIPT_FILENAME"];
      $spotted_atr[] = $this->title;
      $spotted_atr[] = $this->channel;
      $spotted_atr[] = $this->prefix;
      $spotted_atr[] = $this->debug;
      $spotted_atr[] = $this->connect;
      $spotted_atr[] = $this->data_public; 
      $spotted_atr[] = $this->data_private;
      $spotted_atr[] = $this->smartypath;
      $spotted_atr[] = $this->xajaxpath;
      $spotted_atr[] = $this->container_type;
      $spotted_atr[] = $this->smileytheme;
      $spotted_atr[] = $this->version;
      $this->id = md5(serialize($spotted_atr));
    }
    return $this->id;
  }  

  /**
   * save the phpfreechatconfig object into sessions if necessary
   * else restore the old phpfreechatconfig object
   */
  function synchronizeWithSession()
  {
    $session_id = $this->prefix."chatconfig_".$this->getId();

    if (isset($_SESSION[$session_id]))
    {
      $pfc_configvar = unserialize($_SESSION[$session_id]); // restore $chatconfig var
      foreach($pfc_configvar as $key => $val)
	$this->$key = $val;
      if ($this->debug) pxlog("synchronizeWithSession[".$this->getId()."]: restore chatconfig from session nick=".$this->nick, "chatconfig", $this->getId());
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
    $_SESSION[$session_id] = serialize(get_object_vars($this));
    if ($this->debug) pxlog("saveInSession[".$this->getId()."]: nick=".$this->nick, "chatconfig", $this->getId());
  }
}

?>

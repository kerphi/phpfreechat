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
  var $rootpath      = "";
  //  var $active         = true;
  
  function phpFreeChatConfig( $params = array() )
  {
    $this->default_params["title"]               = "My phpFreeChat";
    $this->default_params["channel"]             = preg_replace("/[^a-z0-9]*/","",strtolower($this->default_params["title"]));
    $this->default_params["nick"]                = "";
    $this->default_params["frozen_nick"]         = false;
    $this->default_params["max_nick_len"]        = 15;
    $this->default_params["max_text_len"]        = 250;
    $this->default_params["connect_at_startup"]  = true;
    $this->default_params["start_minimized"]     = false;
    $this->default_params["refresh_delay"]       = 5000; // in mili-seconds (5 seconds)
    $this->default_params["max_msg"]             = 20;
    $this->default_params["height"]              = "440px";
    $this->default_params["width"]               = "";
    $this->default_params["css_file"]            = "";
    $this->default_params["server_script"]       = "";
    $this->default_params["useie7"]              = true;
    $this->default_params["ie7path"]             = dirname(__FILE__)."/../lib/IE7_0_9";
    $this->default_params["smartypath"]          = dirname(__FILE__)."/../lib/Smarty-2.6.7";
    $this->default_params["xajaxpath"]           = dirname(__FILE__)."/../lib/xajax_0.2_stable";
    $this->default_params["jspath"]              = dirname(__FILE__)."/../lib/javascript";
    $this->default_params["data_private"]        = dirname(__FILE__)."/../data/private";
    $this->default_params["data_public"]         = dirname(__FILE__)."/../data/public";
    $this->default_params["shownotice"]          = true;
    $this->default_params["debug"]               = false;
    $this->default_params["active"]              = true;
    $this->default_params["nickmarker"]          = true;
    $this->default_params["clock"]               = true;
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

  
  /**
   * Return the selected container instance
   * by default it is the File container
   */
  function &getContainerInstance()
  {
    // bug in php4: cant make a static phpFreeChatContainer instance because
    // it make problems with phpFreeChatConfig references (not updated)
    // it works well in php5, maybe there is a workeround but I don't have time to debug this
    // to reproduce the bug: uncomment the next lines and try to change your nickname
    //                       the old nickname will not be removed
    //    static $container;
    //    if (!isset($container))
    //    {
    $container_classname = "phpFreeChatContainer".$this->container_type;
    require_once dirname(__FILE__)."/".strtolower($container_classname).".class.php";
    $container = new $container_classname($this);
    //    }
    return $container;
  }

  /**
   * Check the functions really exists on this server
   */
  function _checkUsedFunctions( $f_list )
  {
    $ok = true;
    foreach( $f_list as $func => $err )
    {
      if (!function_exists( $func ))
      {
        $this->errors[] = $func." doesn't exists. ".$err;
        $ok = false;
      }
    }
    return $ok;
  }
  
  /**
   * Initialize the phpfreechat configuration
   * this initialisation is done once at startup then it is stored into a session cache
   */
  function init()
  {
    $this->errors = array();
    $ok = true;

    // first of all, check the used functions
    $f_list["file_get_contents"] = "You need PHP 4 >= 4.3.0 or PHP 5";
    $err_session_x = "You need PHP 4 or PHP 5";
    $f_list["session_start"]   = $err_session_x;
    $f_list["session_destroy"] = $err_session_x;
    $f_list["session_id"]      = $err_session_x;
    $f_list["session_name"]    = $err_session_x;    
    $err_preg_x = "You need PHP 3 >= 3.0.9 or PHP 4 or PHP 5";
    $f_list["preg_match"]      = $err_preg_x;
    $f_list["preg_replace"]    = $err_preg_x;
    $f_list["preg_split"]      = $err_preg_x;
    $err_ob_x = "You need PHP 4 or PHP 5";
    $f_list["ob_start"]        = $err_ob_x;
    $f_list["ob_get_contents"] = $err_ob_x;
    $f_list["ob_end_clean"]    = $err_ob_x;
    $f_list["get_object_vars"] = "You need PHP 4 or PHP 5";
    $ok &= $this->_checkUsedFunctions($f_list);
    
    $ok &= $this->_testWritableDir($this->data_public, "data_public");
    $ok &= $this->_testWritableDir($this->data_private, "data_private");
    $ok &= $this->_testWritableDir($this->data_private."/templates_c/");
    $ok &= $this->_installDir($this->jspath, $this->data_public."/javascript/");
    
    $ok &= $this->_installFile(dirname(__FILE__)."/../misc/shade.gif", $this->data_public."/images/shade.gif");
    $ok &= $this->_installFile(dirname(__FILE__)."/../misc/minimize.gif", $this->data_public."/images/minimize.gif");
    $ok &= $this->_installFile(dirname(__FILE__)."/../misc/maximize.gif", $this->data_public."/images/maximize.gif");

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
    if ($ok)
    {
      // install public xajax js to phpfreechat public directory
      $ok &= $this->_installFile($this->xajaxpath."/xajax_js/xajaxCompress.php",
                                 $this->data_public."/xajax_js/xajaxCompress.php");
      $ok &= $this->_installFile($this->xajaxpath."/xajax_js/xajax_uncompressed.js",
                                 $this->data_public."/xajax_js/xajax_uncompressed.js" );
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
    // test ie7 lib
    $dir = $this->ie7path;
    if ($ok && !is_dir($dir))
    {
      $ok = false;
      $this->errors[] = $dir." doesn't exists, ie7 library can't be found.";
    }
    if ($ok && !file_exists($dir."/ie7-core.js"))
    {
      $ok = false;
      $this->errors[] = "ie7-core.js not found, ie7 library can't be found.";
    }
    $ok &= $this->_installDir($this->ie7path, $this->data_public."/ie7/");
    
    // ---
    // test server script
    if ($ok &&
        $this->server_script != "")
    {
      $filetotest = $this->server_script;
      // do not take into account the url parameters
      if (preg_match("/(.*)\?(.*)/",$this->server_script, $res))
        $filetotest = $res[1];
      if ( !file_exists($filetotest) )
      {
        $ok = false;
        $this->errors[] = $filetotest." doesn't exist";
      }
    }
    
    // ---
    // run specific container initialisation
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

    // load root path
    $this->rootpath = phpFreeChatTools::RelativePath(phpFreeChatTools::GetScriptFilename(),
                                                     dirname(__FILE__).'/../');

    // load smileys from file
    if ($ok)
      $this->loadSmileyTheme();
    
    // do not froze nickname if it has not be specified
    if ($this->nick == "" && $this->frozen_nick)
      $this->frozen_nick = false;
    
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
      else if (preg_match("/^([a-z_0-9]*(\.gif|\.png))(.*)$/i",$line,$res))
      {
        $smiley_file = $this->rootpath.'/smileys/'.$this->smileytheme.'/'.$res[1];
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
    $vars = get_object_vars($this);
    foreach($vars as $p_k => $p_v)
      $smarty->assign($p_k, $p_v);
  }

  function getId()
  {
    // calculate the chat id
    if ($this->id == 0)
    {
      $spotted_atr = array();
      $spotted_atr[] = dirname(__FILE__);
      // do not check script filename if the chat use a script for the server side
      // because it's possible to put the script at a different place than the client script
      if ($this->server_script == "")
        $spotted_atr[] = phpFreeChatTools::GetScriptFilename();
      $spotted_atr[] = $this->title;
      $spotted_atr[] = $this->channel;
      $spotted_atr[] = $this->prefix;
      $spotted_atr[] = $this->debug;
      $spotted_atr[] = $this->data_public; 
      $spotted_atr[] = $this->data_private;
      $spotted_atr[] = $this->smartypath;
      $spotted_atr[] = $this->xajaxpath;
      $spotted_atr[] = $this->container_type;
      $spotted_atr[] = $this->smileytheme;
      $spotted_atr[] = $this->shownotice;
      $spotted_atr[] = $this->frozen_nick;
      $spotted_atr[] = $this->max_msg;
      $spotted_atr[] = $this->clock;
      $spotted_atr[] = $this->nickmarker;
      $spotted_atr[] = $this->connect_at_startup;
      $spotted_atr[] = $this->start_minimized;
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
      $pfc_configvar = unserialize($_SESSION[$session_id]);
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



  function _testWritableDir($dir, $name = "")
  {
    if ($dir == "")
    {
      $this->errors[] = ($name!="" ? $name : $dir)." directory must be specified";
      return false;
    }

    if (is_file($dir))
    {
      $this->errors[] = $dir." must be a directory";
      return false;
    }
    if (!is_dir($dir))
      @phpFreeChatTools::RecursiveMkdir($dir);
    if (!is_dir($dir))
    {
      $this->errors[] = $dir." can't be created";
      return false;
    }
    if (!is_writeable($dir))
    {
      $this->errors[] = $dir." is not writeable";
      return false;
    }
    if (!is_readable($dir))
    {
      $this->errors[] = $dir." is not readable";
      return false;
    }
    return true;
  }

  function _installFile($src_file, $dst_file)
  {
    $src_dir = dirname($src_file);
    $dst_dir = dirname($dst_file);
    
    if (!is_file($src_file))
    {
      $this->errors[] = $src_file." is not a file.";
      return false;
    }
    if (!is_readable($src_file))
    {
      $this->errors[] = $src_file." is not readable.";
      return false;
    }      
    if (!is_dir($src_dir))
    {
      $this->errors[] = $src_dir." is not a directory.";
      return false;
    }
    if (!is_dir($dst_dir))
      phpFreeChatTools::RecursiveMkdir($dst_dir);
    return copy( $src_file, $dst_file );
  }

  function _installDir($src_dir, $dst_dir)
  {
    if (!is_dir($src_dir))
    {
      $this->errors[] = $src_dir." is not a directory.";
      return false;
    }
    if (!is_readable($src_dir))
    {
      $this->errors[] = $src_dir." is not readable.";
      return false;
    }
    return @phpFreeChatTools::CopyR( $src_dir, $dst_dir );
  }

}

?>

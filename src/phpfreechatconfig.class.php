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
require_once dirname(__FILE__)."/phpfreechati18n.class.php";

/**
 * phpFreeChatConfig stock configuration data into sessions and initialize some stuff
 *
 * @author Stephane Gully <stephane.gully@gmail.com>
 */
class phpFreeChatConfig
{
  var $serverid            = 0; // this is the chat server id (comparable to the server host in IRC)
  var $nick                = ""; // the initial nickname ("" means the user will be queried)
  var $title               = ""; // default is _pfc("My Chat")
  var $channel             = ""; // default is a value calculated from title
  var $frozen_nick         = false;
  var $max_nick_len        = 15;
  var $max_text_len        = 250;
  var $refresh_delay       = 5000; // in mili-seconds (5 seconds)
  var $max_msg             = 20;
  var $connect_at_startup  = true;
  var $start_minimized     = false;
  var $height              = "440px";
  var $width               = "";
  var $css_file            = ""; // used to personalize the chat appearance
  var $shownotice          = 2; // show: 0 = nothing, 1 = just nickname changes, 2 = 1+connect/quit
  var $nickmarker          = true; // show/hide nicknames colors
  var $clock               = true; // show/hide dates and hours
  var $smileyurl           = ""; // default is calculated from smileypath value
  var $smileypath          = ""; // default is dirname(__FILE__)."/../smileys";
  var $smileytheme         = "default";
  var $tplpath             = ""; // default is dirname(__FILE__)."/../templates";
  var $tpltheme            = "default";
  var $language            = "";      // could be something in i18n/* directory ("" means the language is guess from the server config)
  var $output_encoding     = "UTF-8"; // could be ISO-8859-1 or anything else (which must be supported by iconv php module)
  var $container_type      = "File";  
  var $rootpath            = ""; // default is dirname(__FILE__)."/..";
  var $rooturl             = ""; // default is a value calculated from rootpath
  var $client_script_path  = "";
  var $client_script_url   = ""; // default is calculated from 'client_script_path'
  var $server_script_path  = "";
  var $server_script_url   = ""; // default is calculated from 'server_script_path'
  var $useie7              = true; // use IE7 lib : fix crappy IE display bugs
  var $ie7path             = ""; // default is dirname(__FILE__)."/../lib/IE7_0_9";
  var $xajaxpath           = ""; // default is dirname(__FILE__)."/../lib/xajax_0.2_stable";
  var $jspath              = ""; // default is dirname(__FILE__)."/../lib/javascript";
  var $csstidypath         = ""; // default is dirname(__FILE__)."/../lib/csstidy-1.1";
  var $data_private_path   = ""; // default is dirname(__FILE__)."/../data/private";
  var $data_public_path    = ""; // default is dirname(__FILE__)."/../data/public";
  var $data_public_url     = ""; // default is calculated from 'data_public_path' path

  var $smileys             = array();
  var $errors              = array();
  var $prefix              = "pfc_";
  var $active              = false; // used internaly
  var $is_init             = false; // used internaly to know if the chat config is initialized
  var $version             = ""; // the phpfreechat version: taken from the 'version' file content
  var $sessionid           = 0; // the client sessionid, this is automatically set by phpfreechat instance
  var $debug               = false;
  
  function phpFreeChatConfig( $params = array() )
  {
    // setup the local for translated messages
    phpFreeChatI18N::Init(isset($params["language"]) ? $params["language"] : "");

    // set user's values
    foreach ( $params as $k => $v )
    {
      if (!isset($this->$k))
        $this->errors[] = _pfc("Error: undefined or obsolete parameter '%s', please correct or remove this parameter", $k);
      $this->$k = $v;
    }

    // setup a defaut title if user didn't set it up
    if ($this->title == "")        $this->title        = _pfc("My Chat");
    if ($this->ie7path == "")      $this->ie7path      = dirname(__FILE__)."/../lib/IE7_0_9";
    if ($this->xajaxpath == "")    $this->xajaxpath    = dirname(__FILE__)."/../lib/xajax_0.2_stable";
    if ($this->jspath == "")       $this->jspath       = dirname(__FILE__)."/../lib/javascript";
    if ($this->csstidypath == "")  $this->csstidypath  = dirname(__FILE__)."/../lib/csstidy-1.1";
    if ($this->data_private_path == "") $this->data_private_path = dirname(__FILE__)."/../data/private";
    if ($this->data_public_path == "")  $this->data_public_path  = dirname(__FILE__)."/../data/public";
    if ($this->smileypath == "")   $this->smileypath   = dirname(__FILE__)."/../smileys";
    if ($this->rootpath == "")     $this->rootpath     = dirname(__FILE__)."/..";
    if ($this->tplpath == "")      $this->tplpath      = dirname(__FILE__)."/../templates";
    
    // choose a auto-generated channel name if user choose a title but didn't choose a channel name
    if ( $this->channel == "" )
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
    $container =& new $container_classname($this);
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
        $this->errors[] = _pfc("%s doesn't exist: %s", $func, $err);
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
    $ok = true;

    // first of all, check the used functions
    $f_list["file_get_contents"] = _pfc("You need %s", "PHP 4 >= 4.3.0 or PHP 5");
    $err_session_x = "You need PHP 4 or PHP 5";
    $f_list["session_start"]   = $err_session_x;
    $f_list["session_destroy"] = $err_session_x;
    $f_list["session_id"]      = $err_session_x;
    $f_list["session_name"]    = $err_session_x;    
    $err_preg_x = _pfc("You need %s", "PHP 3 >= 3.0.9 or PHP 4 or PHP 5");
    $f_list["preg_match"]      = $err_preg_x;
    $f_list["preg_replace"]    = $err_preg_x;
    $f_list["preg_split"]      = $err_preg_x;
    $err_ob_x = _pfc("You need %s", "PHP 4 or PHP 5");
    $f_list["ob_start"]        = $err_ob_x;
    $f_list["ob_get_contents"] = $err_ob_x;
    $f_list["ob_end_clean"]    = $err_ob_x;
    $f_list["get_object_vars"] = _pfc("You need %s", "PHP 4 or PHP 5");
    $ok &= $this->_checkUsedFunctions($f_list);
    
    $ok &= $this->_testWritableDir($this->data_public_path, "data_public_path");
    $ok &= $this->_testWritableDir($this->data_private_path, "data_private_path");
    $ok &= $this->_installDir($this->jspath, $this->data_public_path."/javascript/");
    
    $ok &= $this->_installFile(dirname(__FILE__)."/../misc/color-on.gif", $this->data_public_path."/images/color-on.gif");
    $ok &= $this->_installFile(dirname(__FILE__)."/../misc/color-off.gif", $this->data_public_path."/images/color-off.gif");
    $ok &= $this->_installFile(dirname(__FILE__)."/../misc/clock-on.gif", $this->data_public_path."/images/clock-on.gif");
    $ok &= $this->_installFile(dirname(__FILE__)."/../misc/clock-off.gif", $this->data_public_path."/images/clock-off.gif");
    $ok &= $this->_installFile(dirname(__FILE__)."/../misc/logout.gif", $this->data_public_path."/images/logout.gif");
    $ok &= $this->_installFile(dirname(__FILE__)."/../misc/login.gif", $this->data_public_path."/images/login.gif");
    $ok &= $this->_installFile(dirname(__FILE__)."/../misc/minimize.gif", $this->data_public_path."/images/minimize.gif");
    $ok &= $this->_installFile(dirname(__FILE__)."/../misc/maximize.gif", $this->data_public_path."/images/maximize.gif");
    $ok &= $this->_installFile(dirname(__FILE__)."/../misc/shade.gif", $this->data_public_path."/images/shade.gif");

    // ---
    // test xajax lib existance
    $dir = $this->xajaxpath;
    if ($ok && !is_dir($dir))
    {
      $ok = false;
      $this->errors[] = _pfc("%s doesn't exist, %s library can't be found", $dir, "XAJAX");
    }
    if ($ok && !file_exists($dir."/xajax.inc.php"))
    {
      $ok = false;
      $this->errors[] = _pfc("%s not found, %s library can't be found", "xajax.inc.php", "XAJAX");
    }
    if ($ok)
    {
      // install public xajax js to phpfreechat public directory
      $ok &= $this->_installFile($this->xajaxpath."/xajax_js/xajaxCompress.php",
                                 $this->data_public_path."/xajax_js/xajaxCompress.php");
      $ok &= $this->_installFile($this->xajaxpath."/xajax_js/xajax_uncompressed.js",
                                 $this->data_public_path."/xajax_js/xajax_uncompressed.js" );
    }

    // ---
    // test ie7 lib
    $dir = $this->ie7path;
    if ($ok && !is_dir($dir))
    {
      $ok = false;
      $this->errors[] = _pfc("%s doesn't exist, %s library can't be found", $dir, "IE7");
    }
    if ($ok && !file_exists($dir."/ie7-core.js"))
    {
      $ok = false;
      $this->errors[] = _pfc("%s not found, %s library can't be found", "ie7-core.js", "IE7");
    }
    $ok &= $this->_installDir($this->ie7path, $this->data_public_path."/ie7/");

    // ---
    // test client script
    if ($ok)
    {
      // try to find the path into server configuration
      if ($this->client_script_path == "")
	$this->client_script_path = phpFreeChatTools::GetScriptFilename();
      $filetotest = $this->client_script_path;
      // do not take into account the url parameters
      if (preg_match("/(.*)\?(.*)/", $filetotest, $res))
	$filetotest = $res[1];
      if ( !file_exists($filetotest) )
      {
	$ok = false;
	$this->errors[] = _pfc("%s doesn't exist", $filetotest);
      }

      if ($this->client_script_url == "")
      {
	$this->client_script_url = "./".basename($filetotest);
      }
    }

    // calculate smiley url
    if ($this->smileyurl == "")
    {
      $this->smileyurl = phpFreeChatTools::RelativePath($this->client_script_path, $this->smileypath);
    }

    // calculate datapublic url
    if ($this->data_public_url == "")
    {
      $this->data_public_url = phpFreeChatTools::RelativePath($this->client_script_path, $this->data_public_path);
    }
    // ---
    // test server script
    if ($ok)
    {
      if ($this->server_script_path == "") $this->server_script_path = $this->client_script_path;
      $filetotest = $this->server_script_path;
      // do not take into account the url parameters
      if (preg_match("/(.*)\?(.*)/",$this->server_script_path, $res))
        $filetotest = $res[1];
      if ( !file_exists($filetotest) )
      {
	$ok = false;
        $this->errors[] = _pfc("%s doesn't exist", $filetotest);
      }
      if ($this->server_script_url == "")
      {
	$this->server_script_url = $this->client_script_url;
      }
    }
    
    // ---
    // run specific container initialisation
    if ($ok)
    {
      $container_classname = "phpFreeChatContainer".$this->container_type;
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
    $this->rooturl = phpFreeChatTools::RelativePath($this->client_script_path, $this->rootpath);

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
  
  function &getErrors()
  {
    return $this->errors;
  }

  function loadSmileyTheme()
  {
    $theme = file($this->smileypath."/".$this->smileytheme."/theme");
    $result = array();
    foreach($theme as $line)
    {
      if (preg_match("/^#.*/",$line))
        continue;
      else if (preg_match("/^([a-z_0-9]*(\.gif|\.png))(.*)$/i",$line,$res))
      {
        $smiley_file = $this->smileyurl.'/'.$this->smileytheme.'/'.$res[1];
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

  function getId()
  {
    // calculate the chat id
    // do not put in the parameter list something which is modified in
    // ->init() methode
    if ($this->serverid == 0)
    {
      $spotted_atr = array();
      $spotted_atr[] = dirname(__FILE__);
      $spotted_atr[] = $this->title;
      $spotted_atr[] = $this->channel;
      //$spotted_atr[] = $this->prefix; /* do not use prefix here because it is used before the first getId call */
      $spotted_atr[] = $this->debug;
      //$spotted_atr[] = $this->client_script_path; /* do not uncomment because it can be set in ->init() methode */ 
      //$spotted_atr[] = $this->server_script_path; /* do not uncomment because it can be set in ->init() methode */
      $spotted_atr[] = $this->data_public_path; 
      $spotted_atr[] = $this->data_private_path;
      $spotted_atr[] = $this->xajaxpath;
      $spotted_atr[] = $this->csstidypath;
      $spotted_atr[] = $this->container_type;
      $spotted_atr[] = $this->smileypath;
      $spotted_atr[] = $this->smileytheme;
      $spotted_atr[] = $this->tplpath;
      $spotted_atr[] = $this->tpltheme;
      $spotted_atr[] = $this->shownotice;
      $spotted_atr[] = $this->frozen_nick;
      $spotted_atr[] = $this->max_msg;
      $spotted_atr[] = $this->clock;
      $spotted_atr[] = $this->nickmarker;
      $spotted_atr[] = $this->connect_at_startup;
      $spotted_atr[] = $this->start_minimized;
      $spotted_atr[] = $this->language;
      $spotted_atr[] = $this->output_encoding;
      $this->serverid = md5(serialize($spotted_atr));
    }
    return $this->serverid;
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
      $errors =& $this->getErrors();
      if (count($errors) > 0)
      {
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
    pxlog($this->nick, "chatconfig", $this->getId());
    pxlog(debug_backtrace(), "chatconfig", $this->getId());

    //    if ($this->debug) pxlog("saveInSession[".$this->getId()."]: nick=".$this->nick, "chatconfig", $this->getId());
  }



  function _testWritableDir($dir, $name = "")
  {
    if ($dir == "")
    {
      $this->errors[] = _pfc("%s directory must be specified", ($name!="" ? $name : $dir));
      return false;
    }

    if (is_file($dir))
    {
      $this->errors[] = _pfc("%s must be a directory",$dir);
      return false;
    }
    if (!is_dir($dir))
      @phpFreeChatTools::RecursiveMkdir($dir);
    if (!is_dir($dir))
    {
      $this->errors[] = _pfc("%s can't be created",$dir);
      return false;
    }
    if (!is_writeable($dir))
    {
      $this->errors[] = _pfc("%s is not writeable",$dir);
      return false;
    }
    if (!is_readable($dir))
    {
      $this->errors[] = _pfc("%s is not readable",$dir);
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
      $this->errors[] = _pfc("%s is not a file", $src_file);
      return false;
    }
    if (!is_readable($src_file))
    {
      $this->errors[] = _pfc("%s is not readable", $src_file);
      return false;
    }      
    if (!is_dir($src_dir))
    {
      $this->errors[] = _pfc("%s is not a directory", $src_dir);
      return false;
    }
    if (!is_dir($dst_dir))
      @phpFreeChatTools::RecursiveMkdir($dst_dir);
    return @copy( $src_file, $dst_file );
  }

  function _installDir($src_dir, $dst_dir)
  {
    if (!is_dir($src_dir))
    {
      $this->errors[] = _pfc("%s is not a directory", $src_dir);
      return false;
    }
    if (!is_readable($src_dir))
    {
      $this->errors[] = _pfc("%s is not readable", $src_dir);
      return false;
    }
    return @phpFreeChatTools::CopyR( $src_dir, $dst_dir );
  }

}

?>

<?php
/**
 * pfcglobalconfig.class.php
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

require_once dirname(__FILE__)."/pfctools.php";
require_once dirname(__FILE__)."/pfci18n.class.php";

/**
 * pfcGlobalConfig stock configuration data into sessions and initialize some stuff
 *
 * @author Stephane Gully <stephane.gully@gmail.com>
 */
class pfcGlobalConfig
{
  var $serverid            = ""; // this is the chat server id (comparable to the server host in IRC)
  
  // these parameters are dynamic (not cached)
  var $nick                = ""; // the initial nickname ("" means the user will be queried)
  var $isadmin             = false;
  var $channels            = array(); // the default joined channels when opening the chat
  var $privmsg             = array(); // the default privmsg chat to lauch when opening the chat
  var $active              = false;   // by default the user is not connected
  
  // these parameters are static (cached)
  var $proxys              = array("auth");
  var $title               = ""; // default is _pfc("My Chat")
  var $channel             = ""; // default is _pfc("My room")
  var $frozen_nick         = false;
  var $max_nick_len        = 15;
  var $max_text_len        = 400;
  var $refresh_delay       = 5000; // in mili-seconds (5 seconds)
  var $timeout             = 20000; // in mili-seconds (20 seconds)
  var $max_msg             = 20;
  var $quit_on_closedwindow = false; // false because a reload event is the same as a close event
  var $focus_on_connect    = true;
  var $connect_at_startup  = true;
  var $start_minimized     = false;
  var $height              = "440px";
  var $width               = "";
  var $shownotice          = 3; // show: 0 = nothing, 1 = just nickname changes, 2 = connect/quit, 3 = 1+2
  var $nickmarker          = true; // show/hide nicknames colors
  var $clock               = true; // show/hide dates and hours
  var $openlinknewwindow   = true; // used to open the links in a new window

  var $showwhosonline      = true;
  var $showsmileys         = true;
  var $btn_sh_whosonline   = true; // display show/hide button for who is online
  var $btn_sh_smileys      = true; // display show/hide button for smileys

  var $theme               = "default";
  var $themepath           = "";
  var $themeurl            = "";
  var $themepath_default   = "";
  var $themeurl_default    = "";

  var $language            = "";      // could be something in i18n/* directory ("" means the language is guess from the server config)
  var $output_encoding     = "UTF-8"; // could be ISO-8859-1 or anything else (which must be supported by iconv php module)
  var $container_type      = "File";

  var $client_script_path  = "";
  var $client_script_url   = ""; // default is calculated from 'client_script_path'
  var $server_script_path  = "";
  var $server_script_url   = ""; // default is calculated from 'server_script_path'
  var $useie7              = true; // use IE7 lib : fix crappy IE display bugs
  var $ie7path             = ""; // default is dirname(__FILE__)."/../lib/IE7_0_9";
  var $xajaxpath           = ""; // default is dirname(__FILE__)."/../lib/xajax_0.2.3";
  var $jspath              = ""; // default is dirname(__FILE__)."/../lib/javascript";
  var $usecsstidy          = false;
  var $csstidypath         = ""; // default is dirname(__FILE__)."/../lib/csstidy-1.1";
  var $data_private_path   = ""; // default is dirname(__FILE__)."/../data/private";
  var $data_public_path    = ""; // default is dirname(__FILE__)."/../data/public";
  var $data_public_url     = ""; // default is calculated from 'data_public_path' path

  var $smileys             = array();
  var $errors              = array();
  var $prefix              = "pfc_";
  //  var $active              = false; // used internaly
  var $is_init             = false; // used internaly to know if the chat config is initialized
  var $version             = ""; // the phpfreechat version: taken from the 'version' file content
  //  var $sessionid           = 0; // the client sessionid, this is automatically set by phpfreechat instance
  var $debugurl            = "";
  var $debug               = false;
  var $debugxajax          = false;
  
  function pfcGlobalConfig( $params = array() )
  {
    //    $params["sessionid"] = session_id();

    // setup the local for translated messages
    pfcI18N::Init(isset($params["language"]) ? $params["language"] : "");

    // load users container or keep default one
    if (isset($params["container_type"]))
      $this->container_type = $params["container_type"];
    
    // load default container's config
    $container =& $this->getContainerInstance();
    $container_cfg = $container->getDefaultConfig();
    foreach( $container_cfg as $k => $v )
    {
      $attr = "container_cfg_".$k;
      if (!isset($this->$attr))
        $this->$attr = $v;
    }

    // load all user's parameters which will override default ones
    foreach ( $params as $k => $v )
    {
      if (!isset($this->$k))
        $this->errors[] = _pfc("Error: undefined or obsolete parameter '%s', please correct or remove this parameter", $k);
      $this->$k = $v;
    }

    if ($this->data_private_path == "") $this->data_private_path = dirname(__FILE__)."/../data/private";
    if ($this->data_public_path == "")  $this->data_public_path  = dirname(__FILE__)."/../data/public";

    $this->synchronizeWithCache();

    // the 'nick' is dynamic, it must not be cached
    if (isset($params["nick"]))    $this->nick    = $params["nick"];
    // the 'isadmin' flag is dynamic, it must not be cached
    if (isset($params["isadmin"])) $this->isadmin = $params["isadmin"];
  }

  function &Instance( $params = array() )
  {
    static $i;
    
    if (!isset($i))
      $i = new pfcGlobalConfig( $params );
    return $i;
  }

  
  /**
   * Return the selected container instance
   * by default it is the File container
   */
  function &getContainerInstance()
  {
    // bug in php4: cant make a static pfcContainer instance because
    // it make problems with pfcGlobalConfig references (not updated)
    // it works well in php5, maybe there is a workeround but I don't have time to debug this
    // to reproduce the bug: uncomment the next lines and try to change your nickname
    //                       the old nickname will not be removed
    //    static $container;
    //    if (!isset($container))
    //    {
    $container_classname = "pfcContainer_".$this->container_type;
    require_once dirname(__FILE__)."/containers/".strtolower($this->container_type).".class.php";
    $container =& new $container_classname($this);
    //    }
    return $container;
  }

  /**
   * Initialize the phpfreechat configuration
   * this initialisation is done once at startup then it is stored into a session cache
   */
  function init()
  {
    $ok = true;

    if ($this->debug) pxlog("pfcGlobalConfig::init()", "chatconfig", $this->getId());

    if ($this->title == "")        $this->title        = _pfc("My Chat");
    if ($this->channel == "")      $this->channel      = _pfc("My room");
    if ($this->ie7path == "")      $this->ie7path      = dirname(__FILE__)."/../lib/IE7_0_9";
    if ($this->xajaxpath == "")    $this->xajaxpath    = dirname(__FILE__)."/../lib/xajax_0.2.3";
    if ($this->jspath == "")       $this->jspath       = dirname(__FILE__)."/../lib/javascript";
    if ($this->csstidypath == "")  $this->csstidypath  = dirname(__FILE__)."/../lib/csstidy-1.1";

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
    $this->errors = array_merge($this->errors, check_functions_exist($f_list));
    
    $this->errors = array_merge($this->errors, @test_writable_dir($this->data_public_path, "data_public_path"));
    $this->errors = array_merge($this->errors, @test_writable_dir($this->data_private_path, "data_private_path"));
    $this->errors = array_merge($this->errors, @install_dir($this->jspath, $this->data_public_path."/javascript"));
    $this->errors = array_merge($this->errors, @test_writable_dir(dirname(__FILE__)."/../data/private/cache", "data_public_path/cache"));
    
    // ---
    // test xajax lib existance
    $dir = $this->xajaxpath;
    if (!is_dir($dir))
      $this->errors[] = _pfc("%s doesn't exist, %s library can't be found", $dir, "XAJAX");
    if (!file_exists($dir."/xajax.inc.php"))
      $this->errors[] = _pfc("%s not found, %s library can't be found", "xajax.inc.php", "XAJAX");
    // install public xajax js to phpfreechat public directory
    $this->errors = array_merge($this->errors, @install_file($this->xajaxpath."/xajax_js/xajax.js",
                                                             $this->data_public_path."/xajax_js/xajax.js"));
    $this->errors = array_merge($this->errors, @install_file($this->xajaxpath."/xajax_js/xajax_uncompressed.js",
                                                             $this->data_public_path."/xajax_js/xajax_uncompressed.js" ));
    // ---
    // test ie7 lib
    $dir = $this->ie7path;
    if (!is_dir($dir))
      $this->errors[] = _pfc("%s doesn't exist, %s library can't be found", $dir, "IE7");
    if (!file_exists($dir."/ie7-core.js"))
      $this->errors[] = _pfc("%s not found, %s library can't be found", "ie7-core.js", "IE7");
    $this->errors = array_merge($this->errors, @install_dir($this->ie7path, $this->data_public_path."/ie7/"));
    
    // ---
    // test client script
    // try to find the path into server configuration
    if ($this->client_script_path == "")
      $this->client_script_path = getScriptFilename();
    $filetotest = $this->client_script_path;
    // do not take into account the url parameters
    if (preg_match("/(.*)\?(.*)/", $filetotest, $res))
      $filetotest = $res[1];
    if ( !file_exists($filetotest) )
      $this->errors[] = _pfc("%s doesn't exist", $filetotest);   
    if ($this->client_script_url == "")
      $this->client_script_url = "./".basename($filetotest);

    // set the default theme path
    if ($this->themepath_default == "")
      $this->themepath_default = dirname(__FILE__)."/../themes";
    if ($this->themepath == "")
      $this->themepath = $this->themepath_default;
        
    // calculate the default theme url
    if ($this->themeurl_default == "")
      $this->themeurl_default = relativePath($this->client_script_path, $this->themepath_default);
    if ($this->themeurl == "")
      $this->themeurl = relativePath($this->client_script_path, $this->themepath);
    
    // calculate datapublic url
    if ($this->data_public_url == "")
      $this->data_public_url = relativePath($this->client_script_path, $this->data_public_path);
    // ---
    // test server script
    if ($this->server_script_path == "")
    {
      $this->server_script_path = $this->client_script_path;
      if ($this->server_script_url == "")
        $this->server_script_url  = $this->client_script_url;
    }
    $filetotest = $this->server_script_path;
    // do not take into account the url parameters
    if (preg_match("/(.*)\?(.*)/",$this->server_script_path, $res))
      $filetotest = $res[1];
    if ( !file_exists($filetotest) )
      $this->errors[] = _pfc("%s doesn't exist", $filetotest);
    if ($this->server_script_url == "")
      $this->server_script_url = relativePath($this->client_script_path, $this->server_script_path)."/".basename($filetotest);
    
    // ---
    // run specific container initialisation
    $container_classname = "pfcContainer_".$this->container_type;
    require_once dirname(__FILE__)."/containers/".strtolower($this->container_type).".class.php";
    $container = new $container_classname($this);
    $container_errors = $container->init();
    $this->errors = array_merge($this->errors, $container_errors);
    
    // load debug url
    $this->debugurl = relativePath($this->client_script_path, dirname(__FILE__)."/../debug");

    // check the serverid is really defined
    if ($this->serverid == "")
      $this->errors[] = _pfc("'%s' parameter is mandatory by default use '%s' value", "serverid", "md5(__FILE__)");
    
    // check the max_msg is >= 0
    if (!is_numeric($this->max_msg) || $this->max_msg < 0)
      $this->errors[] = _pfc("'%s' parameter must be a positive number", "max_msg");

    // check the max_nick_len is >= 0
    if (!is_numeric($this->max_nick_len) || $this->max_nick_len < 0)
      $this->errors[] = _pfc("'%s' parameter must be a positive number", "max_nick_len");
    
    // check the max_text_len is >= 0
    if (!is_numeric($this->max_text_len) || $this->max_text_len < 0)
      $this->errors[] = _pfc("'%s' parameter must be a positive number", "max_text_len");
    
    // check the refresh_delay is >= 0
    if (!is_numeric($this->refresh_delay) || $this->refresh_delay < 0)
      $this->errors[] = _pfc("'%s' parameter must be a positive number", "refresh_delay");
    
    // check the timeout is >= 0
    if (!is_numeric($this->timeout) || $this->timeout < 0)
      $this->errors[] = _pfc("'%s' parameter must be a positive number", "timeout");
    
    // check the language is known
    $lg_list = pfcI18N::GetAcceptedLanguage();
    if ( $this->language != "" && !in_array($this->language, $lg_list) )
      $this->errors[] = _pfc("'%s' parameter is not valid. Available values are : '%s'", "language", implode(", ", $lg_list));

    // check the width parameter is not used
    // because of a display bug in IE
    if ( $this->width != "" &&
	 $this->width != "auto" )
    {
      $this->errors[] = "Do not uses 'width' parameter because of a display bug in IE6, please look at this workaround : http://www.phpfreechat.net/forum/viewtopic.php?pid=867#p867";
      $ok = false;
    }
        
    // load smileys from file
    $this->loadSmileyTheme();
    
    // do not froze nickname if it has not be specified
    if ($this->nick == "" && $this->frozen_nick)
      $this->frozen_nick = false;
    
    // load version number from file
    $this->version = file_get_contents(dirname(__FILE__)."/../version");

    $this->is_init = (count($this->errors) == 0);
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
    $theme = file($this->getFilePathFromTheme("smileys/theme"));
    $result = array();
    foreach($theme as $line)
    {
      if (preg_match("/^#.*/",$line))
        continue;
      else if (preg_match("/^([a-z_0-9]*(\.gif|\.png))(.*)$/i",$line,$res))
      {
        $smiley_file = $this->getFileUrlFromTheme('smileys/'.$res[1]);
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
    /* channel is concatenated because it dynamic parameters
     * further these parameter must be separated from global pfcconfig
     * and can be changed dynamicaly in the user session */
    return $this->serverid;
  }  


  /**
   * save the pfcConfig object into cache if it doesn't exists yet
   * else restore the old pfcConfig object
   */
  function synchronizeWithCache($destroy = false)
  {
    $cachefile = dirname(__FILE__)."/../data/private/cache/pfcglobalconfig_".$this->getId();

    // destroy the cache if init parameter is present into the url
    if (isset($_GET["init"]) || $destroy) @unlink($cachefile);
    
    if (file_exists($cachefile))
    {
      $pfc_configvar = unserialize(file_get_contents($cachefile));
      foreach($pfc_configvar as $key => $val)
	$this->$key = $val;
      return true; // synchronized
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
      // save the validated config in cache
      $this->saveInCache();
      return false; // new cache created
    }
  }
  function saveInCache()
  {
    $cachefile = dirname(__FILE__)."/../data/private/cache/pfcglobalconfig_".$this->getId();
    file_put_contents($cachefile, serialize(get_object_vars($this)));
    if ($this->debug) pxlog("pfcGlobalConfig::saveInCache()", "chatconfig", $this->getId());
  }

  function getFileUrlFromTheme($file)
  {
    if (file_exists($this->themepath."/".$this->theme."/".$file))
      return $this->themeurl."/".$this->theme."/".$file;
    else
      if (file_exists($this->themepath_default."/default/".$file))
	return $this->themeurl_default."/default/".$file;
      else
	die(_pfc("Error: '%s' could not be found, please check your themepath '%s' and your theme '%s' are correct", $file, $this->themepath, $this->theme));
  }

  function getFilePathFromTheme($file)
  {
    if (file_exists($this->themepath."/".$this->theme."/".$file))
      return $this->themepath."/".$this->theme."/".$file;
    else
      if (file_exists($this->themepath_default."/default/".$file))
	return $this->themepath_default."/default/".$file;
      else
	die(_pfc("Error: '%s' could not be found, please check your themepath '%s' and your theme '%s' are correct", $file, $this->themepath, $this->theme));
  }
}

?>

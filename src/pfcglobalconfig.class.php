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
require_once dirname(__FILE__).'/pfccontainer.class.php';

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
  var $nickmeta            = array(); // this is the nickname user's metadata, you can add : sexe, age, real name ... (ex: array('sexe'=>'f') )
  var $nickmeta_private    = array('ip'); // this is the meta that only admins can see

  var $isadmin             = false;
  var $admins              = array("admin" => ""); // the key is the nickname, the value is the password
  var $firstisadmin        = false; // give admin rights to the first connected user on the server

  var $islocked            = false; // set this parameter to true to lock the chat for all users
  var $lockurl             = "http://www.phpfreechat.net"; // this is the url where the users must be redirected when the chat is locked
  
  // these parameters are static (cached)
  /**
   * These proxies will be skiped. ex: append "censor" to the list to disable words censoring
   */
  var $skip_proxies         = array();
  /**
   * These proxies will be handled just before to process commands and just after system proxies
   */
  var $post_proxies         = array();
  /**
   * These proxies will be handled before system proxies (at begining)
   */
  var $pre_proxies          = array();
  /**
   * Will contains proxies to execute on each command (filled in the init step) this parameter could not be overridden
   */
  var $proxies              = array();
  var $proxies_cfg          = array("auth"    => array(),
                                    "noflood" => array("charlimit" => 450,
                                                       "msglimit"  => 10,
                                                       "delay"     => 5),
                                    "censor"  => array("words"     => array("fuck","sex","bitch"),
                                                       "replaceby" => "*",
                                                       "regex"     => false),
                                    "log"     => array("path" => ""));
  var $proxies_path         = ""; // a custom proxies path
  var $proxies_path_default = ""; // dirname(__FILE__).'/proxies'
  var $cmd_path            = ""; // a custom commands path
  var $cmd_path_default    = ""; // dirname(__FILE__).'/commands'
  var $title               = ""; // default is _pfc("My Chat")
  var $channels            = array(); // the default joined channels when opening the chat
  var $frozen_channels     = array(); // if empty, allows users to create there own channels
  var $max_channels        = 10; // this the max number of allowed channels by users
  var $privmsg             = array(); // the joined private chat when opening the chat (the nicknames must be online)
  var $max_privmsg         = 5;  // this the max number of allowed privmsg by users
  var $frozen_nick         = false; // set it to true if you don't want the user to be able to change his nickname
  var $max_nick_len        = 15;
  var $max_text_len        = 400;
  var $refresh_delay       = 5000; // in mili-seconds (5 seconds)
  var $max_refresh_delay   = 60000; // in mili-seconds (60 seconds)
  var $timeout             = 20000; // in mili-seconds (20 seconds)
  var $max_msg             = 20; // number of messages keept in the history (this is what you see when you reload the chat)
  var $max_displayed_lines = 150; // maximum number of displayed lines (old lines will be deleted to save browser's memory)
  var $quit_on_closedwindow = true; // could be annoying because the reload event is the same as a close event
  var $focus_on_connect    = true;
  var $connect_at_startup  = true;
  var $start_minimized     = false;
  var $height              = "440px";
  var $width               = "";
  var $shownotice          = 3; // show: 0 = nothing, 1 = just nickname changes, 2 = join/quit, 3 = 1+2
  var $nickmarker          = true; // show/hide nicknames colors
  var $clock               = true; // show/hide dates and hours
  var $startwithsound      = true; // start with sound enabled
  var $openlinknewwindow   = true; // used to open the links in a new window
  var $notify_window       = true; // true : appends a prefix to the window title with the number of new posted messages
  
  /**
   * Be sure that you are conform to the license page before setting this to false !
   * http://www.phpfreechat.net/license.en.html
   */
  var $display_pfc_logo    = true; 

  var $displaytabimage       = true;
  var $displaytabclosebutton = true;
  var $showwhosonline      = true;
  var $showsmileys         = true;
  var $btn_sh_whosonline   = true; // display show/hide button for who is online
  var $btn_sh_smileys      = true; // display show/hide button for smileys
  var $bbcode_colorlist    = array("#FFFFFF","#000000","#000055","#008000","#FF0000","#800000","#800080","#FF5500","#FFFF00","#00FF00","#008080","#00FFFF","#0000FF","#FF00FF","#7F7F7F","#D2D2D2");
  var $nickname_colorlist  = array('#CCCCCC','#000000','#3636B2','#2A8C2A','#C33B3B','#C73232','#80267F','#66361F','#D9A641','#3DCC3D','#1A5555','#2F8C74','#4545E6','#B037B0','#4C4C4C','#959595');
  
  var $theme               = "default";
  var $theme_path          = '';
  var $theme_default_path  = '';
  var $theme_url           = '';
  var $theme_default_url   = '';
  
  var $language            = "";      // could be something in i18n/* directory ("" means the language is guess from the server config)
  var $output_encoding     = "UTF-8"; // could be ISO-8859-1 or anything else (which must be supported by iconv php module)
  var $container_type      = "File";

  var $client_script_path  = "";
  var $server_script_path  = "";
  var $server_script_url   = ""; // default is calculated from 'server_script_path'
  var $data_private_path   = ""; // default is dirname(__FILE__)."/../data/private";
  var $data_public_path    = ""; // default is dirname(__FILE__)."/../data/public";
  var $data_public_url     = ""; // default is calculated from 'data_public_path' path

  /**
   * This is the prototype javascript library url.
   * Use this parameter to use your external library.
   * default is data/js/prototype.js
   */
  var $prototypejs_url     = '';
  
  var $smileys             = array();
  var $errors              = array();
  var $is_init             = false; // used internaly to know if the chat config is initialized
  var $version             = ""; // the phpfreechat version: taken from the 'version' file content
  var $debugurl            = "";
  var $debug               = false;

  /**
   * This is the user time zone
   * it is the difference in seconds between user clock and server clock
   */
  var $time_offset         = 0;
  /**
   * How to display the dates in the chat
   */
  var $date_format         = "d/m/Y";
  /**
   * How to display the time in the chat
   */
  var $time_format         = "H:i:s";
  
  /**
   * This parameter is useful when your chat server is behind a reverse proxy that
   * forward client ip address in HTTP_X_FORWARDED_FOR http header.
   * see : http://www.phpfreechat.net/forum/viewtopic.php?id=1344
   */
  var $get_ip_from_xforwardedfor = false;

  
  // private parameters
  var $_sys_proxies         = array("lock", "checktimeout", "checknickchange", "auth", "noflood", "censor", "log");
  var $_dyn_params          = array("nick","isadmin","islocked","admins","frozen_channels", "channels", "privmsg", "nickmeta","time_offset","date_format","time_format");
  var $_params_type         = array();
  var $_query_string        = '';
  
  function pfcGlobalConfig( $params = array() )
  {
    // @todo find a cleaner way to forward serverid to i18n functions
    $GLOBALS['serverid'] = isset($params['serverid']) ? $params['serverid'] : '_serverid_';
    // setup the locales for the translated messages
    pfcI18N::Init(isset($params['language']) ? $params['language'] : '');

    // check the serverid is really defined
    if (!isset($params["serverid"]))
      $this->errors[] = _pfc("'%s' parameter is mandatory by default use '%s' value", "serverid", "md5(__FILE__)");
    $this->serverid = $params["serverid"];

    // setup data_private_path because _GetCacheFile needs it
    if (!isset($params["data_private_path"]))
      $this->data_private_path = dirname(__FILE__)."/../data/private";
    else
      $this->data_private_path = $params["data_private_path"];
    
    // check if a cached configuration allready exists
    // don't load parameters if the cache exists
    $cachefile = $this->_GetCacheFile();    
    if (!file_exists($cachefile))
    {
      // first of all, save our current state in order to be able to check for variable types later
      $this->_saveParamsTypes();

      if (!isset($params["data_public_path"]))
        $this->data_public_path  = dirname(__FILE__)."/../data/public";
      else
        $this->data_public_path = $params["data_public_path"];

      // if the user didn't specify the server_script_url, then remember it and
      // append QUERY_STRING to it
      if (!isset($params['server_script_url']))
        $this->_query_string = isset($_SERVER['QUERY_STRING']) && $_SERVER['QUERY_STRING'] != '' ?
          '?'.$_SERVER['QUERY_STRING'] :
          '';
      
      // load users container or keep default one
      if (isset($params["container_type"]))
        $this->container_type = $params["container_type"];     
      
      // load default container's config
      $ct =& pfcContainer::Instance($this->container_type, true);
      $ct_cfg = $ct->getDefaultConfig();
      foreach( $ct_cfg as $k => $v )
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
        if (preg_match('/^_/',$k))
          $this->errors[] = _pfc("Error: '%s' is a private parameter, you are not allowed to change it", $k);
        
        if ($k == "proxies_cfg")
        {
          // don't replace all the proxy_cfg parameters, just replace the specified ones
          foreach ( $params["proxies_cfg"] as $k2 => $v2 )
          {
            if (is_array($v2))
              foreach( $v2 as $k3 => $v3)
                $this->proxies_cfg[$k2][$k3] = $v3;
            else
              $this->proxies_cfg[$k2] = $v2;
          }
        }
        else
          $this->$k = $v;
      }
    }

    // load dynamic parameter even if the config exists in the cache
    foreach ( $this->_dyn_params as $dp )
      if (isset($params[$dp]))
        $this->$dp = $params[$dp];

    // 'channels' is now a dynamic parameter, just check if I need to initialize it or not
    if (is_array($this->channels) &&
        count($this->channels) == 0 &&
        !isset($params['channels']))
      $this->channels = array(_pfc("My room"));
    
    // now load or save the configuration in the cache
    $this->synchronizeWithCache();

    // to be sure the container instance is initialized
    $ct =& pfcContainer::Instance($this->container_type, true);

    // This is a dirty workaround which fix a infinite loop when:
    // 'frozen_nick' is true
    // 'nick' length is > 'max_nick_len'
    $this->nick = $this->filterNickname($this->nick);
  }

  function &Instance( $params = array() )
  {
    static $i;
    if (!isset($i))
      $i = new pfcGlobalConfig( $params );
    return $i;
  }

  
  /**
   * This function saves all the parameters types in order to check later if the types are ok
   */
  function _saveParamsTypes()
  {
    $vars = get_object_vars($this);
    foreach($vars as $k => $v)
    {
      if (is_string($v))                $this->_params_type["string"][]  = $k;
      else if (is_bool($v))             $this->_params_type["bool"][]    = $k;
      else if (is_array($v))            $this->_params_type["array"][]   = $k;
      else if (is_int($v) && $v>0)      $this->_params_type["positivenumeric"][] = $k;
      else $this->_params_type["misc"][] = $k;
    }
  }
  
  /**
   * Initialize the phpfreechat configuration
   * this initialisation is done once at startup then it is stored into a session cache
   */
  function init()
  {
    $ok = true;

    if ($this->debug) pxlog("pfcGlobalConfig::init()", "chatconfig", $this->getId());

    // check the parameters types
    $array_params = $this->_params_type["array"];
    foreach( $array_params as $ap )
    {
      if (!is_array($this->$ap))
        $this->errors[] = _pfc("'%s' parameter must be an array", $ap);
    }
    $numerical_positive_params = $this->_params_type["positivenumeric"];
    foreach( $numerical_positive_params as $npp )
    {
      if (!is_int($this->$npp) || $this->$npp < 0)
        $this->errors[] = _pfc("'%s' parameter must be a positive number", $npp);
    }
    $boolean_params = $this->_params_type["bool"];
    foreach( $boolean_params as $bp )
    {
      if (!is_bool($this->$bp))
        $this->errors[] = _pfc("'%s' parameter must be a boolean", $bp);
    }
    $string_params = $this->_params_type["string"];
    foreach( $string_params as $sp )
    {
      if (!is_string($this->$sp))
        $this->errors[] = _pfc("'%s' parameter must be a charatere string", $sp);
    }

    if ($this->title == "")           $this->title        = _pfc("My Chat");
      
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
    
    //    $this->errors = array_merge($this->errors, @test_writable_dir($this->data_public_path, "data_public_path"));
    $this->errors = array_merge($this->errors, @test_writable_dir($this->data_private_path, "data_private_path"));
    $this->errors = array_merge($this->errors, @test_writable_dir($this->data_private_path."/cache", "data_private_path/cache"));

    
    // install the public directory content
    $dir = dirname(__FILE__)."/../data/public/js";
    $dh = opendir($dir);
    while (false !== ($file = readdir($dh)))
    {
      $f_src = $dir.'/'.$file;
      $f_dst = $this->data_public_path.'/js/'.$file;
      if ($file == "." || $file == ".." || !is_file($f_src)) continue; // skip . and .. generic files
      // install js files only if the destination doesn't exists or if the destination timestamp is older than the source timestamp
      if (!file_exists($f_dst) || filemtime($f_dst) < filemtime($f_src) )
      {
        mkdir_r($this->data_public_path.'/js/');
        copy( $f_src, $f_dst );
      }
      if (!file_exists($f_dst)) $this->errors[] = _pfc("%s doesn't exist, data_public_path cannot be installed", $f_dst);
    }
    closedir($dh);


    // ---
    // test client script
    // try to find the path into server configuration
    if ($this->client_script_path == '')
      $this->client_script_path = getScriptFilename();


    if ($this->server_script_url == '' && $this->server_script_path == '')
    {    
      $filetotest = $this->client_script_path;
      // do not take into account the url parameters
      if (preg_match("/(.*)\?(.*)/", $filetotest, $res))
        $filetotest = $res[1];
      if ( !file_exists($filetotest) )
        $this->errors[] = _pfc("%s doesn't exist", $filetotest);   
      $this->server_script_url  = './'.basename($filetotest).$this->_query_string;
    }
  
    //if ($this->client_script_url == "")
    //      $this->client_script_url = "./".basename($filetotest);
    
    // calculate datapublic url
    if ($this->data_public_url == "")
      $this->data_public_url = relativePath($this->client_script_path, $this->data_public_path);

    if ($this->server_script_path == '')
      $this->server_script_path = $this->client_script_path;
    
    // ---
    // test server script    
    if ($this->server_script_url == '')
    {
      $filetotest = $this->server_script_path;
      // do not take into account the url parameters
      if (preg_match("/(.*)\?(.*)/",$this->server_script_path, $res))
        $filetotest = $res[1];
      if ( !file_exists($filetotest) )
        $this->errors[] = _pfc("%s doesn't exist", $filetotest);
      $this->server_script_url = relativePath($this->client_script_path, $this->server_script_path).'/'.basename($filetotest).$this->_query_string;
    }

    // check if the theme_path parameter are correctly setup
    if ($this->theme_default_path == '' || !is_dir($this->theme_default_path))
      $this->theme_default_path = dirname(__FILE__).'/../themes';
    if ($this->theme_path == '' || !is_dir($this->theme_path))
      $this->theme_path = $this->theme_default_path;

    // If the user didn't give any theme_default_url value,
    // copy the default theme resources in a public directory
    if ($this->theme_default_url == '')
    {
      mkdir_r($this->data_public_path.'/themes/default');
      if (!is_dir($this->data_public_path.'/themes/default'))
        $this->errors[] = _pfc("cannot create %s", $this->data_public_path.'/themes/default');
      else
      {
        $ret = copy_r( dirname(__FILE__).'/../themes/default',
                       $this->data_public_path.'/themes/default' );
        if (!$ret)
          $this->errors[] = _pfc("cannot copy %s in %s",
                                 dirname(__FILE__).'/../themes/default',
                                 $this->data_public_path.'/themes/default');
      }
      $this->theme_default_url = $this->data_public_url.'/themes';
    }
    if ($this->theme_url == '')
    {
      mkdir_r($this->data_public_path.'/themes/'.$this->theme);
      if (!is_dir($this->data_public_path.'/themes/'.$this->theme))
        $this->errors[] = _pfc("cannot create %s", $this->data_public_path.'/themes/'.$this->theme);
      else
      {
        $ret = copy_r( $this->theme_path.'/'.$this->theme,
                       $this->data_public_path.'/themes/'.$this->theme );
        if (!$ret)
          $this->errors[] = _pfc("cannot copy %s in %s",
                                 $this->theme_path.'/'.$this->theme,
                                 $this->data_public_path.'/themes/'.$this->theme);
      }      
      $this->theme_url = $this->theme_default_url;
    }

    // if the user do not have an existing prototype.js library, we use the embeded one
    if ($this->prototypejs_url == '') $this->prototypejs_url = $this->data_public_url.'/js/prototype.js';

    // ---
    // run specific container initialisation
    $ct =& pfcContainer::Instance();
    /*    $container_classname = "pfcContainer_".$this->container_type;
    require_once dirname(__FILE__)."/containers/".strtolower($this->container_type).".class.php";
    $container = new $container_classname($this);*/
    $ct_errors = $ct->init($this);
    $this->errors = array_merge($this->errors, $ct_errors);
    
    // load debug url
    $this->debugurl = relativePath($this->client_script_path, dirname(__FILE__)."/../debug");

    // check the language is known
    $lg_list = pfcI18N::GetAcceptedLanguage();
    if ( $this->language != "" && !in_array($this->language, $lg_list) )
      $this->errors[] = _pfc("'%s' parameter is not valid. Available values are : '%s'", "language", implode(", ", $lg_list));

    // calculate the proxies chaine
    $this->proxies = array();
    foreach($this->pre_proxies as $px)
    {
      if (!in_array($px,$this->skip_proxies) && !in_array($px,$this->proxies))
        $this->proxies[] = $px;
        
    }
    foreach($this->_sys_proxies as $px)
    {
      if (!in_array($px,$this->skip_proxies) && !in_array($px,$this->proxies))
        $this->proxies[] = $px;
        
    }
    foreach($this->post_proxies as $px)
    {
      if (!in_array($px,$this->skip_proxies) && !in_array($px,$this->proxies))
        $this->proxies[] = $px;
        
    }
    // save the proxies path
    $this->proxies_path_default = dirname(__FILE__).'/proxies';
    // check the customized proxies path
    if ($this->proxies_path != '' && !is_dir($this->proxies_path))
      $this->errors[] = _pfc("'%s' directory doesn't exist", $this->proxies_path);
    if ($this->proxies_path == '') $this->proxies_path = $this->proxies_path_default;
    
    // save the commands path
    $this->cmd_path_default = dirname(__FILE__).'/commands';
    if ($this->cmd_path == '') $this->cmd_path = $this->cmd_path_default;
        
    // load smileys from file
    $this->loadSmileyTheme();
    
    // load version number from file
    $this->version = trim(file_get_contents(dirname(__FILE__)."/../version.txt"));
    
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
    $theme = file($this->getFilePathFromTheme("smileys/theme.txt"));
    $result = array();
    foreach($theme as $line)
    {
      $line = trim($line);
      if (preg_match("/^#.*/",$line))
        continue;
      else if (preg_match("/([a-z_\-0-9\.]+)(.*)$/i",$line,$res))
      {
        $smiley_file = 'smileys/'.$res[1];
        $smiley_str = trim($res[2])."\n";
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
    return $this->serverid;
  }  

  /*
  function _getProxyFile($serverid = "", $data_public_path = "")
  {
    if ($serverid == "")          $serverid = $this->getId();
    if ($data_public_path == "") $data_public_path = $this->data_public_path;
    return $data_public_path."/".$serverid."/proxy.php";
  }
  */
  
  function _GetCacheFile($serverid = "", $data_private_path = "")
  {
    if ($serverid == '')          $serverid = $this->getId();
    if ($data_private_path == '') $data_private_path = $this->data_private_path;
    return $data_private_path.'/cache/'.$serverid.'.php';
  }
  
  function destroyCache()
  {
    $cachefile = $this->_GetCacheFile();
    if (!file_exists($cachefile))
      return false;
    $this->is_init = false;
    // destroy the cache lock file
    $cachefile_lock = $cachefile."_lock";
    if (file_exists($cachefile_lock)) @unlink($cachefile_lock);
    // destroy the cache file
    return @unlink($cachefile);
  }
  
  /**
   * Save the pfcConfig object into cache if it doesn't exists yet
   * else restore the old pfcConfig object
   */
  function synchronizeWithCache()
  {
    $cachefile = $this->_GetCacheFile();
    $cachefile_lock = $cachefile."_lock";

    if (file_exists($cachefile))
    {
      // if a cache file exists, remove the lock file because config has been succesfully stored
      if (file_exists($cachefile_lock)) @unlink($cachefile_lock);

      include $cachefile;
      foreach($pfc_conf as $key => $val)
        // the dynamics parameters must not be cached
        if (!in_array($key,$this->_dyn_params))
          $this->$key = $val;

      return true; // synchronized
    }
    else
    {
      if (file_exists($cachefile_lock))
      {
        // delete too old lockfiles (more than 15 seconds)
        $locktime = filemtime($cachefile_lock);
        if ($locktime+15 < time())
          unlink($cachefile_lock);
        else
          return false; // do nothing if the lock file exists
      }
      else
        @touch($cachefile_lock); // create the lockfile
      
      if (!$this->isInit())
        $this->init();
      $errors =& $this->getErrors();
      if (count($errors) > 0)
      {
        @unlink($cachefile_lock); // destroy the lock file for the next attempt
        echo "<p>"._pfc("Please correct these errors").":</p><ul>"; foreach( $errors as $e ) echo "<li>".$e."</li>"; echo "</ul>";
        exit;
      }
      // save the validated config in cache
      $this->saveInCache();
      return false; // new cache created
    }
  }
  function saveInCache()
  {
    $cachefile = $this->_GetCacheFile();
    $data = '<?php ';

    $conf = get_object_vars($this);
    $keys = array_keys($conf);
    foreach($keys as $k)
      if (preg_match('/^_.*/',$k))
        unset($conf[$k]);

    // remove dynamic parameters
    foreach($this->_dyn_params as $k)
      unset($conf[$k]);

    $data .= '$pfc_conf = '.var_export($conf,true).";\n";
    $data .= '?>';
    
    file_put_contents($cachefile, $data/*serialize(get_object_vars($this))*/);
    if ($this->debug) pxlog("pfcGlobalConfig::saveInCache()", "chatconfig", $this->getId());
  }

  function isDefaultFile($file)
  {
    $fexists1 = file_exists($this->theme_path."/default/".$file);
    $fexists2 = file_exists($this->theme_path."/".$this->theme."/".$file);
    return ($this->theme == "default" ? $fexists1 : !$fexists2);
  }

  /*
  function getFileUrlByProxy($file, $addprefix = true)
  {
    if (file_exists($this->theme_path."/".$this->theme."/".$file))
      return ($addprefix ? $this->data_public_url."/".$this->getId()."/proxy.php" : "")."?p=".$this->theme."/".$file;
    else
      if (file_exists($this->theme_default_path."/default/".$file))
        return ($addprefix ? $this->data_public_url."/".$this->getId()."/proxy.php" : "")."?p=default/".$file;
      else
	die(_pfc("Error: '%s' could not be found, please check your theme_path '%s' and your theme '%s' are correct", $file, $this->theme_path, $this->theme));
  }
  */
    
  function getFilePathFromTheme($file)
  {
    if (file_exists($this->theme_path."/".$this->theme."/".$file))
      return $this->theme_path."/".$this->theme."/".$file;
    else
      if (file_exists($this->theme_default_path."/default/".$file))
        return $this->theme_default_path."/default/".$file;
      else
      {
        $this->destroyCache();
        die(_pfc("Error: '%s' could not be found, please check your themepath '%s' and your theme '%s' are correct", $file, $this->theme_path, $this->theme));
      }
  }

  function getFileUrlFromTheme($file)
  {
    if (file_exists($this->theme_path.'/'.$this->theme.'/'.$file))
      return $this->theme_url.'/'.$this->theme.'/'.$file;
    else
      if (file_exists($this->theme_default_path.'/default/'.$file))
        return $this->theme_default_url.'/default/'.$file;
      else
        return 'notfound';
  }


  function filterNickname($nickname)
  {
    $nickname = trim($nickname);
    require_once dirname(__FILE__)."/../lib/utf8/utf8_substr.php";
    $nickname = (string)utf8_substr($nickname, 0, $this->max_nick_len);
    return $nickname;
  }
}

?>
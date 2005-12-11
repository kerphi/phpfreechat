<?php

class phpChatConfig
{
  var $nick = "";
  var $id;
  var $default_params = array();
  var $errors = array();
  var $is_init = false;
  
  function phpChatConfig( $params = array() )
  {
    $this->default_params["title"]               = "My phpChat";
    $this->default_params["init_nick"]           = "";
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
    $this->default_params["cache_dir"]           = dirname(__FILE__)."/cache/";
    $this->default_params["debug"]               = false;
    $this->default_params["prefix"]              = "phpchat_";
    $this->default_params["container_type"]      = ($params["container_type"]!="") ? $params["container_type"] : "File";

    // set defaults values
    foreach ( $this->default_params as $k => $v ) $this->$k = $v;
    
    // set user's values
    foreach ( $params as $k => $v ) $this->$k = $v;

    // load default container's config
    $container =& $this->getContainerInstance();
    $container_cfg = $container->getDefaultConfig();
    foreach( $container_cfg as $k => $v )
    {
      $attr = "container_cfg_".$k;
      if (!isset($this->$attr))
        $this->$attr = $v;
    }

    // calculate the chat id
    $this->id = md5(serialize($this));
  }
  
  function &getContainerInstance()
  {
    $container_classname = "phpChat_Container_".$this->container_type;
    require_once dirname(__FILE__)."/".strtolower($container_classname).".class.php";
    $container = new $container_classname($this);
    return $container;
  }
  
  function init()
  {
    $this->errors = array();
    $ok = true;
    
    // ---
    // test cache directory
    if ($ok && $this->cache_dir == "")
    {
      $ok = false;
      $this->errors[] = "cache directory must be specified";
    }
    if ($ok && is_file($this->cache_dir))
    {
      $ok = false;
      $this->errors[] = $this->cache_dir." must be a directory";
    }      
    if ($ok && !is_dir($this->cache_dir))
      @mkdir($this->cache_dir);
    if ($ok && !is_dir(dirname($this->cache_dir)))
    {
      $ok = false;
      $this->errors[] = dirname($this->cache_dir)." can't be created";
    }      
    if ($ok && !is_writeable($this->cache_dir))
    {
      $ok = false;
      $this->errors[] = $this->cache_dir." is not writeable";
    }
    if ($ok && !is_readable($this->cache_dir))
    {
      $ok = false;
      $this->errors[] = $this->cache_dir." is not readable";
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
      $container_classname = "phpChat_Container_".$this->default_params["container_type"];
      require_once dirname(__FILE__)."/".strtolower($container_classname).".class.php";
      $container = new $container_classname($this);
      $container_errors = $container->init();
      if (count($container_errors)>0)
      {
        $this->errors = array_merge($this->errors, $container_errors);
        $ok = false;
      }
    }
    
    if ($this->init_nick != "")
      $this->nick = $this->init_nick;

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
  
  function assignToSmarty( &$smarty )
  {
    foreach ( $this->default_params as $p_k => $p_v )
      $smarty->assign($p_k, $this->$p_k);
  }
}

?>
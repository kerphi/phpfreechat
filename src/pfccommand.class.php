<?php
/**
 * pfccommand.class.php
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
require_once dirname(__FILE__)."/pfci18n.class.php";
require_once dirname(__FILE__)."/pfcuserconfig.class.php";

/**
 * pfcCommand is an abstract class (interface) which must be inherited by each concrete commands
 * Commands examples : /nick /me /update ...
 *
 * @example ../demo/demo27_customized_command.php
 * @author Stephane Gully <stephane.gully@gmail.com>
 */
class pfcCommand
{
  /**
   * Command name (lowercase)
   */
  var $name;

  /**
   * Contains the command syntaxe (how to use the command)
   */
  var $usage;
  
  /**
   * Not used for now
   */
  var $desc;
  var $help;

  /**
   * This is the pfcGlobalConfig instance
   */
  var $c;
  
  /**
   * This is the pfcUserConfig instance
   */
  var $u;

  /**
   * Used to instanciate a command
   * $tag is the command name : "nick", "me", "update" ...
   */
  function &Factory($name)
  {
    $c =& pfcGlobalConfig::Instance();

    // instanciate the real command
    $cmd           = NULL;
    $cmd_name      = strtolower($name);
    $cmd_classname = "pfcCommand_".$name;
    if (!class_exists($cmd_classname))
    {
      $cmd_paths = array($c->cmd_path_default,$c->cmd_path);
      foreach($cmd_paths as $cp)
      {
        $cmd_filename  = $cp."/".$cmd_name.".class.php";
        if (@file_exists($cmd_filename)) require_once($cmd_filename);
      }
    }
    if (class_exists($cmd_classname))
    {
      $cmd =& new $cmd_classname();
      $cmd->name = $cmd_name;
      
      // instanciate the proxies chaine
      $firstproxy =& $cmd;
      for($i = count($c->_proxies)-1; $i >= 0; $i--)
      {
        $proxy_name      = $c->_proxies[$i];
        $proxy_classname = "pfcProxyCommand_" . $proxy_name;
        if (!class_exists($proxy_classname))
        {
          // try to include the proxy class file from the default path or from the customized path
          $proxy_filename  = $c->proxies_path_default.'/'.$proxy_name.".class.php";
          if (file_exists($proxy_filename))
            require_once($proxy_filename);
          else
          {
            $proxy_filename = $c->proxies_path.'/'.$proxy_name.".class.php";
            if (file_exists($proxy_filename)) require_once($proxy_filename);
          }
        }
        if (class_exists($proxy_classname))
        {
          // instanciate the proxy
          $proxy =& new $proxy_classname();
          $proxy->name      = $cmd_name;
          $proxy->proxyname = $proxy_name;
          $proxy->linkTo($firstproxy);
          $firstproxy =& $proxy;
        }
      }
      // return the proxy, not the command (the proxy will forward the request to the real command)
      return $firstproxy;
    }
    return $cmd;
  }

  /**
   * Constructor
   * @private
   */
  function pfcCommand()
  {
    $this->c =& pfcGlobalConfig::Instance();
    $this->u =& pfcUserConfig::Instance();
  }

  /**
   * Virtual methode which must be implemented by concrete commands
   * It is called by the phpFreeChat::HandleRequest function to execute the wanted command
   */
  function run(&$xml_reponse, $p)
  {
    die(_pfc("%s must be implemented", get_class($this)."::".__FUNCTION__));
  }
  
  /**
   * Force whois reloading
   */
  function forceWhoisReload($nicktorewhois)
  {
    $c  = $this->c;
    $u  = $this->u;
    $ct =& $c->getContainerInstance();

    $nickid = $ct->getNickid($nicktorewhois);

    // get the user who have $nicktorewhois in their list
    $channels = $ct->getMeta("nickid-to-channelid", $nickid);
    $channels = $channels['value'];
    $channels = array_diff($channels, array('SERVER'));
    $otherids = array();
    foreach($channels as $chan)
    {
      $ret = $ct->getOnlineNick($ct->decode($chan));
      $otherids = array_merge($otherids, $ret['nickid']);
    }
    
    // alert them that $nicktorewhois user info just changed
    foreach($otherids as $otherid)
    {
      $cmdstr = 'whois2';
      $cmdp = array();
      $cmdp['param'] = $nicktorewhois;
      pfcCommand::AppendCmdToPlay($otherid, $cmdstr, $cmdp);
      
      /*
      $cmdtoplay = $ct->getUserMeta($otherid, 'cmdtoplay');
      $cmdtoplay = ($cmdtoplay == NULL) ? array() : unserialize($cmdtoplay);
      $cmdtmp = array("whois2",    // cmdname 
                      $nicktorewhois,   // param 
                      NULL,       // sender 
                      NULL,       // recipient 
                      NULL,       // recipientid 
                      );
      if (!in_array($cmdtmp, $cmdtoplay))
      {
        $cmdtoplay[] = $cmdtmp;
        $ct->setUserMeta($otherid, 'cmdtoplay', serialize($cmdtoplay));
      }
      */
    }
  }

  function AppendCmdToPlay($nickid, $cmdstr, $cmdp)
  {
    $c =& pfcGlobalConfig::Instance();
    $u =& pfcUserConfig::Instance();
    
    $ct =& $c->getContainerInstance();
    if ($nickid != "")
    {
      $cmdtoplay = $ct->getUserMeta($nickid, 'cmdtoplay');
      $cmdtoplay = ($cmdtoplay == NULL) ? array() : unserialize($cmdtoplay);
      $cmdtmp = array();
      $cmdtmp['cmdstr'] = $cmdstr;
      $cmdtmp['params'] = $cmdp;
      $cmdtoplay[] = $cmdtmp;
      $ct->setUserMeta($nickid, 'cmdtoplay', serialize($cmdtoplay));
      return true;
    }
    else
      return false;
  }

  function RunPendingCmdToPlay($nickid,$clientid,&$xml_reponse)
  {
    $c =& pfcGlobalConfig::Instance();
    $u =& pfcUserConfig::Instance();
    $ct =& $c->getContainerInstance();

    $morecmd = true;
    while($morecmd)
    {
      // take a command from the list
      $cmdtoplay = $ct->getUserMeta($nickid, 'cmdtoplay');
      $cmdtoplay = ($cmdtoplay == NULL) ? array() : unserialize($cmdtoplay);
      if (count($cmdtoplay) == 0) { $morecmd = false; continue; }
      // take the last posted command
      $cmdtmp = array_pop($cmdtoplay);
      // store the new cmdtoplay list (-1 item)
      $ct->setUserMeta($nickid, 'cmdtoplay', serialize($cmdtoplay));
      
      // play the command
      //      print_r($cmdtmp);
      $cmd =& pfcCommand::Factory($cmdtmp['cmdstr']);
      $cmdp = $cmdtmp['params'];
      if (!isset($cmdp['param']))       $cmdp['param'] = '';
      if (!isset($cmdp['sender']))      $cmdp['sender'] = null;
      if (!isset($cmdp['recipient']))   $cmdp['recipient']   = null;      
      if (!isset($cmdp['recipientid'])) $cmdp['recipientid'] = null;      
      $cmdp['clientid']  = $clientid; // the clientid must be the current user one
      $cmdp['cmdtoplay'] = true; // used to run some specials actions in the command (ex:  if the cmdtoplay is a 'leave' command, then show an alert to the kicked or banished user)
      if ($c->debug)
        $cmd->run($xml_reponse, $cmdp);
      else
        @$cmd->run($xml_reponse, $cmdp);
      
      // check if there is other command to play
      $cmdtoplay = $ct->getUserMeta($nickid, 'cmdtoplay');
      $cmdtoplay = ($cmdtoplay == NULL) ? array() : unserialize($cmdtoplay);        

      $morecmd = (count($cmdtoplay) > 0);
    }
  }

  
  function trace(&$xml_reponse, $msg, $data = NULL)
  {
    if ($data != NULL)
    {
      require_once dirname(__FILE__).'/pfcjson.class.php';
      $json = new pfcJSON();      
      $js = $json->encode($data);
      $xml_reponse->script("trace('".$msg." -> ".$js."');");
    }
    else
      $xml_reponse->script("trace('".$msg."');");

  }

  function ParseCommand($cmd_str)
  {
    $pattern_quote   = '/([^\\\]|^)"([^"]+[^\\\])"/';
    $pattern_quote   = '/"([^"]+)"/';
    $pattern_noquote = '/([^"\s]+)/';
    $pattern_command = '/^\/([a-z0-9]+)\s*(.*)/';
    $result = array();
  
    // parse the command name (ex: '/invite')
    if (preg_match($pattern_command, $cmd_str, $res))
    {
      $cmd = $res[1];
      $params_str = $res[2];
      // parse the quotted parameters (ex: '/invite "nickname with spaces"')
      preg_match_all($pattern_quote,$params_str,$res1,PREG_OFFSET_CAPTURE);
      $params_res = $res1[1];
      // split the parameters string
      $nospaces = preg_split($pattern_quote,$params_str,-1,PREG_SPLIT_OFFSET_CAPTURE|PREG_SPLIT_NO_EMPTY);
      foreach($nospaces as $p)
      {
        // parse the splited blocks with unquotted parameter pattern (ex: '/invite nicknamewithoutspace')
        preg_match_all($pattern_noquote,$p[0],$res2,PREG_OFFSET_CAPTURE);
        foreach( $res2[1] as $p2 )
        {
          $p2[1] += $p[1];
          $params_res[] = $p2;
        }
      }

      // order the array by offset
      $params = array();
      foreach($params_res as $p) $params[$p[1]] = $p[0];
      ksort($params);
      $params = array_values($params);
      $params = array_map("trim",$params);
    
      $result['cmdstr']  = $cmd_str;
      $result['cmdname'] = $cmd;
      $result['params']  = $params;
    }
    return $result;
  }
  
}

?>
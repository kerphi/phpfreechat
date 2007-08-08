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
  var $name = '';

  /**
   * Contains the command syntaxe (how to use the command)
   */
  var $usage = '';
  
  /**
   * Not used for now
   */
  var $desc = '';
  var $help = '';

  /**
   * Used to instanciate a command
   * $tag is the command name : "nick", "me", "update" ...
   */
  function &Factory($name)
  {
    $c =& pfcGlobalConfig::Instance();

    // instanciate the real command
    $cmd           = NULL;
    $cmd_name      = $name;
    $cmd_classname = "pfcCommand_".$name;

    $cmd_filename  = $c->cmd_path_default.'/'.$cmd_name.'.class.php';
    if (file_exists($cmd_filename)) require_once($cmd_filename);
    $cmd_filename  = $c->cmd_path.'/'.$cmd_name.'.class.php';
    if (file_exists($cmd_filename)) require_once($cmd_filename);
    
    if (!class_exists($cmd_classname)) { $tmp = NULL; return $tmp; }
    
    $cmd =& new $cmd_classname;
    $cmd->name = $cmd_name;
      
    // instanciate the proxies chaine
    $firstproxy =& $cmd;
    for($i = count($c->proxies)-1; $i >= 0; $i--)
    {
      $proxy_name      = $c->proxies[$i];
      $proxy_classname = "pfcProxyCommand_" . $proxy_name;

      // try to include the proxy class file from the default path or from the customized path
      $proxy_filename  = $c->proxies_path_default.'/'.$proxy_name.".class.php";
      if (file_exists($proxy_filename)) require_once($proxy_filename);
      $proxy_filename  = $c->proxies_path.'/'.$proxy_name.".class.php";
      if (file_exists($proxy_filename)) require_once($proxy_filename);
            
      if (!class_exists($proxy_classname))
        return $firstproxy;
      
      // instanciate the proxy
      $proxy =& new $proxy_classname;
      $proxy->name      = $cmd_name;
      $proxy->proxyname = $proxy_name;
      $proxy->linkTo($firstproxy);
      $firstproxy =& $proxy;
    }

    /*
    $tmp = '';
    $cur = $firstproxy;
    while($cur)
    {
      $tmp .= (isset($cur->proxyname)?$cur->proxyname:$cur->name).'|';
      $cur = $cur->next;
    }
    $tmp .= var_export($firstproxy,true);
    file_put_contents('/tmp/debug1',$tmp);
*/
    
    // return the proxy, not the command (the proxy will forward the request to the real command)
    return $firstproxy;
  }

  /**
   * Constructor
   * @private
   */
  function pfcCommand()
  {
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
  function forceWhoisReload($nickid)
  {
    $c =& pfcGlobalConfig::Instance();
    $u =& pfcUserConfig::Instance();
    $ct =& pfcContainer::Instance();

    // list the users in the same channel as $nickid
    $channels = $ct->getMeta("nickid-to-channelid", $nickid);
    $channels = $channels['value'];
    $channels = array_diff($channels, array('SERVER'));
    $otherids = array();
    foreach($channels as $chan)
    {
      $ret = $ct->getOnlineNick($ct->decode($chan));
      $otherids = array_merge($otherids, $ret['nickid']);
    }
    
    // alert them that $nickid user info just changed
    foreach($otherids as $otherid)
    {
      $cmdstr = 'whois2';
      $cmdp = array();
      $cmdp['params'] = array($nickid);
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

  /**
   * Add command to be played onto command stack
   * @param $nickid is the user that entered the command
   * @param $cmdstr is the command
   * @param $cmdp is the command's parameters
   * @return false if $nickid is blank, true for all other values of $nickid
   */
  function AppendCmdToPlay($nickid, $cmdstr, $cmdp)
  {
    $c =& pfcGlobalConfig::Instance();
    $u =& pfcUserConfig::Instance();
    
    $ct =& pfcContainer::Instance();

    // check for empty nickid
    if ($nickid == "") return false;

    // get new command id
    $cmdtoplay_id = $ct->incMeta("nickid-to-cmdtoplayid", $nickid, 'cmdtoplayid');
    if (count($cmdtoplay_id["value"]) == 0)
      $cmdtoplay_id = 0;
    else
      $cmdtoplay_id = $cmdtoplay_id["value"][0];

    // create command array
    $cmdtoplay = array();
    $cmdtoplay['cmdstr'] = $cmdstr;
    $cmdtoplay['params'] = $cmdp;
    
    // store command to play
    $ct->setCmdMeta($nickid, $cmdtoplay_id, serialize($cmdtoplay));
    
    return true;
  }


  /**
   * Run all commands to be played for a user
   * @param $nickid is the user that entered the command
   * @param $context
   * @param $xml_reponse
   */
  function RunPendingCmdToPlay($nickid, $context, &$xml_reponse)
  {
    $c =& pfcGlobalConfig::Instance();
    $u =& pfcUserConfig::Instance();
    $ct =& pfcContainer::Instance();

    // Get all queued commands to be played
    $cmdtoplay_ids = $ct->getCmdMeta($nickid);
    // process each command and parse content
    foreach ( $cmdtoplay_ids as $cid )
    {
      // take a command from the list
      $cmdtoplay = $ct->getCmdMeta($nickid, $cid);
      $cmdtoplay = ($cmdtoplay == NULL || count($cmdtoplay) == 0) ? array() : unserialize($cmdtoplay[0]);

      // play the command
      $cmd =& pfcCommand::Factory($cmdtoplay['cmdstr']);
      $cmdp = $cmdtoplay['params'];
      if (!isset($cmdp['param']))       $cmdp['param'] = '';
      if (!isset($cmdp['sender']))      $cmdp['sender'] = $context['sender'];
      if (!isset($cmdp['recipient']))   $cmdp['recipient']   = $context['recipient'];
      if (!isset($cmdp['recipientid'])) $cmdp['recipientid'] = $context['recipientid'];
      $cmdp['clientid']  = $context['clientid']; // the clientid must be the current user one
      $cmdp['cmdtoplay'] = true; // used to run some specials actions in the command (ex:  if the cmdtoplay is a 'leave' command, then show an alert to the kicked or banished user)
      if ($c->debug)
        $cmd->run($xml_reponse, $cmdp);
      else
        @$cmd->run($xml_reponse, $cmdp);

      // delete command when complete
      $ct->rmMeta("nickid-to-cmdtoplay", $nickid, $cid);
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

  function ParseCommand($cmd_str, $one_parameter = false)
  {
    $pattern_quote   = '/([^\\\]|^)"([^"]+[^\\\])"/';
    $pattern_quote   = '/"([^"]+)"/';
    $pattern_noquote = '/([^"\s]+)/';
    $pattern_command = '/^\/([a-z0-9]+)\s*([a-z0-9]+)\s*([a-z0-9]+)\s*(.*)/';
    $result = array();
    
    // parse the command name (ex: '/invite')
    if (preg_match($pattern_command, $cmd_str, $res))
    {
      $cmd         = $res[1];
      $clientid    = $res[2];
      $recipientid = $res[3];
      $params_str  = $res[4];

      // don't parse multiple parameters for special commands with only one parameter
      // this make possible to send double quotes (") in these commands
      if ($one_parameter || $cmd == 'send' || $cmd == 'notice' || $cmd == 'me')
      {
        $result['cmdstr']  = $cmd_str;
        $result['cmdname'] = $cmd;
        $result['params']  = array($clientid, $recipientid, $params_str);
        return $result;
      }


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
      $params = array_merge(array($clientid,$recipientid), $params);
      
      $result['cmdstr']  = $cmd_str;
      $result['cmdname'] = $cmd;
      $result['params']  = $params;
    }
    return $result;
  }
  
  /*
  // THIS IS ANOTHER WAY TO PARSE THE PARAMETERS
  // IT'S NOT SIMPLIER BUT MAYBE FASTER
  // @todo : take the faster methode
  function ParseCommand($cmd_str, $one_parameter = false)
  {
    $pattern_command = '/^\/([a-z0-9]+)\s*([a-z0-9]+)\s*([a-z0-9]+)\s*(.*)/';
    $result = array();
    
    // parse the command name (ex: '/invite')
    if (preg_match($pattern_command, $cmd_str, $res))
    {
      $cmd         = $res[1];
      $clientid    = $res[2];
      $recipientid = $res[3];
      $params_str  = $res[4];

      // don't parse multiple parameters for special commands with only one parameter
      // this make possible to send double quotes (") in these commands
      if ($one_parameter || $cmd == 'send' || $cmd == 'notice' || $cmd == 'me')
      {
        $result['cmdstr']  = $cmd_str;
        $result['cmdname'] = $cmd;
        $result['params']  = array($clientid, $recipientid, $params_str);
        return $result;
      }

      $params = array($clientid, $recipientid);
      $sep    = preg_match('/[^\\\\]"/',$params_str) ? '"' : ' ';
      if ($sep == ' ') $params_str = ' ' . $params_str;
      $offset = 0;
      while (1)
      {
        $i1 = strpos($params_str,$sep,$offset);
        // capture the parameter value
        if ($i1 !== FALSE)
        {
          // remove multi-separators
          while (1)
          {
            if (strpos($params_str,$sep,$i1+1) - $i1 == 1)
              $i1++;
            else
              break;
          }
          // search the parameter terminason
          $offset = $i1+1;
          $i2 = strpos($params_str,$sep,$offset);
          if ($i2 !== FALSE)
          {
            $offset = $i2 + ($sep == '"' ? 1 : 0);
            $p = substr($params_str, $i1+1, $i2-$i1-1);
            if (!preg_match('/^\s*$/',$p))
              $params[] = $p;
          }
          else
            break;
        }
        else
          break;
      }
      // append the tail
      if ($offset < strlen($params_str))
        $params[] = substr($params_str,$offset);
      
      $result['cmdstr']  = $cmd_str;
      $result['cmdname'] = $cmd;
      $result['params']  = $params;
    }
    return $result;
  }
*/

  
}

?>
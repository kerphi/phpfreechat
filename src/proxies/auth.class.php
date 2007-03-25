<?php
/**
 * auth.class.php
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
require_once dirname(__FILE__)."/../pfci18n.class.php";
require_once dirname(__FILE__)."/../pfcuserconfig.class.php";
require_once dirname(__FILE__)."/../pfcproxycommand.class.php";

/**
 * pfcProxyCommand_auth
 *
 * @author Stephane Gully <stephane.gully@gmail.com>
 */
class pfcProxyCommand_auth extends pfcProxyCommand
{
  function run(&$xml_reponse, $p)
  {
    $clientid    = $p["clientid"];
    $param       = $p["param"];
    $sender      = $p["sender"];
    $recipient   = $p["recipient"];
    $recipientid = $p["recipientid"];

    $c =& pfcGlobalConfig::Instance();
    $u =& pfcUserConfig::Instance();


    // do not allow someone to run a command if he is not online
    if ( !$u->isOnline() &&
         $this->name != 'error' &&
         $this->name != 'connect' &&
         $this->name != 'update' )
    {
      $cmdp = $p;
      $cmdp["param"] = _pfc("Your must be connected to send a message");
      $cmd =& pfcCommand::Factory("error");
      $cmd->run($xml_reponse, $cmdp);
      return false;
    }

    
    // protect admin commands
    $admincmd = array("kick", "ban", "unban", "op", "deop", "debug", "rehash");
    if ( in_array($this->name, $admincmd) )
    {
      $container =& pfcContainer::Instance();
      $nickid = $u->nickid;
      $isadmin = $container->getUserMeta($nickid, 'isadmin');
      if (!$isadmin)
      {
        $xml_reponse->script("alert('".addslashes(_pfc("You are not allowed to run '%s' command", $this->name))."');");
        return false;
      }
    }    
    
    // channels protection
    if ($this->name == "join" ||
        $this->name == "join2")
    {
      $container   =& pfcContainer::Instance();
      $channame    = $param;
      
      // check the user is not listed in the banished channel list
      $chan        = pfcCommand_join::GetRecipient($channame);
      $chanid      = pfcCommand_join::GetRecipientId($channame);
      $banlist     = $container->getChanMeta($chan, 'banlist_nickid');
      if ($banlist == NULL) $banlist = array(); else $banlist = unserialize($banlist);
      $nickid = $u->nickid;
      if (in_array($nickid,$banlist))
      {
        // the user is banished, show a message and don't forward the /join command
        $msg = _pfc("Can't join %s because you are banished", $param);
        $xml_reponse->script("pfc.handleResponse('".$this->proxyname."', 'ban', '".addslashes($msg)."');");
        return false;
      }

      if (count($c->frozen_channels)>0)
      {
        if (!in_array($channame,$c->frozen_channels))
        {
          // the user is banished, show a message and don't forward the /join command
          $msg = _pfc("Can't join %s because the channels list is restricted", $param);
          $xml_reponse->script("pfc.handleResponse('".$this->proxyname."', 'frozen', '".addslashes($msg)."');");
          return false;
        }
      }
    }
    
    // forward the command to the next proxy or to the final command
    $p["clientid"]    = $clientid;
    $p["param"]       = $param;
    $p["sender"]      = $sender;
    $p["recipient"]   = $recipient;
    $p["recipientid"] = $recipientid;
    return $this->next->run($xml_reponse, $p);
  }
}

?>

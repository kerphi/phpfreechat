<?php
/**
 * noflood.class.php
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
 * pfcProxyCommand_noflood
 * this proxy will protect the chat from flooders
 * @author Stephane Gully <stephane.gully@gmail.com>
 */
class pfcProxyCommand_noflood extends pfcProxyCommand
{
  function run(&$xml_reponse, $p)
  {
    $clientid    = $p["clientid"];
    $param       = $p["param"];
    $sender      = $p["sender"];
    $recipient   = $p["recipient"];
    $recipientid = $p["recipientid"];

    $c =& $this->c;
    $u =& $this->u;

    $cmdtocheck = array("send", "nick", "me");
    if ( in_array($this->name, $cmdtocheck) )
    {
      $container =& $c->getContainerInstance();
      $nickid        = $u->nickid;
      $isadmin       = $container->getUserMeta($nickid, 'isadmin');
      $lastfloodtime = $container->getUserMeta($nickid, 'floodtime');
      $nbflood       = $container->getUserMeta($nickid, 'nbflood');
      $floodtime     = time();
      
      if ($floodtime - $lastfloodtime <= $c->proxys_cfg[$this->proxyname]["delay"])
        $nbflood++;
      else
        $nbflood = 0;
      
      if ($nbflood>$c->proxys_cfg[$this->proxyname]["limit"])
      {
        // warn the flooder
        $msg = _pfc("Please don't post so many message, flood is not tolerated");
        $xml_reponse->addScript("alert('".addslashes($msg)."');");

        // kick the flooder
        $cmdp = $p;
        $cmdp["param"] = $recipientid." ";
        $cmdp["param"] .=_pfc("kicked from %s by %s", $u->channels[$recipientid]["name"], "noflood");
        $cmd =& pfcCommand::Factory("leave");
        $cmd->run($xml_reponse, $cmdp);
        return;
      }

      if ($nbflood == 0)
        $container->setUserMeta($nickid, 'floodtime', $floodtime);
      $container->setUserMeta($nickid, 'nbflood', $nbflood);
    }
    
    // forward the command to the next proxy or to the final command
    $p["clientid"]    = $clientid;
    $p["param"]       = $param;
    $p["sender"]      = $sender;
    $p["recipient"]   = $recipient;
    $p["recipientid"] = $recipientid;
    $this->next->run($xml_reponse, $p);
  }
}

?>

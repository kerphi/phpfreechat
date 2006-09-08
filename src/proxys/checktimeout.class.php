<?php
/**
 * checktimeout.class.php
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
 * pfcProxyCommand_checktimeout
 * this command disconnect obsolete users (timouted)
 * an obsolete user is an user which didn't update his stats since more than 20 seconds (timeout value)
 * @author Stephane Gully <stephane.gully@gmail.com>
 */
class pfcProxyCommand_checktimeout extends pfcProxyCommand
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

    if ( $this->name == 'update' ||
         $this->name == 'connect' )
    {
      // disconnect users from the server pool
      $container =& $c->getContainerInstance();
      $disconnected_users = $container->removeObsoleteNick(NULL,$c->timeout); 
    }

    if ( $this->name == 'getonlinenick' )
    {
      // disconnect users from specific channels
      $container =& $c->getContainerInstance();
      $disconnected_users = $container->removeObsoleteNick($recipient,$c->timeout);
      if (isset($disconnected_users["nick"]))
        foreach ($disconnected_users["nick"] as $n)
        {
          $cmdp = $p;
          $cmdp["param"] = _pfc("%s quit (timeout)", $n);
          $cmdp["flag"] = 2;
          $cmd =& pfcCommand::Factory("notice");
          $cmd->run($xml_reponse, $cmdp);
        }
    }

    // forward the command to the next proxy or to the final command
    $this->next->run($xml_reponse, $p);
  }
}

?>
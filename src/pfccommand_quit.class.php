<?php
/**
 * phpfreechat.class.php
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

require_once(dirname(__FILE__)."/pfccommand.class.php");

class pfcCommand_quit extends pfcCommand
{
  function run(&$xml_reponse, $clientid, $param ="")
  {
    $c =& $this->c;
   
    // set the chat inactive
    $c->active = false;
    $c->saveInSession();

    // then remove the nickname file
    $container =& $c->getContainerInstance();
    if ($container->removeNick($c->nick))
    {
      $cmd =& pfcCommand::Factory("notice", $c);
      $cmd->run($xml_reponse, $clientid, _pfc("%s quit", $c->nick), 2);
    }

    // stop updates
    $xml_reponse->addScript("pfc.updateChat(false);");
    $xml_reponse->addScript("pfc.isconnected = false; pfc.refresh_loginlogout();");

    if ($c->debug) pxlog("Cmd_quit[".$c->sessionid."]: a user just quit -> nick=".$c->nick, "chat", $c->getId());
  }
}

?>
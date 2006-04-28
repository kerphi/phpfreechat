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

class pfcCommand_updatemynick extends pfcCommand
{
  function run(&$xml_reponse, $clientid, $param ="")
  {
    $c =& $this->c;
    $container =& $c->getContainerInstance();
    $was_there = $container->updateNick($c->nick);
    if (!$was_there)
    {
      /*
      @todo: write the timeout adjustment when the user object will be available
      if ($c->debug) pxlog("Cmd_updateMyNick[".$c->sessionid."]: nick ".$c->nick." updated but was not there, adjust timeout to ".$c->timeout, "chat", $c->getId());
      // adjust the timeout value dynamicaly for this user
      $c->timeout += $c->refresh_delay;
      $c->saveInSession();
      */
    }
  }
}

?>
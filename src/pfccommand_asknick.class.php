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

class pfcCommand_asknick extends pfcCommand
{
  function run(&$xml_reponse, $clientid, $nicktochange)
  {
    $c =& $this->c;
    $nicktochange = phpFreeChat::FilterNickname($nicktochange);
    
    if ($c->frozen_nick)
    {
      // assign a random nick
      $cmd =& pfcCommand::Factory("nick", $c);
      $cmd->run($xml_reponse, $clientid, $nicktochange."".rand(1,1000));
    }
    else
    {
      if ($nicktochange == "")
      {
        $nicktochange = $c->nick;
        $msg = _pfc("Please enter your nickname");
      }
      else
        $msg = "'".$nicktochange."' is used, please choose another nickname.";
      $xml_reponse->addScript("var newnick = prompt('".addslashes($msg)."', '".addslashes($nicktochange)."'); if (newnick) pfc.handleRequest('/nick', newnick);");
    }
  }
}

?>
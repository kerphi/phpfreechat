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
  function run(&$xml_reponse, $clientid, $param, $sender, $recipient, $recipientid)
  {
    $c =& $this->c;
    $u =& $this->u;

    //    $xml_reponse->addScript("alert('proxy auth');");

    //    if ($this->name == "send")
    //      $xml_reponse->addScript("alert('proxy auth');");

    if ($this->name == "op")
    {
      if (!in_array($u->nick, $c->admins))
      {
        $xml_reponse->addScript("alert('not allowed to do /op');");
      }
    }

    
    // on passe la main a au prochain proxy (ou a la command finale)
    $this->next->run(&$xml_reponse, $clientid, $param, $sender, $recipient, $recipientid);
  }
}

?>
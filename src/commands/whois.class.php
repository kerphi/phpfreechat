<?php
/**
 * whois.class.php
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

require_once(dirname(__FILE__)."/../../lib/json/JSON.php");
require_once(dirname(__FILE__)."/../pfccommand.class.php");

class pfcCommand_whois extends pfcCommand
{
  var $usage = "/whois nickname";
  
  function run(&$xml_reponse, $p)
  {
    $clientid    = $p["clientid"];
    $param       = $p["param"];
    $sender      = $p["sender"];
    $recipient   = $p["recipient"];
    $recipientid = $p["recipientid"];
    
    $c =& $this->c;
    $u =& $this->u;
    $ct =& $c->getContainerInstance();

    $nickid = $ct->getNickId($param);
    if ($nickid)
    {
      $usermeta = $ct->getAllUserMeta($nickid);
      $usermeta['nickid'] = $nickid;
      unset($usermeta['cmdtoplay']); // used internaly
      
      $json = new Services_JSON();
      $js = $json->encode($usermeta);
      $xml_reponse->addScript("pfc.handleResponse('".$this->name."', 'ok', ".$js.");");
    }
    else
      $xml_reponse->addScript("pfc.handleResponse('".$this->name."', 'ko','');");
  }
}

?>
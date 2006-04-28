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

class pfcCommand_getonlinenick extends pfcCommand
{
  function run(&$xml_reponse, $clientid, $param ="")
  {
    $c =& $this->c;

    // get the actual nicklist
    $nicklist_sid = $c->prefix."nicklist_".$c->getId()."_".$clientid;
    $oldnicklist = isset($_SESSION[$nicklist_sid]) ? $_SESSION[$nicklist_sid] : array();
    
    $container =& $c->getContainerInstance();
    $disconnected_users = $container->removeObsoleteNick();
    foreach ($disconnected_users as $u)
    {
      $cmd =& pfcCommand::Factory("notice", $c);
      $cmd->run($xml_reponse, $clientid, _pfc("%s disconnected (timeout)",$u), 2);
    }
    $users = $container->getOnlineNick();
    sort($users);
    // check if the nickname list must be updated
    if ($oldnicklist != $users)
    {
      if ($c->debug) pxlog("Cmd_getOnlineNick[".$c->sessionid."]: nicklist updated - nicklist=".var_export($users, true), "chat", $c->getId());

      $_SESSION[$nicklist_sid] = $users;

      $js = "";
      foreach ($users as $u)
      {
        $nickname = addslashes($u); // must escape ' charactere for javascript string
        $js      .= "'".$nickname."',";
      }
      $js    = substr($js, 0, strlen($js)-1); // remove last ','
      
      $xml_reponse->addScript("pfc.updateNickList(Array(".$js."));");
    }
  }
}

?>
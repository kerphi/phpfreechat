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

class pfcCommand_nick extends pfcCommand
{
  function run(&$xml_reponse, $clientid, $newnick)
  {
    $c =& $this->c;
    $newnick = phpFreeChat::FilterNickname($newnick);

    if ($c->debug) pxlog("Cmd_nick[".$c->sessionid."]: newnick=".preg_quote($c->nick,'/'), "chat", $c->getId());

    if ($newnick == "")
    {
      // the choosen nick is empty
      if ($c->debug) pxlog("Cmd_nick[".$c->sessionid."]: the choosen nick is empty", "chat", $c->getId());
      $cmd =& pfcCommand::Factory("asknick", $c);
      $cmd->run($xml_reponse, $clientid, "");
      return;
    }
   
    $container =& $c->getContainerInstance();
    $newnickid = $container->getNickId($newnick);
    $oldnickid = $container->getNickId($c->nick);

    // now check unsensitive case
    // 'BoB' and 'bob' must be considered same nicknames
    $nick_in_use = false;
    $online_users = $container->getOnlineNick();
    foreach($online_users as $ou)
    {
      if (preg_match("/^".preg_quote($ou)."$/i",$newnick))
      {
        // the nick match
	// just allow the owner to change his capitalised letters
        if ($container->getNickId($ou) != $oldnickid)
          $nick_in_use = true;
      }
    }

    if ( $newnickid == "undefined" && !$nick_in_use )
    {
      // this is a real nickname change
      $container->changeNick($newnick);
      $oldnick = $c->nick;
      $c->nick = $newnick;
      $c->saveInSession();
      $xml_reponse->addAssign($c->prefix."handle", "value", $newnick);
      if ($oldnick != $newnick && $oldnick != "")
      {
	$cmd =& pfcCommand::Factory("notice", $c);
	$cmd->run($xml_reponse, $clientid, _pfc("%s changes his nickname to %s",$oldnick,$newnick), 1);
      }
      if ($c->debug) pxlog("Cmd_nick[".$c->sessionid."]: first time nick is assigned -> newnick=".$c->nick." oldnick=".$oldnick, "chat", $c->getId());
      
      // new nickname is undefined (not used) and
      // current nickname (oldnickname) is not mine or is undefined
      if ($oldnickid != $c->sessionid)
      {
        // set the chat active (allow periodic updates)
        $c->active = true;
        $c->saveInSession();

	$cmd =& pfcCommand::Factory("notice", $c);
	$cmd->run($xml_reponse, $clientid, _pfc("%s is connected",$c->nick), 2);
      }
    }
    else if ($newnickid == $c->sessionid)
    {
      // user didn't change his nickname
      $xml_reponse->addAssign($c->prefix."handle", "value", $newnick);
      if ($c->debug) pxlog("Cmd_nick[".$c->sessionid."]: user just reloded the page so let him keep his nickname without any warnings -> nickid=".$newnickid." nick=".$newnick, "chat", $c->getId());
    }
    else
    {
      // the wanted nick is allready used
      if ($c->debug) pxlog("Cmd_nick[".$c->sessionid."]: wanted nick is allready in use -> wantednickid=".$newnickid." wantednick=".$newnick, "chat", $c->getId());
      $cmd =& pfcCommand::Factory("asknick", $c);
      $cmd->run($xml_reponse, $clientid, $newnick);
    }

    // refresh users info on client side
    $xml_reponse->addScript("pfc.nickname = '".$c->nick."';");
  }
}

?>

<?php

require_once(dirname(__FILE__)."/../pfccommand.class.php");

class pfcCommand_nick extends pfcCommand
{
  function run(&$xml_reponse, $clientid, $param, $sender, $recipient, $recipientid)
  {
    $c =& $this->c;
    $u =& $this->u;

    $newnick = phpFreeChat::FilterNickname($param);
    $oldnick = $u->nick;

    if ($c->debug) pxlog("/nick ".$newnick, "chat", $c->getId());

    if ($newnick == "")
    {
      // the choosen nick is empty
      if ($c->debug) pxlog("/nick the choosen nick is empty", "chat", $c->getId());
      $xml_reponse->addScript("pfc.handleResponse('nick', 'empty', '');");
      //$cmd =& pfcCommand::Factory("asknick");
      //$cmd->run($xml_reponse, $clientid, "", $sender, $recipient);
      return;
    }
   
    $container =& $c->getContainerInstance();
    $newnickid = $container->getNickId($newnick);
    $oldnickid = $container->getNickId($u->nick);

    // now check unsensitive case
    // 'BoB' and 'bob' must be considered same nicknames
    $nick_in_use = false;
    $online_users = $container->getOnlineNick(NULL);
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
      // new nickname is undefined (not used) and
      // current nickname (oldnick) is mine and
      // oldnick is different from new nick
      // -> this is a nickname change
      if ($oldnickid == $u->sessionid &&
          $oldnick != $newnick && $oldnick != "")
      {
        // really change the nick (rename it)
        $container->changeNick(NULL, $newnick, $oldnick);
        foreach($u->channels as $chan)
          $container->changeNick($chan["recipient"], $newnick, $oldnick);
        foreach( $u->privmsg as $pv )
          $container->changeNick($pv["recipient"], $newnick, $oldnick);
        $u->nick = $newnick;
        $u->saveInCache();

        $cmd =& pfcCommand::Factory("notice");
        foreach($u->channels as $id => $chan)
          $cmd->run($xml_reponse, $clientid, _pfc("%s changes his nickname to %s",$oldnick,$newnick), $sender, $chan["recipient"], $id, 1);
        foreach( $u->privmsg as $id => $pv )
          $cmd->run($xml_reponse, $clientid, _pfc("%s changes his nickname to %s",$oldnick,$newnick), $sender, $pv["recipient"], $id, 1);

        $xml_reponse->addScript("pfc.handleResponse('nick', 'changed', '".$newnick."');");
      }
      
      // new nickname is undefined (not used) and
      // current nickname (oldnick) is not mine or is undefined
      // -> this is a first connection
      if ($oldnickid != $u->sessionid)
      {
        // this is a first connection (create the nickname)
        $container->createNick(NULL, $newnick, $u->sessionid);
        foreach($u->channels as $chan)
          $container->createNick($chan["recipient"], $newnick, $u->sessionid);
        foreach($u->privmsg as $pv)
          $container->createNick($pv["recipient"], $newnick, $u->sessionid);
        $u->nick   = $newnick;
        $u->active = true;
        $u->saveInCache();

        $xml_reponse->addScript("alert('join: u->nick=".$u->nick);
        
	$cmd =& pfcCommand::Factory("notice");
        foreach($u->channels as $id => $chan)
          $cmd->run($xml_reponse, $clientid, _pfc("%s is connected", $u->nick), $sender, $chan["recipient"], $id, 2);
        foreach($u->privmsg as $id => $pv)
          $cmd->run($xml_reponse, $clientid, _pfc("%s is connected", $u->nick), $sender, $pv["recipient"], $id, 2);

        $xml_reponse->addScript("pfc.handleResponse('nick', 'connected', '".$newnick."');");
      
        if ($c->debug)
          pxlog("/nick ".$newnick." (first connection, oldnick=".$oldnick.")", "chat", $c->getId());
      }

      // force the handle field to be uptodate
      //      $xml_reponse->addAssign($c->prefix."handle", "value", $newnick);
      //$xml_reponse->addScript("$('".$c->prefix."words').focus();");
      
    }
    else if ($newnickid == $u->sessionid)
    {
      // user didn't change his nickname
      //$xml_reponse->addAssign($c->prefix."handle", "value", $newnick);
      $xml_reponse->addScript("pfc.handleResponse('nick', 'notchanged', '".$newnick."');");

      if ($c->debug)
        pxlog("/nick ".$newnick." (user just reloded the page so let him keep his nickname without any warnings -> nickid=".$newnickid.")", "chat", $c->getId());
    }
    else
    {
      // the wanted nick is allready used, just ask again the user
      //$cmd =& pfcCommand::Factory("asknick");
      //$cmd->run($xml_reponse, $clientid, $newnick, $sender, $recipient);

      $xml_reponse->addScript("pfc.handleResponse('nick', 'isused', '".$newnick."');");
      
      if ($c->debug)
        pxlog("/nick ".$newnick." (wanted nick is allready in use -> wantednickid=".$newnickid.")", "chat", $c->getId());
    }

    // refresh users info on client side
    //$xml_reponse->addScript("pfc.nickname = '".$u->nick."';");
  }
}

?>

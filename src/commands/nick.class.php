<?php

require_once(dirname(__FILE__)."/../pfccommand.class.php");

class pfcCommand_nick extends pfcCommand
{
  var $usage = "/nick {newnickname}";
  
  function run(&$xml_reponse, $p)
  {
    $clientid    = $p["clientid"];
    $param       = $p["param"];
    $sender      = $p["sender"];
    $recipient   = $p["recipient"];
    $recipientid = $p["recipientid"];

    $c =& $this->c;
    $u =& $this->u;

    if (trim($param) == "")
    {
      // error
      $cmdp = $p;
      $cmdp["param"] = _pfc("Missing parameter");
      $cmdp["param"] .= " (".$this->usage.")";
      $cmd =& pfcCommand::Factory("error");
      $cmd->run($xml_reponse, $cmdp);
      return;
    }
    
    $newnick = phpFreeChat::FilterNickname($param);
    $oldnick = $u->nick;

    if ($c->debug) pxlog("/nick ".$newnick, "chat", $c->getId());
   
    $container =& $c->getContainerInstance();
    $newnickid = $container->getNickId($newnick);
    $oldnickid = $container->getNickId($u->nick);

    // now check unsensitive case
    // 'BoB' and 'bob' must be considered same nicknames
    $nick_in_use = false;
    $online_users = $container->getOnlineNick(NULL);
    if (isset($online_users["nickid"]))
      foreach($online_users["nickid"] as $nid)
      {
        if (preg_match("/^".preg_quote($container->getNickname($nid))."$/i",$newnick))
        {
          // the nick match
          // just allow the owner to change his capitalised letters
          if ($nid != $oldnickid)
            $nick_in_use = true;
        }
      }

    if ( $newnickid == "undefined" && !$nick_in_use )
    {
      // new nickname is undefined (not used) and
      // current nickname (oldnick) is mine and
      // oldnick is different from new nick
      // -> this is a nickname change
      if ($oldnickid == $u->nickid &&
          $oldnick != $newnick && $oldnick != "")
      {
        // really change the nick (rename it)
        $container->changeNick($newnick, $oldnick);
        $u->nick = $newnick;
        $u->saveInCache();

        // notify all the joined channels/privmsg
        $cmdp = $p;
        $cmdp["param"] = _pfc("%s changes his nickname to %s",$oldnick,$newnick);
        $cmdp["flag"]  = 1;
        $cmd =& pfcCommand::Factory("notice");
        foreach($u->channels as $id => $chan)
        {
          $cmdp["recipient"]   = $chan["recipient"];
          $cmdp["recipientid"] = $id;
          $cmd->run($xml_reponse, $cmdp);
        }
        foreach( $u->privmsg as $id => $pv )
        {
          $cmdp["recipient"]   = $pv["recipient"];
          $cmdp["recipientid"] = $id;
          $cmd->run($xml_reponse, $cmdp);
        }
        $xml_reponse->addScript("pfc.handleResponse('nick', 'changed', '".$newnick."');");
      }
      
      // new nickname is undefined (not used) and
      // current nickname (oldnick) is not mine or is undefined
      // -> this is a first connection
      if ($oldnickid != $u->nickid)
      {
        // this is a first connection (create the nickname)
        $container->createNick(NULL, $newnick, $u->nickid);
        foreach($u->channels as $chan)
          $container->createNick($chan["recipient"], $newnick, $u->nickid);
        foreach($u->privmsg as $pv)
          $container->createNick($pv["recipient"], $newnick, $u->nickid);
        $u->nick   = $newnick;
        $u->active = true;
        $u->saveInCache();

        $xml_reponse->addScript("alert('TODO?! remove this unused code ?');");
        $xml_reponse->addScript("pfc.handleResponse('nick', 'connected', '".$newnick."');");
      
        if ($c->debug)
          pxlog("/nick ".$newnick." (first connection, oldnick=".$oldnick.")", "chat", $c->getId());
      }

      // force the handle field to be uptodate
      //      $xml_reponse->addAssign($c->prefix."handle", "value", $newnick);
      //$xml_reponse->addScript("$('".$c->prefix."words').focus();");
      
    }
    else if ($newnickid == $u->nickid)
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
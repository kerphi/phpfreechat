<?php

require_once(dirname(__FILE__)."/pfccommand.class.php");

class pfcCommand_update extends pfcCommand
{
  function run(&$xml_reponse, $clientid, $param, $sender, $recipient, $recipientid)
  {
    $c =& $this->c;
    $u =& $this->u;
    
    // do not update if user isn't active (didn't connect)
    if ($u->active)
    {
      // -----
      // check if other user talk to me or not
      $container =& $c->getContainerInstance();
      $pvnicks = $container->getMeta("privmsg", "nickname", $u->nick);
      if (is_string($pvnicks)) $pvnicks = unserialize($pvnicks);
      if (!is_array($pvnicks)) $pvnicks = array();
      for( $i=0; $i < count($pvnicks); $i++)
        $xml_reponse->addScript("pfc.handleResponse('update', 'privmsg', '".addslashes($pvnicks[$i])."');");
      $container->rmMeta("privmsg", "nickname", $u->nick);
      // -----

      // update the user nickname timestamp
      $cmd =& pfcCommand::Factory("updatemynick");
      foreach( $u->channels as $id => $chan )
        $cmd->run($xml_reponse, $clientid, $param, $sender, $chan["recipient"], $id);
      foreach( $u->privmsg as $id => $pv )
        $cmd->run($xml_reponse, $clientid, $param, $sender, $pv["recipient"], $id);
      $cmd->run($xml_reponse, $clientid, $param, $sender, NULL, NULL);

      // get other online users on each channels
      $cmd =& pfcCommand::Factory("getonlinenick");
      foreach( $u->channels as $id => $chan )
        $cmd->run($xml_reponse, $clientid, $param, $sender, $chan["recipient"], $id);
      foreach( $u->privmsg as $id => $pv )
        $cmd->run($xml_reponse, $clientid, $param, $sender, $pv["recipient"], $id);

      // get new message posted on each channels
      $cmd =& pfcCommand::Factory("getnewmsg");
      foreach( $u->channels as $id => $chan )
        $cmd->run($xml_reponse, $clientid, $param, $sender, $chan["recipient"], $id);
      foreach( $u->privmsg as $id => $pv )
        $cmd->run($xml_reponse, $clientid, $param, $sender, $pv["recipient"], $id);

      $xml_reponse->addScript("pfc.handleResponse('update', 'ok', '');");
    }
    else
      $xml_reponse->addScript("pfc.handleResponse('update', 'ko', '');");

  }
}

?>
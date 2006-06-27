<?php

require_once(dirname(__FILE__)."/../pfccommand.class.php");

class pfcCommand_send extends pfcCommand
{
  function run(&$xml_reponse, $clientid, $param, $sender, $recipient, $recipientid)
  {
    $c    =& $this->c;
    $u    =& $this->u;
    $nick = phpFreeChat::FilterSpecialChar($sender);
    $text = phpFreeChat::PreFilterMsg($param);


    // send an error because the current user is not connected
    if (!$u->active)
    {
      $cmd =& pfcCommand::Factory("error");
      $cmd->run($xml_reponse, $clientid, _pfc("Your must be connected to send a message"), $sender, $recipient, $recipientid);
      return;
    }
    
    //        $offline = $container->getMeta("offline", "nickname", $u->privmsg[$recipientid]["name"]);

    
    // if this channel is a pv (one to one channel),
    // first of all, check if the other user is connected
    // if he is not connected anymore, display an error
    $can_send = true;
    if (isset($u->privmsg[$recipientid]))
    {
      $container =& $c->getContainerInstance();
      $pvnick   = $u->privmsg[$recipientid]["name"];
      $pvnickid = $container->getNickId($pvnick);
      
      // now check if this user is currently online
      $onlineusers = $container->getOnlineNick(NULL);
      if (!in_array($pvnickid, $onlineusers["nickid"]))
      {
        // send an error because the user is not online
        $cmd =& pfcCommand::Factory("error");
        $cmd->run($xml_reponse, $clientid, _pfc("Can't send the message, %s is offline", $pvnick), $sender, $recipient, $recipientid);
        $can_send = false;
      }
    }

    
    // check the sent text is not empty and the user has a none empty nickname
    $errors = array();
    if ($text == "") $errors[$c->prefix."words"]  = _pfc("Text cannot be empty");
    if ($nick == "") $errors[$c->prefix."handle"] = _pfc("Please enter your nickname");
    if (count($errors) > 0)
    {
      // an error occured, just ignore the message and display errors
      foreach($errors as $e)
        if ($c->debug) pxlog("error /send, user can't send a message -> nick=".$u->nick." err=".$e, "chat", $c->getId());
      $cmd =& pfcCommand::Factory("error");
      $cmd->run($xml_reponse, $clientid, $errors, $sender, $recipient, $recipientid);
      if (isset($errors[$c->prefix."handle"])) // the nick is empty so give it focus
        $xml_reponse->addScript("$('".$c->prefix."handle').focus();");
      $can_send = false;
    }

    
    // Now send the message if there is no errors
    if ($can_send)
    {
      $container =& $c->getContainerInstance();
      $msgid = $container->write($recipient, $nick, "send", $text);
      if ($c->debug) pxlog("/send ".$text." (a user just sent a message -> nick=".$u->nick.")", "chat", $c->getId());
      
      // a message has been posted so :
      // - clear errors
      // - give focus to "words" field
      $xml_reponse->addScript("pfc.clearError(Array('".$c->prefix."words"."','".$c->prefix."handle"."'));");
      $xml_reponse->addScript("$('".$c->prefix."words').focus();");
    }
  }
}

?>
<?php

require_once(dirname(__FILE__)."/pfccommand.class.php");

class pfcCommand_send extends pfcCommand
{
  function run(&$xml_reponse, $clientid, $param, $sender, $recipient, $recipientid)
  {
    $c =& $this->c;
    $u =& $this->u;

    //$xml_reponse->addScript("alert('send: sender=".addslashes($sender)." param=".addslashes($param)." recipient=".addslashes($recipient)." recipientid=".addslashes($recipientid)."');");
    
    // check the nick is not allready known
    $nick = phpFreeChat::FilterSpecialChar($sender);
    $text = phpFreeChat::PreFilterMsg($param);
        
    $errors = array();
    if ($text == "") $errors[$c->prefix."words"]  = _pfc("Text cannot be empty");
    if ($nick == "") $errors[$c->prefix."handle"] = _pfc("Please enter your nickname");
    if (count($errors) == 0)
    {
      $container =& $c->getContainerInstance();
      $msgid = $container->write($recipient, $nick, "send", $text);
      if ($c->debug) pxlog("/send ".$text." (a user just sent a message -> nick=".$u->nick.")", "chat", $c->getId());

      //$xml_reponse->addScript("alert('send: msgid=".$msgid."');");
      
      // a message has been posted so :
      // - clear errors
      // - give focus to "words" field
      $xml_reponse->addScript("pfc.clearError(Array('".$c->prefix."words"."','".$c->prefix."handle"."'));");
      $xml_reponse->addScript("$('".$c->prefix."words').focus();");
    }
    else
    {
      // an error occured, just ignore the message and display errors
      foreach($errors as $e)
        if ($c->debug) pxlog("error /send, user can't send a message -> nick=".$u->nick." err=".$e, "chat", $c->getId());
      $cmd =& pfcCommand::Factory("error");
      $cmd->run($xml_reponse, $clientid, $errors);
      if (isset($errors[$c->prefix."handle"])) // the nick is empty so give it focus
        $xml_reponse->addScript("$('".$c->prefix."handle').focus();");
    }
  }
}

?>
<?php

require_once(dirname(__FILE__)."/../pfccommand.class.php");

class pfcCommand_update extends pfcCommand
{
  function run(&$xml_reponse, $p)
  {
    $clientid    = $p["clientid"];
    $param       = $p["param"];
    $sender      = $p["sender"];
    $recipient   = $p["recipient"];
    $recipientid = $p["recipientid"];
    
    $c =& $this->c;
    $u =& $this->u;
    
    // do not update if user isn't active (didn't connect)
    if ($u->active)
    {
      $cmdp = $p;
      // update the user nickname timestamp on the server
      $cmd =& pfcCommand::Factory("updatemynick");
      $cmdp["recipient"]   = NULL;
      $cmdp["recipientid"] = NULL;
      $cmd->run($xml_reponse, $cmdp);

      // get other online users on each channels
      $cmd =& pfcCommand::Factory("getonlinenick");
      foreach( $u->channels as $id => $chan )
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

      // get new message posted on each channels
      $cmd =& pfcCommand::Factory("getnewmsg");
      foreach( $u->channels as $id => $chan )
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

      $xml_reponse->addScript("pfc.handleResponse('update', 'ok', '');");
    }
    else
      $xml_reponse->addScript("pfc.handleResponse('update', 'ko', '');");

  }
}

?>
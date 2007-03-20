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
    
    $c =& pfcGlobalConfig::Instance();
    $u =& pfcUserConfig::Instance();

    // check the user has not been disconnected from the server by timeout
    // if he has been disconnected, then I reconnect him with /connect command
    if ($u->nick != '' && !$u->isOnline())
    {
      $cmd =& pfcCommand::Factory("connect");
      $cmdp = $p;
      $cmdp['params'] = array($u->nick);
      $cmdp['getoldmsg']   = false;
      $cmdp['joinoldchan'] = false;
      $cmd->run($xml_reponse, $cmdp);
    }
    
    // do not update if user isn't active (didn't connect)
    if ($u->isOnline())
    {
      $cmdp = $p;
      
      // update the user nickname timestamp on the server
      $cmd =& pfcCommand::Factory("updatemynick");
      $cmdp["recipient"]   = NULL;
      $cmdp["recipientid"] = NULL;
      $cmd->run($xml_reponse, $cmdp);

      // get other online users on each channels     
      $cmd =& pfcCommand::Factory("who2");
      foreach( $u->channels as $id => $chan )
      {
        $cmdp["recipient"]   = $chan["recipient"];
        $cmdp["recipientid"] = $id;        
        $cmdp["param"] = ''; // don't forward the parameter because it will be interpreted as a channel name
        $cmd->run($xml_reponse, $cmdp);
      }
      foreach( $u->privmsg as $id => $pv )
      {
        $cmdp["recipient"]   = $pv["recipient"];
        $cmdp["recipientid"] = $id;
        $cmdp["param"] = ''; // don't forward the parameter because it will be interpreted as a channel name
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

      $xml_reponse->script("pfc.handleResponse('update', 'ok', '');");
    }
    else
    {
      $xml_reponse->script("pfc.handleResponse('update', 'ko', '');");
    }

  }
}

?>
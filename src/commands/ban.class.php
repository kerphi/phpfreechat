<?php

require_once(dirname(__FILE__)."/../pfccommand.class.php");

class pfcCommand_ban extends pfcCommand
{
  var $usage = "/ban {nickname}";
  
  function run(&$xml_reponse, $clientid, $param, $sender, $recipient, $recipientid)
  {
    $c =& $this->c;
    $u =& $this->u;

    if (trim($param) == "")
    {
      // error
      $msg = _pfc("Missing parameter");
      $msg .= " (".$this->usage.")";
      $cmd =& pfcCommand::Factory("error");
      $cmd->run($xml_reponse, $clientid, $msg, $sender, $recipient, $recipientid);
      return;
    }


    $container =& $c->getContainerInstance();
    $nickid = $container->getNickId($param);
    if ($nickid != "undefined")
    {
      $cmdtoplay = $container->getMeta("cmdtoplay", "nickname", $nickid);
      $cmdtoplay = ($cmdtoplay == NULL) ? array() : unserialize($cmdtoplay);

      $cmdtmp = array("leave",     /* cmdname */
                      $recipientid,/* param */
                      $sender,     /* sender */
                      $recipient,  /* recipient */
                      $recipientid,/* recipientid */
                      );
      //_pfc("banished from %s by %s", $recipient, $sender);
      $cmdtoplay[] = $cmdtmp; // ban the user from the current channel
      $container->setMeta(serialize($cmdtoplay), "cmdtoplay", "nickname", $nickid);      
    }

    // update the recipient banlist
    $banlist = $container->getMeta("banlist_nickid", "channel", $recipientid);
    if ($banlist == NULL)
      $banlist = array();
    else
      $banlist = unserialize($banlist);
    $banlist[] = $nickid; // append the nickid to the banlist
    $container->setMeta(serialize($banlist), "banlist_nickid", "channel", $recipientid);
  }
}

?>
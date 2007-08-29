<?php

require_once(dirname(__FILE__)."/../pfccommand.class.php");

class pfcCommand_notice extends pfcCommand
{
  function run(&$xml_reponse, $p)
  {
    $clientid    = $p["clientid"];
    $msg         = $p["param"];
    $sender      = $p["sender"];
    $recipient   = $p["recipient"];
    $recipientid = $p["recipientid"];
    $flag        = isset($p["flag"]) ? $p["flag"] : 3;
    
    $c  =& pfcGlobalConfig::Instance();
    $u  =& pfcUserConfig::Instance();
    $ct =& pfcContainer::Instance();

    if ($c->shownotice > 0 &&
        ($c->shownotice & $flag) == $flag)
    {
      $msg = phpFreeChat::FilterSpecialChar($msg);
      $nick = $ct->getNickname($u->nickid);
      $res = $ct->write($recipient, $nick, "notice", $msg);
      if (is_array($res))
      {
        $cmdp = $p;
        $cmdp["param"] = implode(",",$res);
        $cmd =& pfcCommand::Factory("error");
        $cmd->run($xml_reponse, $cmdp);
        return;
      }
    }
  }
}

?>

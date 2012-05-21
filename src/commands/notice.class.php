<?php
/*
 * Fixes a long problematic security breach, well somebody posting as Admin nick is a breach of security
 * even if he doesn't have admin powers..
 * fix by UTAN aka Neumann Valle, you can contact me at vcomputadoras@yahoo.com
 * fix for notice, where any user could use it to pose as admin nick or other user, or non
 * existence user,05/01/2012.
 */
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
    $flag        = isset($p["flag"]) ? $p["flag"] : 7;

    $c  =& pfcGlobalConfig::Instance();
    $u  =& pfcUserConfig::Instance();
    $ct =& pfcContainer::Instance();

    if ($c->shownotice > 0 &&
        ($c->shownotice & $flag) == $flag)
    {
      $msg = phpFreeChat::FilterSpecialChar($msg);

      $msg = $flag == 7 ? '('.$sender.') '.$msg : $msg;

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

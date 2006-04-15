<?php

require_once(dirname(__FILE__)."/pfccommand.class.php");

class pfcCommand_asknick extends pfcCommand
{
  function run(&$xml_reponse, $clientid, $param, $sender, $recipient)
  {
    $c =& $this->c;
    $u =& $this->u;
    
    $nicktochange = phpFreeChat::FilterNickname($param);
    
    if ($c->frozen_nick)
    {
      // assign a random nick
      $cmd =& pfcCommand::Factory("nick");
      $cmd->run($xml_reponse, $clientid, $nicktochange."".rand(1,1000));
    }
    else
    {
      if ($nicktochange == "")
      {
        $nicktochange = $u->nick;
        $msg = _pfc("Please enter your nickname");
      }
      else
        $msg = "'".$nicktochange."' is used, please choose another nickname.";
      $xml_reponse->addScript("var newnick = prompt('".addslashes($msg)."', '".addslashes($nicktochange)."'); if (newnick) pfc.sendRequest('/nick', newnick);");
    }
  }
}

?>
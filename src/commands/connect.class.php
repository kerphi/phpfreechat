<?php

require_once(dirname(__FILE__)."/../pfccommand.class.php");

class pfcCommand_connect extends pfcCommand
{
  function run(&$xml_reponse, $clientid, $param, $sender, $recipient, $recipientid)
  {
    $c =& $this->c;
    $u =& $this->u;

    // disconnect last connected users from the server if necessary 
    $container =& $c->getContainerInstance();
    $disconnected_users = $container->removeObsoleteNick(NULL, $c->timeout);

    // reset the message id indicator (see getnewmsg.class.php)
    // i.e. be ready to re-get all last posted messages
    require_once(dirname(__FILE__)."/join.class.php");
    foreach($u->channels as $chan)
    {
      $channame  = $chan["name"];
      $chanrecip = pfcCommand_join::GetRecipient($channame);
      $chanid    = pfcCommand_join::GetRecipientId($channame);
      $from_id_sid = $c->prefix."from_id_".$c->getId()."_".$clientid."_".$chanid;
      $from_id     = $container->getLastId($chanrecip)-$c->max_msg;
      $_SESSION[$from_id_sid] = ($from_id<0) ? 0 : $from_id;
    }

    
    // check if the user is alone on the server, and give it the admin status if yes
    $isadmin = $c->isadmin;
    if (!$isadmin)
    {
      $users = $container->getOnlineNick(NULL);
      if (count($users["nickid"]) == 0) $isadmin = true;
    }
    
    // setup some user meta
    $nickid = $u->nickid;
    // store the user ip
    $container->setMeta($_SERVER["REMOTE_ADDR"], "ip", "nickname", $nickid);
    // store the admin flag
    $container->setMeta($isadmin, "isadmin", "nickname", $nickid);
    // connect to the server
    $xml_reponse->addScript("pfc.handleResponse('connect', 'ok', '');");
        
    return $clientid;
  }
}

?>
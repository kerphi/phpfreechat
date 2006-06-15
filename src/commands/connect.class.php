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

    $isadmin = $c->isadmin;
    if (!$isadmin)
    {
      // check if the user is alone on the server, and give it the admin status if yes
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
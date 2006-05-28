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


    // setup some user meta
    $nickid = $container->getNickId($u->nick);
    // store the user ip
    $container->setMeta($_SERVER["REMOTE_ADDR"], "ip", "nickname", $nickid);
    // store the admin flag
    $container->setMeta($c->isadmin, "isadmin", "nickname", $nickid);
    // connect to the server
    $xml_reponse->addScript("pfc.handleResponse('connect', 'ok', '');");
        
    return $clientid;
  }
}

?>
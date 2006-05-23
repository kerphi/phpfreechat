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

    // connect to the server
    $xml_reponse->addScript("pfc.handleResponse('connect', 'ok', '');");
        
    return $clientid;
  }
}

?>
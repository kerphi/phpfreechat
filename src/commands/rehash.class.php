<?php

require_once(dirname(__FILE__)."/../pfccommand.class.php");

/**
 * This command deletes the cached configuration. Uses it to take into account new parameters.
 *
 * @author Stephane Gully <stephane.gully@gmail.com>
 */
class pfcCommand_rehash extends pfcCommand
{
  var $desc = "This command deletes the cached configuration. Uses it to take into account new parameters.";
  
  function run(&$xml_reponse, $clientid, $param, $sender, $recipient, $recipientid)
  {
    $c =& $this->c;
    $destroyed = $c->destroyCache();    
    $synchro   = $c->synchronizeWithCache();

    if ($destroyed && $synchro)
      $xml_reponse->addScript("pfc.handleResponse('".$this->name."', 'ko', '');");
    else
      $xml_reponse->addScript("pfc.handleResponse('".$this->name."', 'ok', '');");
  }
}

?>
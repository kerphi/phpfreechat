<?php

require_once(dirname(__FILE__)."/../pfccommand.class.php");

class pfcCommand_clear extends pfcCommand
{
  function run(&$xml_reponse, $p)
  {
    $c =& pfcGlobalConfig::Instance();
    $u =& pfcUserConfig::Instance();

    $xml_reponse->script("pfc.handleResponse('".$this->name."', 'ok', '');");
  }
}

?>
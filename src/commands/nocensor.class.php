<?php

require_once(dirname(__FILE__)."/../pfccommand.class.php");

class pfcCommand_nocensor extends pfcCommand
{
  function run(&$xml_reponse, $p)
  {
	 if(!isset($_SESSION["nocensor"]) || !$_SESSION["nocensor"]) {
		$_SESSION["nocensor"] = true;
	 }
	 else {
		$_SESSION["nocensor"] = false;
	 }

    $xml_reponse->script("pfc.handleResponse('nocensor', 'ok', '');");
  }
}

?>

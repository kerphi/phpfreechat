<?php

require_once(dirname(__FILE__)."/pfccommand.class.php");

class pfcCommand_init extends pfcCommand
{
  function run(&$xml_reponse, $clientid, $param ="")
  {
    $c =& $this->c;

    $cmd =& pfcCommand::Factory("quit");
    $cmd->run($xml_reponse, $clientid);

    if (isset($_COOKIE[session_name()]))
    {
      setcookie(session_name(), '', time()-42000, '/');
    } // clobber the cookie
  }
}

?>
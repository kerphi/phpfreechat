<?php
/**
 * phpfreechat.class.php
 *
 * Copyright © 2006 Stephane Gully <stephane.gully@gmail.com>
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful, 
 * but WITHOUT ANY WARRANTY; without even the implied warranty of 
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details. 
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the
 * Free Software Foundation, 51 Franklin St, Fifth Floor,
 * Boston, MA  02110-1301  USA
 */

require_once(dirname(__FILE__)."/pfccommand.class.php");

class pfcCommand_send extends pfcCommand
{
  function run(&$xml_reponse, $clientid, $msg)
  {
    $c =& $this->c;

    // check the nick is not allready known
    $nick = phpFreeChat::FilterSpecialChar($c->nick);
    $text = phpFreeChat::PreFilterMsg($msg);
        
    $errors = array();
    if ($text == "") $errors[$c->prefix."words"] = _pfc("Text cannot be empty");
    if ($nick == "") $errors[$c->prefix."handle"] = _pfc("Please enter your nickname");
    if (count($errors) == 0)
    {
      $container =& $c->getContainerInstance();
      $container->writeMsg($nick, $text);
      if ($c->debug) pxlog("Cmd_send[".$c->sessionid."]: a user just sent a message -> nick=".$c->nick." m=".$text, "chat", $c->getId());
    	
      // a message has been posted so :
      // - read new messages
      // - give focus to "words" field
      $xml_reponse->addScript("pfc.clearError(Array('".$c->prefix."words"."','".$c->prefix."handle"."'));");
      $xml_reponse->addScript("$('".$c->prefix."words').focus();");
    }
    else
    {
      // an error occured, just ignore the message and display errors
      foreach($errors as $e)
        if ($c->debug) pxlog("Cmd_send[".$c->sessionid."]: user can't send a message -> nick=".$c->nick." err=".$e, "chat", $c->getId());
      $cmd =& pfcCommand::Factory("error", $c);
      $cmd->run($xml_reponse, $clientid, $errors);
      if (isset($errors[$c->prefix."handle"])) // the nick is empty so give it focus
        $xml_reponse->addScript("$('".$c->prefix."handle').focus();");
    }
  }
}

?>
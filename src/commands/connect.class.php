<?php

require_once(dirname(__FILE__)."/../pfccommand.class.php");

class pfcCommand_connect extends pfcCommand
{
  function run(&$xml_reponse, $p)
  {
    $clientid    = $p["clientid"];
    $param       = $p["param"];
    $sender      = $p["sender"];
    $recipient   = $p["recipient"];
    $recipientid = $p["recipientid"];
    
    $c =& $this->c;
    $u =& $this->u;
    $container =& $c->getContainerInstance();

    // reset the message id indicator (see getnewmsg.class.php)
    // i.e. be ready to re-get all last posted messages
    require_once(dirname(__FILE__)."/join.class.php");
    $channels = array();
    if (count($u->channels) == 0)
      $channels = $c->channels;
    else
      foreach($u->channels as $chan)
        $channels[] = $chan["name"];
    foreach($channels as $channame)
    {
      $chanrecip = pfcCommand_join::GetRecipient($channame);
      $chanid    = pfcCommand_join::GetRecipientId($channame);
      // reset the fromid flag
      $from_id_sid = $c->prefix."from_id_".$c->getId()."_".$clientid."_".$chanid;
      $from_id     = $container->getLastId($chanrecip)-$c->max_msg;
      $_SESSION[$from_id_sid] = ($from_id<0) ? 0 : $from_id;
      // reset the oldmsg flag
      $oldmsg_sid = $c->prefix."oldmsg_".$c->getId()."_".$clientid."_".$chanid;
      $_SESSION[$oldmsg_sid] = true;
    }
    
    // check if the user is alone on the server, and give it the admin status if yes
    $isadmin = $c->isadmin;
    if (!$isadmin)
    {
      $users = $container->getOnlineNick(NULL);
      if (isset($users["nickid"]) &&
          count($users["nickid"]) == 0) $isadmin = true;
    }
    
    // setup some user meta
    $nickid = $u->nickid;
    // store the user ip
    $container->setMeta($_SERVER["REMOTE_ADDR"], "ip", "nickname", $nickid);
    // store the admin flag
    $container->setMeta($isadmin, "isadmin", "nickname", $nickid);
    // connect to the server
    $xml_reponse->addScript("pfc.handleResponse('connect', 'ok', '');");
  }
}

?>
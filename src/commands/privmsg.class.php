<?php

require_once(dirname(__FILE__)."/../pfccommand.class.php");

class pfcCommand_privmsg extends pfcCommand
{
  var $usage = "/privmsg {nickname}";

  function run(&$xml_reponse, $p)
  {
    $clientid    = $p["clientid"];
    $params      = $p["params"];
    $sender      = $p["sender"];
    $recipient   = $p["recipient"];
    $recipientid = $p["recipientid"];
    
    $c =& pfcGlobalConfig::Instance();
    $u =& pfcUserConfig::Instance();
    $ct =& pfcContainer::Instance();
    
    if (count($params) == 0)
    {
      // error
      $cmdp = $p;
      $cmdp["param"] = _pfc("Missing parameter");
      $cmdp["param"] .= " (".$this->usage.")";
      $cmd =& pfcCommand::Factory("error");
      $cmd->run($xml_reponse, $cmdp);
      return false;
    }

    // check the pvname exists on the server
    $pvname = '';
    $pvnickid = '';
    if ($this->name == 'privmsg2')
    {
      $pvnickid = $params[0];
      $pvname   = $ct->getNickname($pvnickid);
    }
    else
    {
      $pvname   = $params[0];
      $pvnickid = $ct->getNickId($pvname);
    }
    $nickid   = $u->nickid;
    $nick     = $ct->getNickname($u->nickid);

    // error: can't speak to myself
    if ($pvnickid == $nickid)
    {
      $xml_reponse->script("pfc.handleResponse('".$this->name."','speak_to_myself');");
      return;
    }

    //$this->trace($xml_reponse, $this->name, $sender);

    // error: can't speak to unknown
    if ($pvnickid == '')
    {
      // remove this old pv from the privmsg list
      $pvid_to_remove = "";
      foreach( $u->privmsg as $pv_k => $pv_v )
      {
        if ($pv_v["name"] == $pvname)
          $pvid_to_remove = $pv_k;
      }
      if ($pvid_to_remove != "")
      {
        unset($u->privmsg[$pvid_to_remove]);
        $u->saveInCache();
      }
      
      $xml_reponse->script("pfc.handleResponse('".$this->name."', 'unknown', Array('".addslashes($pvname)."','speak to unknown'));");
      return;
    }

    // generate a pvid from the two nicknames ids
    $a = array($pvnickid, $nickid); sort($a);
    $pvrecipient = "pv_".$a[0]."_".$a[1];
    $pvrecipientid = md5($pvrecipient);
    
    //    $xml_reponse->script("alert('privmsg: pvnickid=".$pvnickid."');");
    //    $xml_reponse->script("alert('privmsg: pvname=".$pvname." pvrecipient=".$pvrecipient."');");
    
    // update the private message list
    // in the sessions
    if (!isset($u->privmsg[$pvrecipientid]))
    {
      if ($c->max_privmsg <= count($u->privmsg))
      {
        // the maximum number of private messages has been reached
        $xml_reponse->script("pfc.handleResponse('".$this->name."', 'max_privmsg', Array());");
        return;
      }
      
      $u->privmsg[$pvrecipientid]["recipient"] = $pvrecipient;
      $u->privmsg[$pvrecipientid]["name"]      = $pvname;
      $u->privmsg[$pvrecipientid]["pvnickid"]  = $pvnickid;
      $u->saveInCache();

      // reset the message id indicator
      // i.e. be ready to re-get all last posted messages
      $from_id_sid = "pfc_from_id_".$c->getId()."_".$clientid."_".$pvrecipientid;
      $from_id     = $ct->getLastId($pvrecipient)-$c->max_msg-1;
      $_SESSION[$from_id_sid] = ($from_id<0) ? 0 : $from_id;
    }

    // register the user (and his metadata) in this pv
    //    $ct->createNick($pvrecipient, $u->nick, $u->nickid);
    $ct->joinChan($nickid, $pvrecipient);
    $this->forceWhoisReload($nickid);
    
    // return ok to the client
    // then the client will create a new tab
    $xml_reponse->script("pfc.handleResponse('".$this->name."', 'ok', Array('".$pvrecipientid."','".addslashes($pvname)."'));");    
  }
}

?>

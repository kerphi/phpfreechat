<?php

require_once(dirname(__FILE__)."/../pfccommand.class.php");

class pfcCommand_join extends pfcCommand
{
  var $usage = "/join {channelname}";
  
  function run(&$xml_reponse, $p)
  {
    $clientid    = $p["clientid"];
    $param       = $p["param"];
    $sender      = $p["sender"];
    $recipient   = $p["recipient"];
    $recipientid = $p["recipientid"];

    $c =& $this->c;
    $u =& $this->u;

    $channame  = trim($param);
    $chanrecip = pfcCommand_join::GetRecipient($channame);
    $chanid    = pfcCommand_join::GetRecipientId($channame);
    
    if ($channame == "")
    {
      $cmdp = $p;
      $cmdp["param"] = _pfc("Missing parameter");
      $cmdp["param"] .= " (".$this->usage.")";
      $cmd =& pfcCommand::Factory("error");
      $cmd->run($xml_reponse, $cmdp);
      return;
    }
    
    if(!isset($u->channels[$chanid]))
    {
      $u->channels[$chanid]["recipient"] = $chanrecip;
      $u->channels[$chanid]["name"]      = $channame;
      $u->saveInCache();

      // clear the cached nicknames list for the given channel
      $nicklist_sid = "pfc_nicklist_".$c->getId()."_".$clientid."_".$chanid;
      $_SESSION[$nicklist_sid] = NULL;
    }

    // show a join message
    $cmdp = $p;
    $cmdp["param"] = _pfc("%s joins %s",$u->nick, $channame);
    $cmdp["recipient"] = $chanrecip;
    $cmdp["recipientid"] = $chanid;
    $cmdp["flag"] = 2;
    $cmd =& pfcCommand::Factory("notice");
    $cmd->run($xml_reponse, $cmdp);


    //$xml_reponse->addScript("alert('join: chan=".$channame.", from_id=".$from_id."');");
    //    $xml_reponse->addScript("alert('join: u->nick=".$u->nick." chanid=".$chanid." channame=".addslashes($channame)."');");
    //    $xml_reponse->addScript("alert('join: fromidsid=".$from_id_sid."');");

    // return ok to the client
    // then the client will create a new tab
    $xml_reponse->addScript("pfc.handleResponse('".$this->name."', 'ok', Array('".$chanid."','".addslashes($channame)."'));");
  }

  function GetRecipient($channame)
  {
    return "ch_".$channame;
  }

  function GetRecipientId($channame)
  {
    return md5(pfcCommand_join::GetRecipient($channame));
  }
  
}

?>
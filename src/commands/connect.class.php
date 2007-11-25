<?php

require_once dirname(__FILE__).'/../pfccommand.class.php';

class pfcCommand_connect extends pfcCommand
{
  function run(&$xml_reponse, $p)
  {
    $clientid    = $p["clientid"];
    $param       = $p["param"];
    $params      = $p["params"];
    $sender      = $p["sender"];
    $recipient   = $p["recipient"];
    $recipientid = $p["recipientid"];
    $getoldmsg   = isset($p["getoldmsg"]) ? $p["getoldmsg"] : true;
    $joinoldchan = isset($p["joinoldchan"]) ? $p["joinoldchan"] : true;

    // nickname must be given to be able to connect to the chat
    $nick = $params[0];

    $c  =& pfcGlobalConfig::Instance();
    $u  =& pfcUserConfig::Instance();
    $ct =& pfcContainer::Instance();

    // reset the message id indicator (see getnewmsg.class.php)
    // i.e. be ready to re-get all last posted messages
    if ($getoldmsg)
      $this->_resetChannelIdentifier($clientid);

    // check if the user is alone on the server, and give it the admin status if yes
    $isadmin = $ct->getUserMeta($u->nickid, 'isadmin');
    if ($isadmin == NULL)
      $isadmin = $c->isadmin;
    if ($c->firstisadmin && !$isadmin)
    {
      $users = $ct->getOnlineNick(NULL);
      if (isset($users["nickid"]) &&
          (count($users["nickid"]) == 0 || (count($users["nickid"]) == 1 && $users["nickid"][0] == $u->nickid)))
        $isadmin = true;
    }

    // create the nickid and setup some user meta
    $nickid = $u->nickid;
    $ct->joinChan($nickid, NULL); // join the server
    // store the user ip
    $ip = ( $c->get_ip_from_xforwardedfor && isset($_SERVER["HTTP_X_FORWARDED_FOR"])) ?
      $_SERVER["HTTP_X_FORWARDED_FOR"] :
      $_SERVER["REMOTE_ADDR"];
    if ($ip == "::1") $ip = "127.0.0.1"; // fix for konqueror & localhost
    $ct->setUserMeta($nickid, 'ip', $ip);
    // store the admin flag
    $ct->setUserMeta($nickid, 'isadmin', $isadmin);
    // store the customized nick metadata
    foreach($c->nickmeta as $k => $v)
      $ct->setUserMeta($nickid, $k, $v);

    // run the /nick command to assign the user nick
    $cmdp = array();
    $cmdp["param"] = $nick;
    $cmd =& pfcCommand::Factory('nick');
    $ret = $cmd->run($xml_reponse, $cmdp);
    if ($ret)
    {
      $chanlist = (count($u->channels) == 0) ? $c->channels : $u->getChannelNames();
      for($i = 0 ; $i < count($chanlist) ; $i++)
      {
        $cmdp = array();
        $cmdp["param"] = $chanlist[$i];
        $cmd =& pfcCommand::Factory( $i < count($chanlist)-1 || !$joinoldchan ? 'join2' : 'join' );
        $cmd->run($xml_reponse, $cmdp);
      }
      
      $pvlist = (count($u->privmsg) == 0) ? $c->privmsg : $u->getPrivMsgNames();
      for($i = 0 ; $i < count($pvlist) ; $i++)
      {
        $cmdp = array();
        $cmdp["param"] = $pvlist[$i];
        $cmd =& pfcCommand::Factory( $i < count($pvlist)-1 || !$joinoldchan ? 'privmsg2' : 'privmsg' );
        $cmd->run($xml_reponse, $cmdp);
      }
      
      $xml_reponse->script("pfc.handleResponse('".$this->name."', 'ok', Array('".addslashes($nick)."'));");
    }
    else
    {
      $xml_reponse->script("pfc.handleResponse('".$this->name."', 'ko', Array('".addslashes($nick)."'));");      
    }
  }

  /**
   * reset the channel identifiers
   */
  function _resetChannelIdentifier($clientid)
  {
    $c  =& pfcGlobalConfig::Instance();
    $u  =& pfcUserConfig::Instance();
    $ct =& pfcContainer::Instance();

    // reset the channel identifiers
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
      $from_id_sid = "pfc_from_id_".$c->getId()."_".$clientid."_".$chanid;
      $from_id     = $ct->getLastId($chanrecip)-$c->max_msg+1;
      $_SESSION[$from_id_sid] = ($from_id<0) ? 0 : $from_id;
      // reset the oldmsg flag
      $oldmsg_sid = "pfc_oldmsg_".$c->getId()."_".$clientid."_".$chanid;
      $_SESSION[$oldmsg_sid] = true;
    }
    // reset the private messages identifiers
    if (count($u->privmsg) > 0)
    {
      foreach($u->privmsg as $recipientid2 => $pv)
      {
        $recipient2  = $pv['recipient'];
        // reset the fromid flag
        $from_id_sid = "pfc_from_id_".$c->getId()."_".$clientid."_".$recipientid2;
        $from_id     = $ct->getLastId($recipient2)-$c->max_msg+1;
        $_SESSION[$from_id_sid] = ($from_id<0) ? 0 : $from_id;
        // reset the oldmsg flag
        $oldmsg_sid = "pfc_oldmsg_".$c->getId()."_".$clientid."_".$recipientid2;
        $_SESSION[$oldmsg_sid] = true;
      }
    }
  }
  
}

?>
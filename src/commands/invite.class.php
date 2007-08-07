<?php
/**
 * invite.class.php
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

require_once(dirname(__FILE__)."/../pfccommand.class.php");
require_once(dirname(__FILE__)."/../commands/join.class.php");

/**
 * /invite command
 *
 * Invites other users into a channel
 * Currently this is implemented as a "autojoin", so the invited user joins automatically.
 * The parameter "target channel" is optional, if not set it defaults to the current channel
 *
 * @author Benedikt Hallinger <beni@php.net>
 * @author Stephane Gully <stephane.gully@gmail.com>
 */
class pfcCommand_invite extends pfcCommand
{
  var $usage = "/invite {nickname to invite} [ {target channel} ]";
	
  function run(&$xml_reponse, $p)
  {
    $clientid    = $p["clientid"];
    $param       = $p["param"];
    $params      = $p["params"];
    $sender      = $p["sender"];
    $recipient   = $p["recipient"];
    $recipientid = $p["recipientid"];
    
    $c =& pfcGlobalConfig::Instance();   // pfcGlobalConfig
    $u =& pfcUserConfig::Instance();   // pfcUserConfig
    $ct =& pfcContainer::Instance(); // Connection to the chatbackend

    $nicktoinvite  = isset($params[0]) ? $params[0] : '';
    $channeltarget = isset($params[1]) ? $params[1] : $u->channels[$recipientid]["name"]; // Default: current channel

    if ($nicktoinvite == '' || $channeltarget == '')
    {
      // Parameters are not ok
      $cmdp = $p;
      $cmdp["params"] = array();
      $cmdp["param"] = _pfc("Missing parameter");
      $cmdp["param"] .= " (".$this->usage.")";
      $cmd =& pfcCommand::Factory("error");
      $cmd->run($xml_reponse, $cmdp);
      return;
    }

    // check that the inviter is already in the channeltarget
    if (!$ct->isNickOnline(pfcCommand_join::GetRecipient($channeltarget),$u->nickid))
    {
      $cmdp = $p;
      $cmdp["params"] = array();
      $cmdp["param"] = _pfc("You must join %s to invite users in this channel",$channeltarget);
      $cmd =& pfcCommand::Factory("error");
      $cmd->run($xml_reponse, $cmdp);
      return;
    }
		
    // inviting a user: just add a join command to play to the aimed user metadata.
    $nicktoinvite_id = $ct->getNickId($nicktoinvite);
    $cmdstr = 'join2';
    $cmdp = array();
    $cmdp['param'] = $channeltarget; // channel target name
    $cmdp['params'][] = $channeltarget; // channel target name
    pfcCommand::AppendCmdToPlay($nicktoinvite_id, $cmdstr, $cmdp);

    // notify the aimed channel that a user has been invited
    $cmdp = array();
    $cmdp["param"] = _pfc("%s was invited by %s",$nicktoinvite,$sender);
    $cmdp["flag"]  = 1;
    $cmdp["recipient"]   = pfcCommand_join::GetRecipient($channeltarget);
    $cmdp["recipientid"] = pfcCommand_join::GetRecipientId($channeltarget);
    $cmd =& pfcCommand::Factory("notice");
    $cmd->run($xml_reponse, $cmdp);    
  }
}
?>

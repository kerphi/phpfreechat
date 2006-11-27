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
 
/**
 * /invite command
 *
 * Invites other users into a channel
 * Currently this is implemented as a "autojoin", so the invited user joins automatically.
 * The parameter "target channel" is optional, if not set it defaults to the current channel
 *
 * @author Benedikt Hallinger <beni@php.net>
 */
class pfcCommand_invite extends pfcCommand
{
	var $usage = "/invite {nickname to invite} [{target channel}]";
	
	function run(&$xml_reponse, $p)
	{
		$clientid    = $p["clientid"];
		$param       = $p["param"];
		$sender      = $p["sender"];
		$recipient   = $p["recipient"];
		$recipientid = $p["recipientid"];
		
		$c =& $this->c;   // pfcGlobalConfig
		$u =& $this->u;   // pfcUserConfig
		$container =& $c->getContainerInstance(); // Connection to the chatbackend
		
		$p_array = split(' ', $param); // Split the parameters: [0]= targetnick, [1]=targetchannel
		if (!isset($p_array[1])) $p_array[1] = $u->channels[$recipientid]["name"]; // Default: current channel
		if (!isset($p_array[0]) || !isset($p_array[1]))
		{
			// Parameters not ok!
			$cmdp = $p;
			$cmdp["param"] = _pfc("Missing parameter");
			$cmdp["param"] .= " (".$this->usage.")";
			$cmd =& pfcCommand::Factory("error");
			$cmd->run($xml_reponse, $cmdp);
			return;
		}
		
		// inviting a user: just add a join command to play to the aimed user metadata.
		$nickid = $container->getNickId($p_array[0]); // get the internal ID of that chatter
		if ($nickid != "")
		{
			$cmdtoplay = $container->getUserMeta($nickid, 'cmdtoplay'); // get the users command queue
			$cmdtoplay = ($cmdtoplay == NULL) ? array() : unserialize($cmdtoplay);
			$cmdtmp = array("join",  /* cmdname */
					$p_array[1], /* param */
					$sender,     /* sender */
					$recipient,  /* recipient */
					$recipientid,/* recipientid */
					);
			$cmdtoplay[] = $cmdtmp; // store the command in the queue
			$container->setUserMeta($nickid, 'cmdtoplay', serialize($cmdtoplay)); // close and store the queue

			// Ok, the user is invited, now write something into the chat, so his tab opens
			$container->write($recipient, 'SYSTEM', "notice", $p_array[0].' was invited by '.$sender);
		}
	}
}
?>
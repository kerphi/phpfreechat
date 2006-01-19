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

require_once dirname(__FILE__)."/phpfreechatconfig.class.php";
require_once dirname(__FILE__)."/../debug/log.php";

/**
 * phpFreeChat is the entry point for developpers
 *
 * @example ../demo/demo1_simple.php
 * @author Stephane Gully <stephane.gully@gmail.com>
 */
class phpFreeChat
{
  var $chatconfig;
  var $xajax;
  
  function phpFreeChat( $params = array() )
  {
    // start the session : session is used for locking purpose and cache purpose
    if(session_id() == "") session_start();
    if (isset($_GET["init"])) session_destroy();

    $params["sessionid"] = session_id();
    
    $c =& phpFreeChatConfig::Instance( $params );
    
    // Xajax doesn't support yet static class methode call
    // I use basic functions to wrap to my statics methodes
    function handleRequest($request, $shownotice = true)
    {
      $c =& phpFreeChatConfig::Instance();
      $c->shownotice = $shownotice;
      return phpFreeChat::HandleRequest($request);
    }
    // then init xajax engine
    if (!class_exists("xajax")) require_once $c->xajaxpath."/xajax.inc.php";
    $this->xajax = new xajax($c->server_file, $c->prefix);
    //$this->xajax->debugOn();
    $this->xajax->registerFunction("handleRequest");
    $this->xajax->processRequests();
  }

  /**
   * printJavaScript must be called into html header
   * usage:
   * <code>
   *   <?php $chat->printJavascript(); ?>
   * </code>
   */
  function printJavaScript()
  {
    $c =& phpFreeChatConfig::Instance();
    // print xajax javascript
    $xajax_js = phpFreeChatTools::RelativePath(dirname($_SERVER["SCRIPT_FILENAME"]),
					       dirname(__FILE__).'/../data/public/');
    $this->xajax->printJavascript($xajax_js, NULL, $xajax_js."/xajax_js/xajax.js");

    // print phpfreechat specific javascript
    $smarty =& phpFreeChatTools::GetSmarty();
    $c->assignToSmarty($smarty);
    echo "<script type=\"text/javascript\">\n<!--\n";
    $smarty->display("javascript1.js.tpl");
    echo "\n-->\n</script>\n";
   
    // include microsoft IE6 patches
    if ($c->useie7)
    {
      $ie7_path = phpFreeChatTools::RelativePath(dirname($_SERVER["SCRIPT_FILENAME"]), $c->ie7path);
      echo "<!-- compliance patch for microsoft browsers -->\n";
      echo "<!--[if lt IE 7]>\n";
      echo "  <script type=\"text/javascript\">IE7_PNG_SUFFIX = \".png\";</script>\n";
      echo "  <script type=\"text/javascript\" src=\"".$ie7_path."/ie7-standard-p.js\"></script>\n";
      echo "<![endif]-->\n";
    }
  }

  /**
   * printChat must be called somewhere in the page
   * it inserts necessary html which will receive chat's data
   * usage:
   * <code>
   *   <?php $chat->printChat(); ?>
   * </code>
   */
  function printChat()
  {
    $c =& phpFreeChatConfig::Instance();   
    $smarty =& phpFreeChatTools::GetSmarty();
    $c->assignToSmarty($smarty);
    $smarty->display("chat.html.tpl");
  }
  
  /**
   * printStyle must be called in the header
   * it inserts CSS in order to style the chat
   * usage:
   * <code>
   *   <?php $chat->printStyle(); ?>
   * </code>
   */
  function printStyle()
  {
    $c =& phpFreeChatConfig::Instance();
    $smarty =& phpFreeChatTools::GetSmarty();
    $c->assignToSmarty($smarty);
    echo "<style type=\"text/css\">\n<!--\n";
    $smarty->display("style.css.tpl");
    if ($c->css_file)
      $smarty->display($c->css_file);
    echo "\n-->\n</style>\n";
  }
  
  function FilterNickname($nickname)
  {
    $c =& phpFreeChatConfig::Instance();
    $nickname = trim($nickname);
    $nickname = substr($nickname, 0, $c->max_nick_len);
    $nickname = htmlspecialchars(stripslashes($nickname));
    return $nickname;
  }
  
  /**
   * search/replace smileys
   */
  function FilterSmiley($msg)
  {
    $c =& phpFreeChatConfig::Instance();
    // build a preg_replace array
    $search = array();
    $replace = array();
    $query = "/(";
    foreach($c->smileys as $s_file => $s_strs)
    {
      foreach ($s_strs as $s_str)
      {
	$query .= preg_quote($s_str)."|";
	$search[] = "/".preg_quote($s_str)."/";
	$replace[] = '<img src="'.$s_file.'" alt="'.$s_str.'" />';
      }
    }
    $query = substr($query, 0, strlen($query)-1);
    $query .= ")/i";

    $split_words = preg_split($query, $msg, -1, PREG_SPLIT_DELIM_CAPTURE);
    $msg = "";
    foreach($split_words as $word)
      $msg .= preg_replace($search, $replace, $word);
    return $msg;
  }

  
  /**
   * Filter messages before they are sent to container
   */
  function PreFilterMsg($msg)
  {
    $c =& phpFreeChatConfig::Instance();
    $msg = substr($msg, 0, $c->max_text_len);
    $msg  = htmlspecialchars(stripslashes($msg));
    
    $msg = phpFreeChat::FilterSmiley($msg);

    if ($msg[0] == "\n") $msg = substr($msg, 1); // delete the first \n generated by FF
    if (strpos($msg,"\n") > 0) $msg  = "<br/>".$msg;
    $msg = str_replace("\r\n", "<br/>", $msg);
    $msg = str_replace("\n", "<br/>", $msg);
    $msg = str_replace("\t", "    ", $msg);
    $msg = str_replace("  ", "&nbsp;&nbsp;", $msg);
    $msg = preg_replace('/(http\:\/\/[^\s]*)/i',  "<a href=\"$1\">$1</a>", $msg );
    return $msg;
  }

  /**
   * Filter messages when they are recived from container
   */
  function PostFilterMsg($msg)
  {
    $c =& phpFreeChatConfig::Instance();
    $msg = preg_replace('/('.preg_quote($c->nick).')/i',  "<strong>$1</strong>", $msg );
    return $msg;
  }

  function HandleRequest($request)
  {
    $c =& phpFreeChatConfig::Instance();
    if ($c->debug) ob_start(); // capture output
 
    $xml_reponse = new xajaxResponse();
    $request = stripslashes($request);

    // check the command
    $cmd    = "";
    $rawcmd = "";
    if (preg_match("/^\/([a-z]*)( ([0-9a-f]*)|)( (.*)|)/i", $request, $res))
    {
      $rawcmd   = $res[1];
      $cmd      = "Cmd_".$rawcmd;
      $clientid = $res[3];
      $param    = $res[5];
    }
    
    // call the command
    // first of all check this method really exists
    if (is_callable(array("phpFreeChat", $cmd)))
    {
      // call the command
      phpFreeChat::$cmd($xml_reponse, $clientid, $param);
    }
    else
    {
      // display an error message
      phpFreeChat::Cmd_error(&$xml_reponse, $clientid, "Unknown command [".stripslashes("/".$rawcmd." ".$param)."]");
    }
      
    // do not update twice
    if ($cmd != "Cmd_update")
    {
      // force an update just after a command is sent
      // thus the message user just poster is really fastly displayed
      phpFreeChat::Cmd_update($xml_reponse, $clientid);
    }
  
    if ($c->debug)
    {
      // capture echoed content
      // if a content not empty is captured it is a php error in the code
      $data = ob_get_contents();
      if ($data != "")
        pxlog("HandleRequest[".$c->sessionid."]: content=".$data, "chat", $c->id);
      ob_end_clean();
    }
    
    return $xml_reponse->getXML();
  }
  
  function Cmd_update(&$xml_reponse, $clientid)
  {
    $c =& phpFreeChatConfig::Instance();
    // do not update if user isn't active (have quit)
    if ($c->active)
    {
      phpFreeChat::Cmd_updateMyNick($xml_reponse, $clientid);
      phpFreeChat::Cmd_getOnlineNick($xml_reponse, $clientid);
      phpFreeChat::Cmd_getNewMsg($xml_reponse, $clientid);
      $xml_reponse->addScript("window.clearTimeout(".$c->prefix."timeout); ".$c->prefix."timeout = window.setTimeout('".$c->prefix."handleRequest(\\'/update \\'+".$c->prefix."clientid)', ".$c->refresh_delay.");");
    }
  }
  
  function Cmd_connect(&$xml_reponse, $clientid)
  {
    $c =& phpFreeChatConfig::Instance();

    // set the chat active
    $c->active = true;
    $c->saveInSession();

    // reset the message id indicator
    // i.e. be ready to re-get all last posted messages
    $_SESSION[$c->prefix."from_id_".$c->id."_".$clientid] = 0;

    // reset the nickname cache
    $_SESSION[$c->prefix."nicklist_".$c->id."_".$clientid] = NULL;
    
    // disable or not the nickname button if the frozen_nick is on/off
    if ($c->frozen_nick)
      $xml_reponse->addAssign($c->prefix."handle", "disabled", true);
    else
      $xml_reponse->addAssign($c->prefix."handle", "disabled", false);
      
    // check if the wanted nickname was allready known
    if ($c->debug)
    {
      $container =& $c->getContainerInstance();
      $nickid    = $container->getNickId($c->nick);
      pxlog("Cmd_connect[".$c->sessionid."]: nick=".$c->nick." nickid=".$nickid, "chat", $c->id);
    }

    if ($c->nick == "")
      // ask user to choose a nickname
      phpFreeChat::Cmd_asknick($xml_reponse, $clientid, "");
    else
      phpFreeChat::Cmd_nick(&$xml_reponse, $clientid, $c->nick);
    return $clientid;
  }

  function Cmd_asknick(&$xml_reponse, $clientid, $nicktochange)
  {
    $c =& phpFreeChatConfig::Instance();
    $nicktochange = phpFreeChat::FilterNickname($newtochange);
    
    if ($c->frozen_nick)
    {
      // assign a random nick
      phpFreeChat::Cmd_nick($xml_reponse, $clientid, $nicktochange."".rand(1,1000));
    }
    else
    {
      if ($nicktochange == "")
      {
        $nicktochange = $c->nick;
        $msg = "Please enter your nickname";
      }
      else
        $msg = "'".$nicktochange."' is used, please choose another nickname.";
      $xml_reponse->addScript("var newpseudo = prompt('".addslashes($msg)."', '".addslashes($nicktochange)."'); if (newpseudo) ".$c->prefix."handleRequest('/nick '+".$c->prefix."clientid + ' ' + newpseudo);");
    }
  }

  function Cmd_nick(&$xml_reponse, $clientid, $newnick)
  {
    $c =& phpFreeChatConfig::Instance();
    $newnick = phpFreeChat::FilterNickname($newnick);

    if ($newnick == "")
    {
      // the choosen nick is empty
      if ($c->debug) pxlog("Cmd_nick[".$c->sessionid."]: the choosen nick is empty", "chat", $c->id);
      phpFreeChat::Cmd_asknick($xml_reponse, $clientid, "");
      return;
    }
   
    $container =& $c->getContainerInstance();
    $newnickid = $container->getNickId($newnick);
    $oldnickid = $container->getNickId($c->nick);

    if ( $newnickid == "undefined" )
    {
      // this is a real nickname change
      $container->changeNick($newnick, $oldnickid);
      $oldnick = $c->nick;
      $c->nick = $newnick;
      $c->saveInSession();
      $xml_reponse->addAssign($c->prefix."handle", "value", $newnick);
      $xml_reponse->addScript("document.getElementById('".$c->prefix."words').focus();");
      if ($oldnick != $newnick && $oldnick != "")
	phpFreeChat::Cmd_notice($xml_reponse, $clientid, htmlspecialchars(stripslashes($oldnick))." changes his nickname to ".htmlspecialchars(stripslashes($newnick)));
      if ($c->debug) pxlog("Cmd_nick[".$c->sessionid."]: first time nick is assigned -> newnick=".$c->nick." oldnick=".$oldnick, "chat", $c->id);
      
      // new nickname is undefined (not used) and
      // current nickname (oldnickname) is not mine or is undefined
      if ($oldnickid != "" && $oldnickid != $c->sessionid)
        phpFreeChat::Cmd_notice($xml_reponse, $clientid, htmlspecialchars(stripslashes($c->nick))." is connected");
    }
    else if ($newnickid == $c->sessionid)
    {
      // user didn't change his nickname
      $xml_reponse->addAssign($c->prefix."handle", "value", $newnick);
      $xml_reponse->addScript("document.getElementById('".$c->prefix."words').focus();");
      if ($c->debug) pxlog("Cmd_nick[".$c->sessionid."]: user just reloded the page so let him keep his nickname without any warnings -> nickid=".$newnickid." nick=".$newnick, "chat", $c->id);
    }
    else
    {
      // the wanted nick is allready used
      if ($c->debug) pxlog("Cmd_nick[".$c->sessionid."]: wanted nick is allready in use -> wantednickid=".$newnickid." wantednick=".$newnick, "chat", $c->id);
      phpFreeChat::Cmd_asknick($xml_reponse, $clientid, $newnick);
    }
  }

  function Cmd_notice(&$xml_reponse, $clientid, $msg)
  {
    $c =& phpFreeChatConfig::Instance();
    if ($c->shownotice)
    {
      $container =& $c->getContainerInstance();
      $msg = phpFreeChat::PreFilterMsg($msg);
      $container->writeMsg("*notice*", $msg);
      if ($c->debug) pxlog("Cmd_notice[".$c->sessionid."]: shownotice=true msg=".$msg, "chat", $c->id);
    }
    else
    {
      if ($c->debug) pxlog("Cmd_notice[".$c->sessionid."]: shownotice=false", "chat", $c->id);
    }
  }

  function Cmd_me(&$xml_reponse, $clientid, $msg)
  {
    $c =& phpFreeChatConfig::Instance();
    $container =& $c->getContainerInstance();
    $msg = phpFreeChat::PreFilterMsg($msg);
    $container->writeMsg("*me*", $c->nick." ".$msg);
    if ($c->debug) pxlog("Cmd_me[".$c->sessionid."]: msg=".$msg, "chat", $c->id);
  }
  
  function Cmd_quit(&$xml_reponse, $clientid)
  {
    $c =& phpFreeChatConfig::Instance();
    
    // set the chat inactive
    $c->active = false;
    $c->saveInSession();

    // then remove the nickname file
    $container =& $c->getContainerInstance();
    if ($container->removeNick($c->nick))
      phpFreeChat::Cmd_notice($xml_reponse, $clientid, $c->nick." quit");

    if ($c->debug) pxlog("Cmd_quit[".$c->sessionid."]: a user just quit -> nick=".$c->nick, "chat", $c->id);
  }
  
  function Cmd_getOnlineNick(&$xml_reponse, $clientid)
  {
    $c =& phpFreeChatConfig::Instance();

    // get the actual nicklist
    $oldnicklist = $_SESSION[$c->prefix."nicklist_".$c->id."_".$clientid];
    
    $container =& $c->getContainerInstance();
    $disconnected_users = $container->removeObsoletNick();
    foreach ($disconnected_users as $u)
      phpFreeChat::Cmd_notice($xml_reponse, $clientid, $u." disconnected (timeout)");
    $users = $container->getOnlineNick();
    sort($users);
    // check if the nickname list must be updated
    if ($oldnicklist != $users)
    {
      if ($c->debug) pxlog("Cmd_getOnlineNick[".$c->sessionid."]: nicklist updated - nicklist=".var_export($users, true), "chat", $c->id);

      $_SESSION[$c->prefix."nicklist_".$c->id."_".$clientid] = $users;

      $html = '<ul>';
      $js = "";
      foreach ($users as $u)
      {
        $nickname = htmlspecialchars(stripslashes($u));
        $html    .= '<li>'.$nickname.'</li>';
        $js      .= "'".$nickname."',";
      }
      $html .= '</ul>';
      $js    = substr($js, 0, strlen($js)-1); // remove last ','
      
      $xml_reponse->addAssign($c->prefix."online", "innerHTML", $html);
      $xml_reponse->addScript($c->prefix."nicklist = Array(".$js.");");
    }
  }

  function Cmd_updateMyNick(&$xml_reponse, $clientid)
  {
    $c =& phpFreeChatConfig::Instance();
    $container =& $c->getContainerInstance();
    $ok = $container->updateNick($c->nick);
    if (!$ok)
      phpFreeChat::Cmd_error(&$xml_reponse, $clientid, "Cmd_updateMyNick failed");
  }
  
  function Cmd_getNewMsg(&$xml_reponse, $clientid)
  {
    // get params from config obj
    $c =& phpFreeChatConfig::Instance();
    
    // check this methode is not being called
    if( isset($_SESSION[$c->prefix."lock_readnewmsg_".$c->id."_".$clientid]) )
    {
      // kill the lock if it has been created more than 10 seconds ago
      $last_10sec = time()-10;
      $last_lock = $_SESSION[$c->prefix."lock_readnewmsg_".$c->id."_".$clientid];
      if ($last_lock < $last_10sec) $_SESSION[$c->prefix."lock_".$c->id."_".$clientid] = 0;
      if ( $_SESSION[$c->prefix."lock_readnewmsg_".$c->id] != 0 ) exit;
    }

    // create a new lock
    $_SESSION[$c->prefix."lock_readnewmsg_".$c->id."_".$clientid] = time();
    
    $from_id = $_SESSION[$c->prefix."from_id_".$c->id."_".$clientid];
    
    $container =& $c->getContainerInstance();
    $new_msg = $container->readNewMsg($from_id);
    $new_from_id = $new_msg["new_from_id"];
    $messages    = $new_msg["messages"];

    // transform new message in html format
    $html = '';
    foreach ($messages as $msg)
    {
      $m_date   = isset($msg[1]) ? $msg[1] : "";
      $m_heure  = isset($msg[2]) ? $msg[2] : "";
      $m_pseudo = isset($msg[3]) ? $msg[3] : "";
      $m_words  = isset($msg[4]) ? $msg[4] : "";
      //$m_words  = phpFreeChat::PostFilterMsg(isset($msg[4]) ? $msg[4] : "");
      $m_cmd    = "cmd_msg";
      if (preg_match("/\*([a-z]*)\*/i", $msg[3], $res))
      {
	if ($res[1] == "notice")
	  $m_cmd = "cmd_notice";
	else if ($res[1] == "me")
	  $m_cmd = "cmd_me";
      }
      $html .= '<div id="'.$c->prefix.'msg'.$msg[0].'" class="'.$c->prefix.$m_cmd.' '.$c->prefix.'message'.($from_id == 0 ? " ".$c->prefix."oldmsg" : "").'">';
      $html .= '<span class="'.$c->prefix.'date'.(($m_date!="" && date("d/m/Y") == $m_date) ? " ".$c->prefix."invisible" : "" ).'">'.$m_date.'</span> ';
      $html .= '<span class="'.$c->prefix.'heure">'.$m_heure.'</span> ';
      if ($m_cmd == "cmd_msg")
      {
	$html .= '<span class="'.$c->prefix.'pseudo">&lt;'.$m_pseudo.'&gt;</span> ';
	$html .= '<span class="'.$c->prefix.'words">'.$m_words.'</span>';
      }
      else if ($m_cmd == "cmd_notice" || $m_cmd == "cmd_me")
      {
	$html .= '<span class="'.$c->prefix.'words">* '.$m_words.'</span>';
      }
      $html .= '</div>';
    }
  	
    if ($html != "") // do not send anything if there is no new messages to show
    {
      // store the new msg id
      $_SESSION[$c->prefix."from_id_".$c->id."_".$clientid] = $new_from_id;
      // append new messages to chat zone
      $xml_reponse->addAppend($c->prefix."chat", "innerHTML", $html);
      // move the scrollbar from N line down
      $xml_reponse->addScript('var div_msg; var msg_height = 0;');
      foreach ($messages as $msg)
        $xml_reponse->addScript('div_msg = document.getElementById(\''.$c->prefix.'msg'.$msg[0].'\'); msg_height += div_msg.offsetHeight+2;');
      $xml_reponse->addScript('document.getElementById(\''.$c->prefix.'chat\').scrollTop += msg_height;');
    }

    // remove the lock
    $_SESSION[$c->prefix."lock_readnewmsg_".$c->id."_".$clientid] = 0;
  }
  
  function Cmd_send(&$xml_reponse, $clientid, $msg)
  {
    $c =& phpFreeChatConfig::Instance();
        
    // check the nick is not allready known
    $nick = $c->nick;
    $text = phpFreeChat::PreFilterMsg($msg);
        
    $errors = array();
    if ($text == "") $errors[$c->prefix."words"] = "Text cannot be empty.";
    if ($nick == "") $errors[$c->prefix."handle"] = "Please enter your nickname.";
    if (count($errors) == 0)
    {
      $container =& $c->getContainerInstance();
      $container->writeMsg($nick, $text);
      if ($c->debug) pxlog("Cmd_send[".$c->sessionid."]: a user just sent a message -> nick=".$c->nick." m=".$text, "chat", $c->id);
    	
      // a message has been posted so :
      // - read new messages
      // - give focus to "words" field
      $xml_reponse->addScript($c->prefix."ClearError(Array('".$c->prefix."words"."','".$c->prefix."handle"."'));");
      $xml_reponse->addScript("document.getElementById('".$c->prefix."words').focus();");
    }
    else
    {
      // an error occured, just ignore the message and display errors
      foreach($errors as $e)
        if ($c->debug) pxlog("Cmd_send[".$c->sessionid."]: user can't send a message -> nick=".$c->nick." err=".$e, "chat", $c->id);
      phpFreeChat::Cmd_error($xml_reponse, $clientid, $errors);
      if (isset($errors[$c->prefix."handle"])) // the nick is empty so give it focus
        $xml_reponse->addScript("document.getElementById('".$c->prefix."handle').focus();");
    }
  }
  
  function Cmd_join(&$xml_reponse, $clientid, $newchat)
  {
    $c =& phpFreeChatConfig::Instance();
  }
  
  function Cmd_error(&$xml_reponse, $clientid, $errors)
  {
    $c =& phpFreeChatConfig::Instance();
    if (is_array($errors))
    {
      $error_ids = ""; $error_str = "";
      foreach ($errors as $k => $e) { $error_ids .= ",'".$k."'"; $error_str.= $e." "; }
      $error_ids = substr($error_ids,1);
      $xml_reponse->addScript($c->prefix."SetError('".addslashes(stripslashes($error_str))."', Array(".$error_ids."));");
    }
    else
      $xml_reponse->addScript($c->prefix."SetError('".addslashes(stripslashes($errors))."', Array());");
  }  
}

?>

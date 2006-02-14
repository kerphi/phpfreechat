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
require_once dirname(__FILE__)."/phpfreechattemplate.class.php";
require_once dirname(__FILE__)."/../debug/log.php";
require_once dirname(__FILE__)."/../lib/utf8/utf8.php";

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
    session_name( "phpfreechat" );
    if (isset($_GET["init"])) unset($_COOKIE[session_name()]);
    if(session_id() == "") session_start();

    $params["sessionid"] = session_id();
    
    $c =& phpFreeChatConfig::Instance( $params );
    
    // Xajax doesn't support yet static class methode call
    // I use basic functions to wrap to my statics methodes
    function handleRequest($request)
    {
      return phpFreeChat::HandleRequest($request);
    }
    // then init xajax engine
    if (!class_exists("xajax")) require_once $c->xajaxpath."/xajax.inc.php";
    $this->xajax = new xajax($c->server_script_url, $c->prefix);
    //$this->xajax->debugOn();
    $this->xajax->waitCursorOff(); // do not show a wait cursor during chat updates
    $this->xajax->errorHandlerOn(); // used to have verbose error logs
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
  function printJavaScript( $return = false )
  {
    $output = '';
    $c =& phpFreeChatConfig::Instance();
    phpFreeChatI18N::SwitchOutputEncoding($c->output_encoding);

    // include javascript libraries
    $js_path = $c->data_public_url."/javascript";
    $output .= "<script type=\"text/javascript\" src=\"".$js_path."/md5.js\"></script>";
    $output .= "<script type=\"text/javascript\" src=\"".$js_path."/cookie.js\"></script>";
    $output .= "<script type=\"text/javascript\" src=\"".$js_path."/image_preloader.js\"></script>";

    // print xajax javascript
    $output .= $this->xajax->getJavascript($c->data_public_url, NULL, $c->data_public_url."/xajax_js/xajax.js");

    // print phpfreechat specific javascript
    $t = new phpFreeChatTemplate($c->tplpath."/".$c->tpltheme."/javascript1.js.tpl.php");
    $t->assignObject($c);
    $output .= "<script type=\"text/javascript\">\n<!--\n";
    $output .= $t->getOutput();
    $output .= "\n-->\n</script>\n";

    // include microsoft IE6 patches
    if ($c->useie7)
    {
      $ie7_path = $c->data_public_url."/ie7";
      $output .= "<!-- compliance patch for microsoft browsers -->\n";
      $output .= "<!--[if lt IE 7]>\n";
      $output .= "  <script type=\"text/javascript\">IE7_PNG_SUFFIX = \".png\";</script>\n";
      $output .= "  <script type=\"text/javascript\" src=\"".$ie7_path."/ie7-standard-p.js\"></script>\n";
      $output .= "<![endif]-->\n";
    }
    phpFreeChatI18N::SwitchOutputEncoding();
	
    // display output
    if ($return)
      return $output;
    else
      echo $output;
  }

  /**
   * printChat must be called somewhere in the page
   * it inserts necessary html which will receive chat's data
   * usage:
   * <code>
   *   <?php $chat->printChat(); ?>
   * </code>
   */
  function printChat( $return = false )
  {
    $c =& phpFreeChatConfig::Instance();
    phpFreeChatI18N::SwitchOutputEncoding($c->output_encoding);
    $t = new phpFreeChatTemplate($c->tplpath."/".$c->tpltheme."/chat.html.tpl.php");
    $t->assignObject($c);
    $output = $t->getOutput();
    phpFreeChatI18N::SwitchOutputEncoding();
    if($return) 
      return $output;
    else 
      echo $output;
  }
  
  /**
   * printStyle must be called in the header
   * it inserts CSS in order to style the chat
   * usage:
   * <code>
   *   <?php $chat->printStyle(); ?>
   * </code>
   */
  function printStyle( $return = false )
  {
    $output = '';
    $c =& phpFreeChatConfig::Instance();
    phpFreeChatI18N::SwitchOutputEncoding($c->output_encoding);

    $css_filename = $c->tplpath."/".$c->tpltheme."/style.css.tpl.php";
    $t = new phpFreeChatTemplate($css_filename);
    $t->assignObject($c);
    $output .= $t->getOutput();
    if ($c->css_file != "")
    {
      $t->setTemplate($c->css_file);
      $output .= $t->getOutput();
    }

    // optimize css
    require_once $c->csstidypath."/css_parser.php";
    $csstidy = new csstidy();
    $csstidy->set_cfg('remove_last_;',TRUE);
    $csstidy->parse($output);
    $output = $csstidy->print_code(NULL, true); 

    // output css
    phpFreeChatI18N::SwitchOutputEncoding();
    $output = "<style type=\"text/css\">\n".$output."\n</style>\n";
    if($return)
      return $output;
    else 
      echo $output;
  }

  /**
   * Encode special caracteres and remove extra slashes
   */
  function FilterSpecialChar($str)
  {
    //$str = stripslashes($str);
    //    $str = addslashes($str);
    $str = htmlspecialchars($str);
    return $str;
  }
  
  /**
   * Just check the nicknames doesn't start with spaces and is not longer than the max_nick_len
   */
  function FilterNickname($nickname)
  {
    $c =& phpFreeChatConfig::Instance();
    //$nickname = str_replace("\\", "", $nickname); // '\' is a forbidden charactere for nicknames
    $nickname = trim($nickname);
    $nickname = utf8_substr($nickname, 0, $c->max_nick_len);
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
        $s_str = stripslashes($s_str); /* the :'( smileys needs this filter */
	$query .= preg_quote($s_str,'/')."|";
	$search[] = "/".preg_quote($s_str,'/')."/";
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
    $msg = phpFreeChat::FilterSpecialChar($msg);
    
    $msg = phpFreeChat::FilterSmiley($msg);

    /*    if ($msg[0] == "\n") $msg = substr($msg, 1); */ // delete the first \n generated by FF
    /* if (strpos($msg,"\n") > 0) $msg  = "<br/>".$msg;
    $msg = str_replace("\r\n", "<br/>", $msg);
    $msg = str_replace("\n", "<br/>", $msg);
    $msg = str_replace("\t", "    ", $msg);*/
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
    $msg = preg_replace('/('.preg_quote($c->nick,'/').')/i', "<strong>$1</strong>", $msg );
    $msg = preg_replace('/\n/i', "", $msg );
    return $msg;
  }

  function HandleRequest($request)
  {
    $c =& phpFreeChatConfig::Instance();
    if ($c->debug) ob_start(); // capture output
 
    $xml_reponse = new xajaxResponse();
    //    $request = stripslashes($request);

    // check the command
    $cmd    = "";
    $rawcmd = "";
    if (preg_match("/^\/([a-z]*)( ([0-9a-f]*)|)( (.*)|)/i", $request, $res))
    {
      $rawcmd   = isset($res[1]) ? $res[1] : "";
      $cmd      = "Cmd_".$rawcmd;
      $clientid = isset($res[3]) ? $res[3] : "";
      $param    = isset($res[5]) ? $res[5] : "";
    }
    
    // call the command
    // first of all check this method really exists
    if (is_callable(array("phpFreeChat", $cmd)))
    {
      // call the command
      if ($c->debug)
        phpFreeChat::$cmd($xml_reponse, $clientid, $param);
      else
        @phpFreeChat::$cmd($xml_reponse, $clientid, $param);
    }
    else
    {
      // display an error message
      phpFreeChat::Cmd_error($xml_reponse, $clientid, __("Unknown command [%s]",stripslashes("/".$rawcmd." ".$param)));
    }
      
    // do not update twice
    if ($cmd != "Cmd_update" &&
        $c->nick != "")
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
        pxlog("HandleRequest[".$c->sessionid."]: content=".$data, "chat", $c->getId());
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
      $xml_reponse->addScript("window.clearTimeout(".$c->prefix."timeout); ".$c->prefix."timeout = window.setTimeout('".$c->prefix."handleRequest(\\'/update ".$clientid."\\')', ".$c->refresh_delay.");");
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
    $_SESSION[$c->prefix."from_id_".$c->getId()."_".$clientid] = 0;

    // reset the nickname cache
    $_SESSION[$c->prefix."nicklist_".$c->getId()."_".$clientid] = NULL;
    
    // disable or not the nickname button if the frozen_nick is on/off
    if ($c->frozen_nick)
      $xml_reponse->addAssign($c->prefix."handle", "disabled", true);
    else
      $xml_reponse->addAssign($c->prefix."handle", "disabled", false);

    // disconnect last connected users if necessary 
    phpFreeChat::Cmd_getOnlineNick($xml_reponse, $clientid);
    
    // check if the wanted nickname was allready known
    if ($c->debug)
    {
      $container =& $c->getContainerInstance();
      $nickid    = $container->getNickId($c->nick);
      pxlog("Cmd_connect[".$c->sessionid."]: nick=".$c->nick." nickid=".$nickid, "chat", $c->getId());
    }

    if ($c->nick == "")
      // ask user to choose a nickname
      phpFreeChat::Cmd_asknick($xml_reponse, $clientid, "");
    else
      phpFreeChat::Cmd_nick($xml_reponse, $clientid, $c->nick);
    return $clientid;
  }

  function Cmd_asknick(&$xml_reponse, $clientid, $nicktochange)
  {
    $c =& phpFreeChatConfig::Instance();
    $nicktochange = phpFreeChat::FilterNickname($nicktochange);
    
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
        $msg = __("Please enter your nickname");
      }
      else
        $msg = "'".$nicktochange."' is used, please choose another nickname.";
      $xml_reponse->addScript("var newnick = prompt('".addslashes($msg)."', '".addslashes($nicktochange)."'); if (newnick) ".$c->prefix."handleRequest('/nick '+".$c->prefix."clientid + ' ' + newnick);");
    }
  }

  function Cmd_nick(&$xml_reponse, $clientid, $newnick)
  {
    $c =& phpFreeChatConfig::Instance();
    $newnick = phpFreeChat::FilterNickname($newnick);

    if ($c->debug) pxlog("Cmd_nick[".$c->sessionid."]: newnick=".preg_quote($c->nick,'/'), "chat", $c->getId());

    if ($newnick == "")
    {
      // the choosen nick is empty
      if ($c->debug) pxlog("Cmd_nick[".$c->sessionid."]: the choosen nick is empty", "chat", $c->getId());
      phpFreeChat::Cmd_asknick($xml_reponse, $clientid, "");
      return;
    }
   
    $container =& $c->getContainerInstance();
    $newnickid = $container->getNickId($newnick);
    $oldnickid = $container->getNickId($c->nick);

    if ( $newnickid == "undefined" )
    {
      // this is a real nickname change
      $container->changeNick($newnick);
      $oldnick = $c->nick;
      $c->nick = $newnick;
      $c->saveInSession();
      $xml_reponse->addAssign($c->prefix."handle", "value", $newnick);
      $xml_reponse->addScript("document.getElementById('".$c->prefix."words').focus();");
      if ($oldnick != $newnick && $oldnick != "")
	phpFreeChat::Cmd_notice($xml_reponse, $clientid, __("%s changes his nickname to %s",$oldnick,$newnick), 1);
      if ($c->debug) pxlog("Cmd_nick[".$c->sessionid."]: first time nick is assigned -> newnick=".$c->nick." oldnick=".$oldnick, "chat", $c->getId());
      
      // new nickname is undefined (not used) and
      // current nickname (oldnickname) is not mine or is undefined
      if ($oldnickid != $c->sessionid)
        phpFreeChat::Cmd_notice($xml_reponse, $clientid, __("%s is connected",$c->nick), 2);
    }
    else if ($newnickid == $c->sessionid)
    {
      // user didn't change his nickname
      $xml_reponse->addAssign($c->prefix."handle", "value", $newnick);
      $xml_reponse->addScript("document.getElementById('".$c->prefix."words').focus();");
      if ($c->debug) pxlog("Cmd_nick[".$c->sessionid."]: user just reloded the page so let him keep his nickname without any warnings -> nickid=".$newnickid." nick=".$newnick, "chat", $c->getId());
    }
    else
    {
      // the wanted nick is allready used
      if ($c->debug) pxlog("Cmd_nick[".$c->sessionid."]: wanted nick is allready in use -> wantednickid=".$newnickid." wantednick=".$newnick, "chat", $c->getId());
      phpFreeChat::Cmd_asknick($xml_reponse, $clientid, $newnick);
    }
  }

  function Cmd_notice(&$xml_reponse, $clientid, $msg, $level = 2)
  {
    $c =& phpFreeChatConfig::Instance();
    if ($c->shownotice > 0 &&
        $c->shownotice >= $level)
    {
      $container =& $c->getContainerInstance();
      $msg = phpFreeChat::FilterSpecialChar($msg);
      $container->writeMsg("*notice*", $msg);
      if ($c->debug) pxlog("Cmd_notice[".$c->sessionid."]: shownotice=true msg=".$msg, "chat", $c->getId());
    }
    else
    {
      if ($c->debug) pxlog("Cmd_notice[".$c->sessionid."]: shownotice=false", "chat", $c->getId());
    }
  }

  function Cmd_me(&$xml_reponse, $clientid, $msg)
  {
    $c =& phpFreeChatConfig::Instance();
    $container =& $c->getContainerInstance();
    $msg = phpFreeChat::PreFilterMsg($msg);
    $container->writeMsg("*me*", $c->nick." ".$msg);
    if ($c->debug) pxlog("Cmd_me[".$c->sessionid."]: msg=".$msg, "chat", $c->getId());
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
      phpFreeChat::Cmd_notice($xml_reponse, $clientid, __("%s quit", $c->nick), 2);

    if ($c->debug) pxlog("Cmd_quit[".$c->sessionid."]: a user just quit -> nick=".$c->nick, "chat", $c->getId());
  }
  
  function Cmd_getOnlineNick(&$xml_reponse, $clientid)
  {
    $c =& phpFreeChatConfig::Instance();

    // get the actual nicklist
    $nicklist_sid = $c->prefix."nicklist_".$c->getId()."_".$clientid;
    $oldnicklist = isset($_SESSION[$nicklist_sid]) ? $_SESSION[$nicklist_sid] : array();
    
    $container =& $c->getContainerInstance();
    $disconnected_users = $container->removeObsoleteNick();
    foreach ($disconnected_users as $u)
      phpFreeChat::Cmd_notice($xml_reponse, $clientid, __("%s disconnected (timeout)",$u), 2);
    $users = $container->getOnlineNick();
    sort($users);
    // check if the nickname list must be updated
    if ($oldnicklist != $users)
    {
      if ($c->debug) pxlog("Cmd_getOnlineNick[".$c->sessionid."]: nicklist updated - nicklist=".var_export($users, true), "chat", $c->getId());

      $_SESSION[$nicklist_sid] = $users;

      $js = "";
      foreach ($users as $u)
      {
        $nickname = addslashes($u); // must escape ' charactere for javascript string
        $js      .= "'".$nickname."',";
      }
      $js    = substr($js, 0, strlen($js)-1); // remove last ','
      
      $xml_reponse->addScript($c->prefix."nicklist = Array(".$js.");");
      $xml_reponse->addScript($c->prefix."updateNickList(".$c->prefix."nicklist);");
    }
  }

  function Cmd_updateMyNick(&$xml_reponse, $clientid)
  {
    $c =& phpFreeChatConfig::Instance();
    $container =& $c->getContainerInstance();
    $ok = $container->updateNick($c->nick);
    if (!$ok)
      phpFreeChat::Cmd_error($xml_reponse, $clientid, "Cmd_updateMyNick failed");
  }
  
  function Cmd_getNewMsg(&$xml_reponse, $clientid)
  {
    // get params from config obj
    $c =& phpFreeChatConfig::Instance();
    
    // check this methode is not being called
    if( isset($_SESSION[$c->prefix."lock_readnewmsg_".$c->getId()."_".$clientid]) )
    {
      // kill the lock if it has been created more than 10 seconds ago
      $last_10sec = time()-10;
      $last_lock = $_SESSION[$c->prefix."lock_readnewmsg_".$c->getId()."_".$clientid];
      if ($last_lock < $last_10sec) $_SESSION[$c->prefix."lock_".$c->getId()."_".$clientid] = 0;
      if ( $_SESSION[$c->prefix."lock_readnewmsg_".$c->getId()."_".$clientid] != 0 ) exit;
    }

    // create a new lock
    $_SESSION[$c->prefix."lock_readnewmsg_".$c->getId()."_".$clientid] = time();
    
    $from_id = isset($_SESSION[$c->prefix."from_id_".$c->getId()."_".$clientid]) ? $_SESSION[$c->prefix."from_id_".$c->getId()."_".$clientid] : 0;
    
    $container =& $c->getContainerInstance();
    $new_msg = $container->readNewMsg($from_id);
    $new_from_id = $new_msg["new_from_id"];
    $messages    = $new_msg["messages"];

    // transform new message in html format
    $msg_sent = false;
    foreach ($messages as $msg)
    {
      $m_id     = isset($msg[0]) ? $msg[0] : "";
      $m_date   = isset($msg[1]) ? $msg[1] : "";
      $m_heure  = isset($msg[2]) ? $msg[2] : "";
      $m_nick   = isset($msg[3]) ? $msg[3] : "";
      $m_words  = phpFreeChat::PostFilterMsg(isset($msg[4]) ? $msg[4] : "");
      $m_cmd    = "cmd_msg";
      if (preg_match("/\*([a-z]*)\*/i", $msg[3], $res))
      {
	if ($res[1] == "notice")
	  $m_cmd = "cmd_notice";
	else if ($res[1] == "me")
	  $m_cmd = "cmd_me";
      }
      $xml_reponse->addScript($c->prefix."parseAndPost(".$m_id.",'".addslashes($m_date)."','".addslashes($m_heure)."','".addslashes($m_nick)."','".addslashes($m_words)."','".addslashes($m_cmd)."',".(date("d/m/Y") == $m_date ? 1 : 0).",".($from_id == 0? 1 : 0).");");
      $msg_sent = true;
    }
  	
    if ($msg_sent)
    {
      // store the new msg id
      $_SESSION[$c->prefix."from_id_".$c->getId()."_".$clientid] = $new_from_id;
    }

    // remove the lock
    $_SESSION[$c->prefix."lock_readnewmsg_".$c->getId()."_".$clientid] = 0;
  }
  
  function Cmd_send(&$xml_reponse, $clientid, $msg)
  {
    $c =& phpFreeChatConfig::Instance();
        
    // check the nick is not allready known
    $nick = phpFreeChat::FilterSpecialChar($c->nick);
    $text = phpFreeChat::PreFilterMsg($msg);
        
    $errors = array();
    if ($text == "") $errors[$c->prefix."words"] = __("Text cannot be empty");
    if ($nick == "") $errors[$c->prefix."handle"] = __("Please enter your nickname");
    if (count($errors) == 0)
    {
      $container =& $c->getContainerInstance();
      $container->writeMsg($nick, $text);
      if ($c->debug) pxlog("Cmd_send[".$c->sessionid."]: a user just sent a message -> nick=".$c->nick." m=".$text, "chat", $c->getId());
    	
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
        if ($c->debug) pxlog("Cmd_send[".$c->sessionid."]: user can't send a message -> nick=".$c->nick." err=".$e, "chat", $c->getId());
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

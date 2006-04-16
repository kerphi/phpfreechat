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

require_once dirname(__FILE__)."/pfccommand.class.php";
require_once dirname(__FILE__)."/pfcglobalconfig.class.php";
require_once dirname(__FILE__)."/pfcuserconfig.class.php";
require_once dirname(__FILE__)."/phpfreechattemplate.class.php";
require_once dirname(__FILE__)."/../lib/utf8/utf8.php";

/**
 * phpFreeChat is the entry point for developpers
 *
 * @example ../demo/demo1_simple.php
 * @author Stephane Gully <stephane.gully@gmail.com>
 */
class phpFreeChat
{
  var $xajax;
  
  function phpFreeChat( &$params )
  {
    if (isset($params["debug"]) && $params["debug"])
      require_once dirname(__FILE__)."/../debug/log.php";

    // check if the given parameters is a simple array
    // or a allready created phpfreechat object
    $c = NULL;
    if (is_object($params) &&
        get_class($params) == "pfcglobalconfig")
      $c =& $params;
    else
      $c =& pfcGlobalConfig::Instance( $params );

    // need to initiate the user config object here because it uses sessions
    $u =& pfcUserConfig::Instance();
    
    // Xajax doesn't support yet static class methode call
    // I use basic functions to wrap to my statics methodes
    function handleRequest($request)
    {
      return phpFreeChat::HandleRequest($request);
    }
    // then init xajax engine
    if (!class_exists("xajax")) require_once $c->xajaxpath."/xajax.inc.php";
    $this->xajax = new xajax($c->server_script_url.(isset($_SERVER["QUERY_STRING"]) && $_SERVER["QUERY_STRING"] != "" ? "?".$_SERVER["QUERY_STRING"] : ""), $c->prefix);
    if ($c->debugxajax) $this->xajax->debugOn();
    $this->xajax->waitCursorOff(); // do not show a wait cursor during chat updates
    $this->xajax->cleanBufferOff();
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
    $c =& pfcGlobalConfig::Instance();
    $u =& pfcUserConfig::Instance();
    //trigger_error("u=".var_export($u));

    pfcI18N::SwitchOutputEncoding($c->output_encoding);
    
    // include javascript libraries
    $js_path = $c->data_public_url."/javascript";
    $output .= "<script type=\"text/javascript\" src=\"".$js_path."/md5.js\"></script>";
    $output .= "<script type=\"text/javascript\" src=\"".$js_path."/cookie.js\"></script>";
    $output .= "<script type=\"text/javascript\" src=\"".$js_path."/image_preloader.js\"></script>";
    $output .= "<script type=\"text/javascript\" src=\"".$js_path."/prototype.js\"></script>";
    $output .= "<script type=\"text/javascript\" src=\"".$js_path."/regex.js\"></script>";
    $output .= "<script type=\"text/javascript\" src=\"".$js_path."/utf8.js\"></script>";
    
    // print phpfreechat specific javascript
    $t = new phpFreeChatTemplate();
    $t->assignObject($c,"c");
    $t->assignObject($u,"u");
    $output .= "<script type=\"text/javascript\">\n // <![CDATA[\n";
    $t->setTemplate($c->getFilePathFromTheme("templates/pfcgui.js.tpl.php"));
    $output .= $t->getOutput();
    $t->setTemplate($c->getFilePathFromTheme("templates/pfcclient.js.tpl.php"));
    $output .= $t->getOutput();
    $output .= "\n // ]]>\n</script>\n";
    
    // print xajax javascript
    $output .= $this->xajax->getJavascript($c->data_public_url, NULL, $c->data_public_url."/xajax_js/xajax.js");

    // include microsoft IE6 patches
    if ($c->useie7)
    {
      $ie7_path = $c->data_public_url."/ie7";
      $output .= "<!-- compliance patch for microsoft browsers -->\n";
      $output .= "<!--[if lt IE 7]>\n";
      $output .= "  <script type=\"text/javascript\">IE7_PNG_SUFFIX = \".png\";</script>\n";
      $output .= "  <script type=\"text/javascript\" src=\"".$ie7_path."/ie7-standard-p.js\"></script>\n";
      $output .= "  <script type=\"text/javascript\" src=\"".$ie7_path."/ie7-recalc.js\"></script>\n";
      $output .= "<![endif]-->\n";
    }
    pfcI18N::SwitchOutputEncoding();
	
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
    $c =& pfcGlobalConfig::Instance();
    $u =& pfcUserConfig::Instance();

    pfcI18N::SwitchOutputEncoding($c->output_encoding);

    $t = new phpFreeChatTemplate($c->getFilePathFromTheme("templates/chat.html.tpl.php"));
    $t->assignObject($u,"u");
    $t->assignObject($c,"c");
    $output = $t->getOutput();
    
    pfcI18N::SwitchOutputEncoding();

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
    $c =& pfcGlobalConfig::Instance();
    $u =& pfcUserConfig::Instance();

    pfcI18N::SwitchOutputEncoding($c->output_encoding);

    $css_filename1 = dirname(__FILE__)."/../themes/default/templates/style.css.tpl.php";
    $css_filename2 = $c->getFilePathFromTheme("templates/style.css.tpl.php");
    $t = new phpFreeChatTemplate();
    $t->assignObject($u,"u");
    $t->assignObject($c,"c");
    $t->setTemplate($css_filename1);
    $output .= $t->getOutput();
    if ($css_filename1 != $css_filename2)
    {
      $t->setTemplate($css_filename2);
      $output .= $t->getOutput();
    }

    if ($c->usecsstidy)
    {
      // optimize css
      require_once $c->csstidypath."/css_parser.php";
      $csstidy = new csstidy();
      $csstidy->set_cfg('remove_last_;',TRUE);
      $csstidy->parse($output);
      $output = $csstidy->print_code(NULL, true); 
    }

    // output css
    pfcI18N::SwitchOutputEncoding();
    $output = "<style type=\"text/css\">\n".$output."\n</style>\n";

    // tabpane
    $output .= '<link id="luna-tab-style-sheet" type="text/css" rel="stylesheet" href="'.$c->data_public_url.'/tabpane/css/luna/tab.css" />';
    
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
    $c =& pfcGlobalConfig::Instance();
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
    $c =& pfcGlobalConfig::Instance();
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
	$replace[] = '<img src="'.$s_file.'" alt="'.$s_str.'" title="'.$s_str.'" />';
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
    $c =& pfcGlobalConfig::Instance();
    $msg = substr($msg, 0, $c->max_text_len);
    $msg = phpFreeChat::FilterSpecialChar($msg);
    
    //    $msg = phpFreeChat::FilterSmiley($msg);

    /*    if ($msg[0] == "\n") $msg = substr($msg, 1); */ // delete the first \n generated by FF
    /* if (strpos($msg,"\n") > 0) $msg  = "<br/>".$msg;
    $msg = str_replace("\r\n", "<br/>", $msg);
    $msg = str_replace("\n", "<br/>", $msg);
    $msg = str_replace("\t", "    ", $msg);*/
    //$msg = str_replace("  ", "&nbsp;&nbsp;", $msg);
    //    $msg = preg_replace('/(http\:\/\/[^\s]*)/i',  "<a href=\"$1\">$1</a>", $msg );
    return $msg;
  }

  /**
   * Filter messages when they are recived from container
   */
  function PostFilterMsg($msg)
  {
    //$c =& pfcGlobalConfig::Instance();
    //    $msg = preg_replace('/('.preg_quote($c->nick,'/').')/i', "<strong>$1</strong>", $msg );
    $msg = preg_replace('/\n/i', "", $msg );
    return $msg;
  }

  function HandleRequest($request)
  {
    $c =& pfcGlobalConfig::Instance();
    $u =& pfcUserConfig::Instance();

    if ($c->debug) ob_start(); // capture output
 
    $xml_reponse = new xajaxResponse();

    // check the command
    $rawcmd    = "";
    $clientid  = "";
    $recipient   = "";
    $recipientid = "";
    $param     = "";
    $sender    = "";
    //if (preg_match("/^\/([a-z]*) ([0-9a-f]*) ([0-9a-f]*)( (.*)|)/", $request, $res))
    //if (preg_match("/^\/([a-z]+) ([0-9a-f]+) ([0-9a-f]+) (.*)/", $request, $res))
    if (preg_match("/^\/([a-z]+) ([0-9a-f]+) ([0-9a-f]+)( (.*)|)/", $request, $res))
    {
      
      $rawcmd      = isset($res[1]) ? $res[1] : "";
      $clientid    = isset($res[2]) ? $res[2] : "";
      $recipientid = isset($res[3]) ? $res[3] : "";
      $param       = isset($res[5]) ? $res[5] : "";
      $sender      = $u->nick;
      //      $recipient   = "home";

      //if ($rawcmd == "join")
      //  trigger_error(var_export($res));

    }

    //if ($rawcmd == "join")
    //trigger_error("channels=".var_export($u->channels));
    //trigger_error("pvs=".var_export($u->privmsg));
    
    // translate the recipientid to the channel name
    if (isset($u->channels[$recipientid]))
    {
      $recipient = $u->channels[$recipientid]["recipient"];
    }
    if (isset($u->privmsg[$recipientid]))
    {
      $recipient = $u->privmsg[$recipientid]["recipient"];

      if ($rawcmd != "update")
      {
        // alert the other from the new pv
        // (warn other user that someone talk to him)
        $container =& $c->getContainerInstance();
        $pvs = $container->getMeta("privmsg", "nickname", $u->privmsg[$recipientid]["name"]);
        if (is_string($pvs)) $pvs = unserialize($pvs);
        if (!is_array($pvs)) $pvs = array();
        if (!in_array($u->nick,$pvs))
        {
          $pvs[] = $u->nick;
          //          $xml_reponse->addScript("alert('pvs[]=".serialize($pvs)."');");
          $container->setMeta(serialize($pvs), "privmsg", "nickname", $u->privmsg[$recipientid]["name"]);
        }
      }
    }
    
    $cmd =& pfcCommand::Factory($rawcmd);
    if ($cmd != NULL)
    {
      // call the command
      if ($c->debug)
	$cmd->run($xml_reponse, $clientid, $param, $sender, $recipient, $recipientid);
      else
	@$cmd->run($xml_reponse, $clientid, $param, $sender, $recipient, $recipientid);
    }
    else
    {
      $cmd =& pfcCommand::Factory("error");
      if ($c->debug)
        $cmd->run($xml_reponse, $clientid, _pfc("Unknown command [%s]",stripslashes("/".$rawcmd." ".$param)), $sender, $recipient, $recipientid);
      else
        @$cmd->run($xml_reponse, $clientid, _pfc("Unknown command [%s]",stripslashes("/".$rawcmd." ".$param)), $sender, $recipient, $recipientid);
    }
    
    // do not update twice
    // do not update when the user just quit
    if ($rawcmd != "update" &&
	$rawcmd != "quit" &&
	$u->nick != "")
    {
      // force an update just after a command is sent
      // thus the message user just poster is really fastly displayed
      $cmd =& pfcCommand::Factory("update");
      if ($c->debug)
	$cmd->run($xml_reponse, $clientid, $param, $sender, $recipient, $recipientid);
      else
	@$cmd->run($xml_reponse, $clientid, $param, $sender, $recipient, $recipientid);
    }
  
    if ($c->debug)
    {
      // capture echoed content
      // if a content not empty is captured it is a php error in the code
      $data = ob_get_contents();
      if ($data != "")
        pxlog("HandleRequest: content=".$data, "chat", $c->getId());
      ob_end_clean();
    }
    
    return $xml_reponse->getXML();
  }
}

?>

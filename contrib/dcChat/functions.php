<?php

require_once dirname(__FILE__)."/config.php";

class dcChat
{
  function getRoom($mode = "general")
  {
    if ($mode == "post")
      return $GLOBALS['news']->f('post_id');
    else
      return "general";
  }
  
  function printJavascript($room = "")
  {
    if ($room == "") $room = dcChat::getRoom();
    $chat =& getChat($room);
    $chat->printJavascript();
  }
  
  function printStyle($room = "")
  {
    if ($room == "") $room = dcChat::getRoom();
    $chat =& getChat($room);
    $chat->printStyle();
  }
  
  function printChat($room = "")
  {
    if ($room == "") $room = dcChat::getRoom();
    $chat =& getChat($room);
    $chat->printChat();
  }
}

?>
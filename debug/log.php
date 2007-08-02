<?php

function pxlog($data, $section = "", $id = "")
{
  $msg = htmlspecialchars(stripslashes(var_export($data, true)));
  $filename = dirname(__FILE__)."/../data/private/debug".$section."_".$id.".log";

  @file_put_contents($filename, "[".$id."] ".date("Y/m/d H:i:s - ").$msg."\n", FILE_APPEND | LOCK_EX);
}

?>

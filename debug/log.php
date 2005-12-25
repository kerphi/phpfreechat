<?php

function pxlog($data, $section = "", $id = "")
{
  $msg = htmlspecialchars(stripslashes(var_export($data, true)));
  $filename = dirname(__FILE__)."/../cache/debug".$section."_".$id.".log";
  if (!file_exists($filename))
    touch($filename);
  $fp = fopen($filename, 'a');
  fwrite($fp, date("Y/m/d H:i:s - ").$msg."\n");
  fclose($fp);
}

?>
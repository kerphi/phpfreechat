<?php

$rootpath    = dirname(__FILE__)."/../../";

$allowedpath = array();
//%allowedpath%

// get the wanted file path
// and check if the file is allowed to be displayed
$page = isset($_GET["p"]) ? $_GET["p"] : "";
if ($page == "") die();
$files = array();
foreach($allowedpath as $ap)
{
  $f = realpath($ap."/".$page);
  if ($f !== FALSE && file_exists($f)) $files[] = $f;
}
$found = "";
for( $i = 0; $i < count($allowedpath) && $found == ""; $i++)
{
  $ap = $allowedpath[$i];
  foreach($files as $file)
  {
    if (strpos($file, $ap) === 0 ) $found = $file;
  }
}
if (trim($found) == "")
  die();
else
  $file = $found;

$contenttype   = "text/plain";
$contentlength = filesize($file);
if (preg_match("/.js$/", $file))
  $contenttype = "text/javascript";
else if (preg_match("/.css$/", $file))
  $contenttype = "text/css";
header("Content-Length: ".$contentlength);
header("Content-Type: ".$contenttype);
session_cache_limiter('public');
echo file_get_contents($file);
flush(); // needed to fix problems with gzhandler enabled
?>
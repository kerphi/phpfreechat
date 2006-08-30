<?php

require_once dirname(__FILE__).'/file_get_contents.php';
require_once dirname(__FILE__).'/file_put_contents.php';

$archivename = trim(file_get_contents(dirname(__FILE__)."/archivename"));
$archivename2 = str_replace(".tar.gz","",str_replace(".zip","",$archivename));
$mirrors = file(dirname(__FILE__)."/mirrors");
$dstpath = dirname(__FILE__)."/data";
$dsturl  = "http".($_SERVER["SERVER_PORT"]!=443?"":"s")."://".
  $_SERVER["SERVER_NAME"].
  ($_SERVER["SERVER_PORT"]!=80 && $_SERVER["SERVER_PORT"]!=443? ":".$_SERVER["SERVER_PORT"] : "").
  dirname($_SERVER["PHP_SELF"])."/data/".$archivename2;

$mirrors = array_map("trim",$mirrors);

$isinstalled = file_exists($dstpath."/isinstalled");

?>

<?php

$http["proxy_host"] = "proxyout.inist.fr";
$http["proxy_port"] = 8080;
$base_url = "http://www.phpfreechat.net/download";
$base_url = "http://puzzle.dl.sourceforge.net/sourceforge/phpfreechat";
$archivename = "phpfreechat-1.0-beta4.tar.gz";
//$archivename = "phpfreechat-0.9.3.tar.gz";
$dstpath = dirname(__FILE__)."/data";

require_once 'PHP/Compat.php';
PHP_Compat::loadFunction('file_get_contents');
PHP_Compat::loadFunction('file_put_contents');

if (!is_writable($dstpath))
  die("ERROR: ".$dstpath." is not writable!");

require_once "HTTP/Request.php";
$req =& new HTTP_Request($base_url."/".$archivename, $http);
if (!PEAR::isError($req->sendRequest()))
{
  $archivecontent = $req->getResponseBody();
  file_put_contents($dstpath."/".$archivename, $archivecontent);

  require_once "File/Archive.php";
  $src = $dstpath."/".$archivename."/";
  $dest = $dstpath;
  File_Archive::extract( $src, $dest );
}

?>
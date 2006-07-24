<?php

require_once 'PHP/Compat.php';
PHP_Compat::loadFunction('file_get_contents');
PHP_Compat::loadFunction('file_put_contents');

$archivename = "phpfreechat-0.1.tar.gz";
$dstpath = dirname(__FILE__)."/data";
if (!is_writable($dstpath))
  die("ERROR: ".$dstpath." is not writable!");

require_once "HTTP/Request.php";
$req =& new HTTP_Request("http://www.phpfreechat.net/download/".$archivename);
if (!PEAR::isError($req->sendRequest()))
{
  $archivecontent = $req->getResponseBody();
  file_put_contents($dstpath."/".$archivename, $archivecontent);

  require_once "File/Archive.php";
  @File_Archive::extract( $src = $dstpath."/".$archivename."/",
                          $dest = $dstpath );
}

?>
<?php require_once dirname(__FILE__)."/config.php"; ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html>
  <head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <title>pfcSetup - Step 2/2</title>
  </head>
  <body>
<h1>pfcSetup - Step 2/2</h1>

<?php if (!$isinstalled) { ?>
<p>Installation de <?php echo $archivename; ?> </p>
<?php } else { ?>
<p>phpfreechat (<?php echo $archivename2; ?>) is already installed <a href="./data/<?php echo $archivename2; ?>">here</a>.</p>
<?php } ?>

<?php
if (!$isinstalled)
{
  // delete the old archive
  @unlink($dstpath."/".$archivename);
  
  $param = array();
  if (isset($_GET["host"])) $param["proxy_host"] = $_GET["host"];
  if (isset($_GET["port"])) $param["proxy_port"] = $_GET["port"];
  require_once "HTTP/Request.php";
  
  $nextmirror = false;
  $unziped    = false;
  // loop over the mirrors
  for($i = 0; $i<count($mirrors) && !$unziped; $i++)
  {
    $copied = false;
    if (preg_match("/^\//",$mirrors[$i]))
    {
      // local file
      if (!file_exists($mirrors[$i]."/".$archivename))
        $errors[$mirrors[$i]][] = "Local file not found";
      else
      {
        @copy($mirrors[$i]."/".$archivename,$dstpath."/".$archivename);
        $copied = true;
      }
    }
    else
    {
      // remote file
      $req =& new HTTP_Request($mirrors[$i]."/".$archivename, $param);
      if (!PEAR::isError($req->sendRequest()))
      {
        $archivecontent = $req->getResponseBody();
        file_put_contents($dstpath."/".$archivename, $archivecontent);
      }
    }
    if (file_exists($dstpath."/".$archivename)) $copied = true;

    // the archive has been copied (but maybe it just contains a html error page!)
    if ($copied)
    {
      require_once "File/Archive.php";
      $src = $dstpath."/".$archivename."/";
      $dest = $dstpath;
      $res = @File_Archive::extract( $src, $dest );
      if (PEAR::isError($res))
        $errors[$mirrors[$i]][] = $res->getMessage();
      else
        $unziped = true;
    }
  }
  
  if ($unziped)
  {
    echo "<p>Done!</p>";
    echo "<p>Click <a href=\"./data/".$archivename2."\">here</a> to visit your chat!</p>";
    @touch($dstpath."/isinstalled");
  }
  else
  {
    echo "<p>Error!</p>";
    echo "<pre>"; print_r($errors); echo "</pre>";
    // delete the wrong archive
    @unlink($dstpath."/".$archivename);
  }
}
?>

  </body>
</html>
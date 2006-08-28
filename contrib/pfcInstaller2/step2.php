<?php require_once dirname(__FILE__)."/config.php"; ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html>
  <head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <title>pfcInstaller - Step 2/2</title>
  </head>
  <body>
<h1>pfcInstaller - Step 2/2</h1>

<?php if (!$isinstalled) { ?>
<p>Installation de <?php echo $archivename; ?> </p>
<?php } else { ?>
<p>phpfreechat (<?php echo $archivename2; ?>) is allready installed <a href="./data/<?php echo $archivename2; ?>">here</a>.</p>
<?php } ?>

<?php
if (!$isinstalled)
{
  $param = array();
  if (isset($_GET["host"])) $http["proxy_host"] = $_GET["host"];
  if (isset($_GET["port"])) $http["proxy_port"] = $_GET["port"];
  require_once "HTTP/Request.php";
  
  $nextmirror = false;
  $unziped    = false;
  // loop over the mirrors
  for($i = 0; $i<count($mirrors) && !$unziped; $i++)
  {
    $req =& new HTTP_Request($mirrors[$i]."/".$archivename, $param);
    if (!PEAR::isError($req->sendRequest()))
    {    
      $archivecontent = $req->getResponseBody();
      file_put_contents($dstpath."/".$archivename, $archivecontent);
      
      require_once "File/Archive.php";
      $src = $dstpath."/".$archivename."/";
      $dest = $dstpath;
      $res = @File_Archive::extract( $src, $dest );
      if (PEAR::isError($res))
        $errors[$mirrors[$i]] = array($res->getMessage());
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
    print_r($errors);
  }
}
?>

  </body>
</html>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
  <head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <title>phpFreeChat demo</title>
  </head>

  <body>

<p>
Let us
<a href=""
   onclick="window.open('demo65_chat_popup.php','chat_popup','toolbar=0,menubar=0,scrollbars=1,width=800,height=650'); return false;">
start chatting
</a>
</p>

<?php if (isset($_GET['profil'])) { ?>
  <p>Here is the user (id=<?php echo $_GET['profil']; ?>)profil</p>
<?php } ?>

<?php /* start hide */ ?>
<?php
  echo "<h2>The source code</h2>";
  $filename = __FILE__;
  echo "<p><code>".$filename."</code></p>";
  echo "<pre style=\"margin: 0 50px 0 50px; padding: 10px; background-color: #DDD;\">";
  $content = file_get_contents($filename);
  $content = preg_replace('/\<\?php \/\* start hide \*\/ \?\>.*?\<\?php \/\* end hide \*\/ \?\>/s','',$content);
  highlight_string($content);
  echo "</pre>";
?>

<?php
  echo "<h2>The chat popup source code</h2>";
  $filename = 'demo65_chat_popup.php';
  echo "<p><code>".$filename."</code></p>";
  echo "<pre style=\"margin: 0 50px 0 50px; padding: 10px; background-color: #DDD;\">";
  $content = file_get_contents($filename);
  highlight_string($content);
  echo "</pre>";
?>
<?php /* end hide */ ?>

  </body>
</html>
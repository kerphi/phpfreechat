<?php

require_once dirname(__FILE__)."/../src/pfcinfo.class.php";
$pfcinfo  = new pfcInfo( md5("Show last posted messages channel") );
$errors = $pfcinfo->getErrors();
if (count($errors))
{
  foreach($errors as $e)
    echo $e;
}
$lastmsg_raw = $pfcinfo->getLastMsg("channel1", 10);

echo "<h1>A demo which explains how to get the last posted messages on a given channel</h1>";

echo '<div style="margin: auto; width: 70%; border: 1px solid red; background-color: #FDD; padding: 1em;">';
$nbmsg = count($lastmsg_raw["data"]);
$info = "<strong>%d</strong> last messages on <strong>'%s'</strong> are:";
echo "<p>".sprintf($info, $nbmsg, "channel1")."</p>";

$bg = 1;
echo '<table style="margin: auto; width: 70%; border: 1px solid red; background-color: #FEE;">';
// format messages to a readable string
// be carreful ! this format will change in future
foreach($lastmsg_raw["data"] as $m)
{
  echo '<tr style="background-color:'.($bg == 1 ? "#FFE;" : "#EEF;").'">';
  echo '<td style="width:100px;">'.$m["date"].'</td>';
  echo '<td style="width:80px;color:#F55;text-align:right;font-weight:bold;">'.$m["sender"].'</td>';
  echo '<td>'.$m["param"].'</td>';
  echo "</tr>";
  if ($bg == 1)
    $bg = 2;
  else
    $bg = 1;
}
echo "</table>";
echo "</div>";

?>

<?php
  // print the current file
  echo "<h2>The source code</h2>";

  $filename = dirname(__FILE__)."/demo32_show_last_messages-config.php";
  echo "<p><code>".$filename."</code></p>";
  echo "<pre style=\"margin: 0 50px 0 50px; padding: 10px; background-color: #DDD;\">";
  $content = file_get_contents($filename);
  highlight_string($content);
  echo "</pre>";

  $filename = __FILE__;
  echo "<p><code>".$filename."</code></p>";
  echo "<pre style=\"margin: 0 50px 0 50px; padding: 10px; background-color: #DDD;\">";
  $content = file_get_contents($filename);
  highlight_string($content);
  echo "</pre>";
?>

<?php

require_once dirname(__FILE__)."/../src/pfcinfo.class.php";
$info  = new pfcInfo( md5("Show last posted messages channel") );
$lastmsg_raw = $info->getLastMsg("channel1", 10);
print_r($lastmsg_raw);

echo "<h1>A demo which explains how to get the last posted messages</h1>";

echo '<div style="margin: auto; width: 70%; border: 1px solid red; background-color: #FDD; padding: 1em;">';
$nbmsg = count($lastmsg_raw["messages"]);
$info = "<strong>%d</strong> last messages on <strong>'%s'</strong> channel are:";
echo "<p>".sprintf($info, $nbmsg, "")."</p>";

$bg = 1;
echo '<table style="margin: auto; width: 70%; border: 1px solid red; background-color: #FEE;">';
// format messages to a readable string
// be carreful ! this format will change in future
foreach($lastmsg_raw["messages"] as $m)
{
  echo '<tr style="background-color: '.($bg == 1 ? "#FFE;" : "#EEF;").'">';
  echo '<td>'.$m[2].'</td>';
  echo '<td style="color:#F75; text-align: right;">'.$m[3].'</td>';
  echo "<td>".$m[4]."</td>";
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
  echo htmlentities($content);
  echo "</pre>";

  $filename = __FILE__;
  echo "<p><code>".$filename."</code></p>";
  echo "<pre style=\"margin: 0 50px 0 50px; padding: 10px; background-color: #DDD;\">";
  $content = file_get_contents($filename);
  echo htmlentities($content);
  echo "</pre>";
?>

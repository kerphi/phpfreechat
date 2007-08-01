<?php

$filename  = dirname(__FILE__).'/data.txt';

// store new message in the file
$msg = isset($_GET['msg']) ? $_GET['msg'] : '';
if ($msg != '')
{
  $lines = file($filename);
  $lines = array_slice($lines, -5);
  $lines[] = time()."\t".$msg."\n";
  file_put_contents($filename,implode("",$lines));
  die();
}

?>
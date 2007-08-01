<?php

$filename  = dirname(__FILE__).'/data.txt';

// infinite loop until the data file is not modified
$lastmodif    = isset($_GET['id']) ? $_GET['id'] : 0;
$currentmodif = filemtime($filename);
while ($currentmodif <= $lastmodif) // check if the data file has been modified
{
  usleep(  10000); // sleep 10ms to unload the CPU
  //usleep(1010000); // sleep 1sec10ms to unload the CPU
  clearstatcache();
  $currentmodif = filemtime($filename);
}


$lines = file($filename);
$data = '';
foreach($lines as $l)
{
  $l = explode("\t",$l);
  if ($l[0] > $lastmodif) $data .= $l[1];
}

// return a json array
$response = array();
$response['data'] = $data;
$response['id'] = $currentmodif;
echo json_encode($response);
flush();

?>
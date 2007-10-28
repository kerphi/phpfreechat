<?php

$filename   = dirname(__FILE__).'/'.basename(__FILE__).'.data';
$timetowait = 3;
if (!is_writable(dirname($filename))) die($filename.' is not writable');
file_put_contents($filename,'some-data1-'.time());
clearstatcache();
$time1 = filemtime($filename);
sleep($timetowait);
file_put_contents($filename,'some-data2-'.time());
clearstatcache();
$time2 = filemtime($filename);
unlink($filename);
echo ($time2-$time1 == $timetowait) ? "filemtime test passed successfully\n" : "filemtime test failed\n";

?>
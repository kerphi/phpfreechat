<?php

require_once dirname(__FILE__)."/../../src/pfctools.php";

$uid = md5(uniqid(rand(), true));
$indexfile = dirname(__FILE__)."/indexfile_to_test/index";
$delay = 1000; // 1000 = 1 ms

$time = 0;
while(1)
{

  $time = microtime();
  if (!file_exists($indexfile)) die();

  $max = file_get_contents_flock($indexfile);

  echo "[".$uid."] max is ".$max.", dtime = ".(microtime()-$time)."\n";
  usleep($delay);
}

?>

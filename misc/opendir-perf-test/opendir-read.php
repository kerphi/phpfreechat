<?php

$uid = md5(uniqid(rand(), true));
$dir = dirname(__FILE__)."/dir_to_test";
$delay = 1000; // 1000 = 1 ms

$time = 0;
while(1)
{

  $time = microtime();
  if (!is_dir($dir)) return false;

  $list = array();
  if ($dh = opendir($dir))
  {
    while (($file = readdir($dh)) !== false)
      if ($file != '.' && $file != '..' && $file != '.svn')
        $list[] = $file;
    closedir($dh);
  }

  sort($list);
  if (count($list)>0)
    $max = $list[count($list)-1];
  else
    $max = -1;
  //foreach($list as $i)
  //  if ($max == -1 || $max < $i)
  //    $max = $i;
  echo "[".$uid."] max is ".$max.", dtime = ".(microtime()-$time)."\n";
  usleep($delay);
}

?>

<?php

$dir = dirname(__FILE__)."/dir_to_test";
$limit = 20;

while(1)
{
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
  {
    $min = $list[0];
    $max = $list[count($list)-1];
  }
  else
  {
    $max = 0;
    $min = 0;
  }
  touch($dir.'/'.($max+1));
  @unlink($dir.'/'.($max-$limit));
  echo "min = $min, max = $max\n";
  sleep(1);
}  

?>

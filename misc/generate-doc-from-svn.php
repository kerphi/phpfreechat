<?php
echo '<pre>';

$f = 'https://phpfreechat.svn.sourceforge.net/svnroot/phpfreechat/trunk/src/pfcglobalconfig.class.php';
$data = file_get_contents($f);

$offset = 0;
preg_match('/class pfcGlobalConfig/',$data,$matches, PREG_OFFSET_CAPTURE, $offset);
$offset = $matches[0][1];

if (preg_match('/\/\*\*/', $data, $matches1, PREG_OFFSET_CAPTURE, $offset))
{
  // debut de commentaire
  $offset1 = $matches1[0][1];
  print_r($matches);
}
if (preg_match('/\*\s(.*)/', $data, $matches2, PREG_OFFSET_CAPTURE, $offset))
{
  // dans le commentaire
  $offset2 = $matches2[1][1];
  print_r($matches);
}
if (preg_match('/\*\//', $data, $matches3, PREG_OFFSET_CAPTURE, $offset))
{
  // fin de commentaire
  $offset3 = $matches3[0][1];
  print_r($matches);
}
if (preg_match('/var\s+\$([a-z]+)\s+=\s+(.*);/i', $data, $matches4, PREG_OFFSET_CAPTURE, $offset))
{
  // analyse du parametre
  $offset4 = $matches4[1][1];
  print_r($matches);
}

?>
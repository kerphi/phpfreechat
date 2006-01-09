<?php

class phpXChatTools
{
  function RelativePath($p1, $p2)
  {
    $p1 = realpath($p1);
    $p2 = realpath($p2);
    $res = "";
    while( $p1 != "" && $p1 != "/" && strpos($p2, $p1) === FALSE)
    {
      $res .= "../";
      $p1 = dirname($p1);
    }
    $p2 = substr($p2, strlen($p1)+1, strlen($p2)-strlen($p1));
    $res .= $p2;
    return $res;
  }
}

?>
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

  function RecursiveMkdir($path)
  {
    // This function creates the specified directory using mkdir().  Note
    // that the recursive feature on mkdir() is broken with PHP 5.0.4 for
    // Windows, so I have to do the recursion myself.
    if (!file_exists($path))
    {
      // The directory doesn't exist.  Recurse, passing in the parent
      // directory so that it gets created.
      phpXChatTools::RecursiveMkdir(dirname($path));
      mkdir($path, 0777);
    }
  }

}

?>
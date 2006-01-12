<?php
/**
 * phpfreechattools.class.php
 *
 * Copyright © 2006 Stephane Gully <stephane.gully@gmail.com>
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful, 
 * but WITHOUT ANY WARRANTY; without even the implied warranty of 
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details. 
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the
 * Free Software Foundation, 51 Franklin St, Fifth Floor,
 * Boston, MA  02110-1301  USA
 */

require_once dirname(__FILE__)."/phpfreechatcontainer.class.php";

/**
 * phpFreeChatTools is a toolbox containing misc. functions
 *
 * @author Stephane Gully <stephane.gully@gmail.com>
 */
class phpFreeChatTools
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
      phpFreeChatTools::RecursiveMkdir(dirname($path));
      mkdir($path, 0777);
    }
  }

}

?>
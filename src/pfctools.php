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

/**
 * this file contains a toolbox with misc. usefull functions
 *
 * @author Stephane Gully <stephane.gully@gmail.com>
 */

// To be sure the directory separator is defined
// I don't know if this constant can be undefined or not so maybe this code is not necessary
if (!defined("DIRECTORY_SEPARATOR"))
  define("DIRECTORY_SEPARATOR", "/");


/**
 * Returns the absolute script filename
 * takes care of php cgi configuration which do not support SCRIPT_FILENAME variable.
 */
function getScriptFilename()
{
  $sf = isset($_SERVER["PATH_TRANSLATED"]) ? $_SERVER["PATH_TRANSLATED"] : ""; // check for a cgi configurations
  if ( $sf == "" ||
       !file_exists($sf))
    $sf = isset($_SERVER["SCRIPT_FILENAME"]) ? $_SERVER["SCRIPT_FILENAME"] : ""; // for 'normal' configurations
  if ( $sf == "" ||
       !file_exists($sf))
  {
    echo "<pre>";
    echo "<span style='color:red'>Error: GetScriptFilename function returns a wrong path. Please contact the pfc team (contact@phpfreechat.net) and copy/paste this array to help debugging.</span>\n";
    print_r($_SERVER);
    echo "</pre>";
    exit;
  }
  return $sf;
}

function relativePath($p1, $p2)
{
  if (is_file($p1)) $p1 = dirname($p1);
  if (is_file($p2)) $p2 = dirname($p2);
  // using realpath function is necessary to resolve symbolic links
  $p1 = realpath(cleanPath($p1));
  $p2 = realpath(cleanPath($p2));
  $res = "";
  //echo $p1."<br>";
  //echo $p2."<br>";
  while( $p1 != "" && $p1 != "/" && strpos($p2, $p1) === FALSE)
  {
    $res .= "../";
    $p1 = dirname($p1);
  }
  $p2 = (isset($_SERVER["WINDIR"]) || isset($_SERVER["windir"])) ?
    str_replace("\\","/",substr($p2, strlen($p1)+1, strlen($p2)-strlen($p1))) :
    substr($p2, strlen($p1)+1, strlen($p2)-strlen($p1));
  $res .= $p2;
  // remove the last "/"
  if (preg_match("/.*\/$/", $res)) $res = preg_replace("/(.*)\//","$1",$res);
  // if rootpath is empty replace it by "." to avoide url starting with "/"
  if ($res == "") $res = ".";
  return $res;
}  

function cleanPath($path)
{
  $result = array();
  $pathA = explode(DIRECTORY_SEPARATOR, $path);
  if (!$pathA[0])
    $result[] = '';
  foreach ($pathA AS $key => $dir) {
    if ($dir == '..') {
      if (end($result) == '..') {
        $result[] = '..';
      } elseif (!array_pop($result)) {
        $result[] = '..';
      }
    } elseif ($dir && $dir != '.') {
      $result[] = $dir;
    }
  }
  if (!end($pathA))
    $result[] = '';
  return implode('/', $result);
}


function mkdir_r($path, $mode = 0777)
{
  // This function creates the specified directory using mkdir().  Note
  // that the recursive feature on mkdir() is broken with PHP 5.0.4 for
  // Windows, so I have to do the recursion myself.
  if (!file_exists($path))
  {
    // The directory doesn't exist.  Recurse, passing in the parent
    // directory so that it gets created.
    mkdir_r(dirname($path), $mode);
    mkdir($path, $mode);
  }
}


/**
 * Copy a file, or recursively copy a folder and its contents
 *
 * @author      Aidan Lister <aidan@php.net>
 * @version     1.0.1
 * @link        http://aidanlister.com/repos/v/function.copyr.php
 * @param       string   $source    Source path
 * @param       string   $dest      Destination path
 * @return      bool     Returns TRUE on success, FALSE on failure
 */
function copyr($source, $dest, $mode = 0777)
{ 
  // Simple copy for a file
  if (is_file($source)) {
    $ret = copy($source, $dest);
    @chmod($dest, $mode);
    return $ret;
  }

  // Make destination directory
  if (!is_dir($dest)) {
    mkdir($dest, $mode);
  }

  // Take the directories entries
  $dir = dir($source);
  $entries = array();
  while (false !== $entry = $dir->read())
  {
    $entries[] = $entry;
  }
  
  // Loop through the folder
  foreach ($entries as $e)
  {
    // Skip pointers
    if ($e == '.' || $e == '..') continue;
    // Deep copy directories
    if ($dest !== $source . DIRECTORY_SEPARATOR . $e)
      copyr($source . DIRECTORY_SEPARATOR . $e, $dest . DIRECTORY_SEPARATOR . $e, $mode);
  }
  
  // Clean up
  $dir->close();
  return true;
}

/**
 * file_get_contents
 * define an alternative file_get_contents when this function doesn't exists on the used php version (<4.3.0)
 */
if (!function_exists('file_get_contents'))
{
  function file_get_contents($filename, $incpath = false, $resource_context = null)
    {
      if (false === $fh = fopen($filename, 'rb', $incpath))
      {
        trigger_error('file_get_contents() failed to open stream: No such file or directory', E_USER_WARNING);
        return false;
      }
      clearstatcache();
      if ($fsize = filesize($filename))
      {
        $data = fread($fh, $fsize);
      }
      else
      {
        while (!feof($fh)) {
          $data .= fread($fh, 8192);
        }
      }
      fclose($fh);
      return $data;
    }
}

/**
 * iconv
 * define an alternative iconv when this function doesn't exists on the php modules
 */
if (!function_exists('iconv'))
{
  if(function_exists('libiconv'))
  {
    // use libiconv if it exists
    function iconv($input_encoding, $output_encoding, $string)
    {
      return libiconv($input_encoding, $output_encoding, $string);
    }
  }
  else
  {
    // fallback if nothing has been found
    function iconv($input_encoding, $output_encoding, $string)
    {
      return $string;
    }
  }
}    

?>

<?php


$method = 2;
$msgid_filename = '/tmp/readwrite';

$uid = md5(uniqid(rand(), true));
$msgids = array();
$msgid = 0;
$oldmsgid = 0;
for($c = 0; $c < 2000; $c++)
{
  // improved method to read and increment a msgid
  if ($method == 1)
  {
    clearstatcache();
    if (file_exists($msgid_filename))
    {
      $fh = fopen($msgid_filename, 'r+');
      for($i = 0; $i < 10; $i++)  // Try 10 times until an exclusive lock can be obtained
      {
        if (flock($fh, LOCK_EX))
        {
          $msgid = chop(fread($fh, filesize($msgid_filename)));
          $msgid++;
          rewind($fh);
          fwrite($fh, $msgid);
          fflush($fh);
          ftruncate($fh, ftell($fh));
          flock($fh, LOCK_UN);
          break;
        }
        // If flock is working properly, this will never be reached
        $delay = rand(0, pow(2, ($i+1)) - 1) * 5000;  // Exponential backoff
        usleep($delay);  
      }
      fclose($fh);
    }
    else 
    {
      $msgid="1";
      file_put_contents($msgid_filename, $msgid, LOCK_EX);
    }
  } 


  // method used in pfc to request a new msgid
  if ($method == 2)
  {
    $msgid = file_get_contents_flock($msgid_filename);
    $msgid++;
    file_put_contents($msgid_filename, $msgid, LOCK_EX);
  }

  $pause = rand(0,1000);
  usleep($pause); // a very small pause
  $msgids[] = $msgid;
  echo sprintf("uid=%s pause=%4s delta=%d msgid=%d\n",$uid,$pause,($msgid-$oldmsgid),trim($msgid));
  $oldmsgid = $msgid;
}
file_put_contents($msgid_filename."_".$uid, implode("\n",$msgids), LOCK_EX);


/**
 * file_get_contents_flock
 * define an alternative file_get_contents when this function doesn't exists on the used php version (<4.3.0)
 */

if (!defined('LOCK_SH')) {
    define('LOCK_SH', 1);
}

function file_get_contents_flock($filename, $incpath = false, $resource_context = null)
{
    if (false === $fh = fopen($filename, 'rb', $incpath)) {
        user_error('file_get_contents() failed to open stream: No such file or directory ['.$filename.']',
            E_USER_WARNING);
        return false;
    }

    // Attempt to get a shared (read) lock
    if (!$flock = flock($fh, LOCK_SH)) {
      return false;
    }

    clearstatcache();
    if ($fsize = filesize($filename)) {
        $data = fread($fh, $fsize);
    } else {
        $data = '';
        while (!feof($fh)) {
            $data .= fread($fh, 8192);
        }
    }

    fclose($fh);
    return $data;
}

/**
 * Replace file_put_contents()
 *
 * @category    PHP
 * @package     PHP_Compat
 * @link        http://php.net/function.file_put_contents
 * @author      Aidan Lister <aidan@php.net>
 * @internal    resource_context is not supported
 * @since       PHP 5
 * @require     PHP 4.0.0 (user_error)
 */
if (!defined('FILE_USE_INCLUDE_PATH')) {
  define('FILE_USE_INCLUDE_PATH', 1);
}

if (!defined('LOCK_EX')) {
  define('LOCK_EX', 2);
}

if (!defined('FILE_APPEND')) {
  define('FILE_APPEND', 8);
}
if (!function_exists('file_put_contents')) {
  function file_put_contents($filename, $content, $flags = null, $resource_context = null)
    {
      // If $content is an array, convert it to a string
      if (is_array($content)) {
        $content = implode('', $content);
      }

      // If we don't have a string, throw an error
      if (!is_scalar($content)) {
        user_error('file_put_contents() The 2nd parameter should be either a string or an array ['.$filename.']',
                   E_USER_WARNING);
        return false;
      }

      // Get the length of data to write
      $length = strlen($content);

      // Check what mode we are using
      $mode = ($flags & FILE_APPEND) ?
        'a' :
        'wb';

      // Check if we're using the include path
      $use_inc_path = ($flags & FILE_USE_INCLUDE_PATH) ?
        true :
        false;

      // Open the file for writing
      if (($fh = @fopen($filename, $mode, $use_inc_path)) === false) {
        user_error('file_put_contents() failed to open stream: Permission denied ['.$filename.']',
                   E_USER_WARNING);
        return false;
      }

      // Attempt to get an exclusive lock
      $use_lock = ($flags & LOCK_EX) ? true : false ;
      if ($use_lock === true) {
        if (!flock($fh, LOCK_EX)) {
          return false;
        }
      }

      // Write to the file
      $bytes = 0;
      if (($bytes = @fwrite($fh, $content)) === false) {
        $errormsg = sprintf('file_put_contents() Failed to write %d bytes to %s ['.$filename.']',
                            $length,
                            $filename);
        user_error($errormsg, E_USER_WARNING);
        return false;
      }

      // Close the handle
      @fclose($fh);

      // Check all the data was written
      if ($bytes != $length) {
        $errormsg = sprintf('file_put_contents() Only %d of %d bytes written, possibly out of free disk space. ['.$filename.']',
                            $bytes,
                            $length);
        user_error($errormsg, E_USER_WARNING);
        return false;
      }

      // Return length
      return $bytes;
    }
}


?>

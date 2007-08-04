<?php

$method = 2;
$msgid_filename = '/tmp/readwrite';

$uid = md5(uniqid(rand(), true));
$msgid = 0;
while(1)
{
  // improved method to request a msgid
  if ($method == 1)
  {
    clearstatcache();
    if (file_exists($msgid_filename)) {
      $fh = fopen($msgid_filename, 'r+');
      while(1) {
        if (flock($fh, LOCK_EX)) {
          $msgid = chop(fread($fh, filesize($msgid_filename)));
          $msgid++;
          rewind($fh);
          fwrite($fh, $msgid);
          fflush($fh);
          ftruncate($fh, ftell($fh));
          flock($fh, LOCK_UN);
          break;
        }
      }
    }
    else {
      $fh = fopen($msgid_filename, 'w+');
      fwrite($fh, "1");
      $msgid="1";
    }
    fclose($fh);
  } 


  // method used in pfc to request a new msgid
  if ($method == 2)
  {
    $msgid = file_get_contents_flock($msgid_filename);
    $msgid++;
    file_put_contents($msgid_filename, $msgid, LOCK_EX);
  }

  echo "uid=".$uid." ".trim($msgid)."\n";
  usleep(rand(0,1000)); // a very small pause
}


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

?>

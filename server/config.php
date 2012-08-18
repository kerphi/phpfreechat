<?php


// This is the time of inactivity to wait before considering a user is disconnected (in milliseconds).
// A user is inactive only if s/he closed his/her chat window. A user with an open chat window is not 
// inactive because s/he sends each refresh_delay an HTTP request.
// (Default value: 35 seconds)
$GLOBALS['pfc_timeout'] = 35;

// include the local config if defined
$clocal = __DIR__.'/config.local.php';
if (file_exists($clocal)) {
  include $clocal;
}
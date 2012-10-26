<?php

// phpfreechat version
$GLOBALS['pfc_version'] = '2.0.4';

// This is the time of inactivity to wait before considering a user is disconnected (in milliseconds).
// A user is inactive only if s/he closed his/her chat window. A user with an open chat window is not 
// inactive because s/he sends each refresh_delay an HTTP request.
// (Default value: 35 seconds)
$GLOBALS['pfc_timeout'] = 35;


// custom user hooks
$GLOBALS['pfc_hooks'] = array();

// HOOK - pfc.before.auth
// Can be used to automaticaly login to the chat 
// with your own auth system (forum, ldap, database, sso ...)
// example:
// $GLOBALS['pfc_hooks']['pfc.before.auth'][5] = function ($app, $req, $res) {
//   return function ($hr) use ($app, $req, $res) {
//     $hr->login = 'kerphi'; // TODO: replace this code with a real example
//   };
// };

// include the local config if defined
$clocal = dirname(__FILE__).'/config.local.php';
if (file_exists($clocal)) {
  include $clocal;
}
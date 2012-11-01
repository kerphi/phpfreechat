<?php

// phpfreechat version
$GLOBALS['pfc_version'] = '2.0.5';

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
//   return function () use ($app, $req, $res) {
//     return 'kerphi'; // TODO: replace this code with a real example
//   };
// };

// HOOK - pfc.filter.login
// Can be used to filter forbidden characters from the login string
// example:
// $GLOBALS['pfc_hooks']['pfc.filter.login'][5] = function ($app, $req, $res) {
//   return function ($login) use ($app, $req, $res) {
//     $ascii_pattern = '/[^a-z0-9()\/\'"|&,. -]/i';
//     return preg_replace($ascii_pattern, '', $login);
//   };
// };

// include the local config if defined
$clocal = dirname(__FILE__).'/config.local.php';
if (file_exists($clocal)) {
  include $clocal;
}
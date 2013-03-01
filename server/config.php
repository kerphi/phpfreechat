<?php

// phpfreechat version
$GLOBALS['pfc_version'] = '2.1.1';

// This is the time of inactivity to wait before considering a user is disconnected (in milliseconds).
// A user is inactive only if s/he closed his/her chat window. A user with an open chat window is not 
// inactive because s/he sends each refresh_delay an HTTP request.
// (Default value: 35 seconds)
$GLOBALS['pfc_timeout'] = 35;

// Gives channel operator rights to the first connected user
// (Default value: true)
$GLOBALS['first_is_op'] = true;

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

// HOOK - pfc.isop
// Can be used to give operator rights to users
// example:
// $GLOBALS['pfc_hooks']['pfc.isop'][5] = function ($app, $req, $res) {
//   return function ($login, $channel, $uid, $cid) use ($app, $req, $res) {
//     if ($login == 'kerphi' and $channel == "Kerphi's room") {
//       return true;
//     } else {
//       return false;
//     }
//   };
// };

// HOOK - pfc.isban
// Can be used to banish user on one or several channels
// example:
// $GLOBALS['pfc_hooks']['pfc.isban'][5] = function ($app, $req, $res) {
//   return function ($login, $channel, $uid, $cid) use ($app, $req, $res) {
//     if ($login == 'baduser' and $channel == "Kerphi's room") {
//       return array('opname' => 'Chat Master', 'reason' => 'Because you are a spammer', 'timestamp' => time());
//     } else {
//       return false;
//     }
//   };
// };

// include the local config if defined
$clocal = dirname(__FILE__).'/config.local.php';
if (file_exists($clocal)) {
  include $clocal;
}
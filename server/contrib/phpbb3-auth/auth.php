<?php
/**
 * Basic script used for phpbb3 ticket based authentication
 * see also pfc-hook.php for a call example
 * This script can be called two ways:
 * auth.php?cmd=login&service=http://xxxx/  -> to login and redirect a ticket to the service
 * auth.php?cmd=serviceValidate&ticket=tttt -> to get the login corresponding to a ticket
 */

// the phpbb3 forum path
define('IN_PHPBB', true);
$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : dirname(__FILE__).'/../../../../../forum/';
$phpEx           = substr(strrchr(__FILE__, '.'), 1);

// prepare the tickets stuff
$GLOBALS['tickets_expiration'] = 5; // in seconds
$GLOBALS['tickets_dir']        = dirname(__FILE__).'/tickets';
cleanupExpiredTickets();

$cmd = isset($_REQUEST['cmd']) ? strtolower($_REQUEST['cmd']) : 'login';
if ($cmd == 'login') {
  // generate a ticket and associate the current login

  // get the current phpbb3 login
  include($phpbb_root_path . 'common.' . $phpEx);
  $user->session_begin();
  $login = $user->data['username'];
  if ($login == 'Anonymous') $login = '';
  
  // generate a new ticket
  $ticket = md5(uniqid('', true)).'_'.time();
  file_put_contents($tickets_dir.'/'.$ticket, $login);
  
  // send back the ticket to the service
  $service = isset($_REQUEST['service']) ? $_REQUEST['service'] : '';
  $service .= '?ticket='.$ticket;
  header('Location: '.$service);
  
} else if ($cmd == 'servicevalidate') {
  // validate a ticket and return the corresponding login
  $ticket = isset($_REQUEST['ticket']) ? $_REQUEST['ticket'] : '';
  if (!file_exists($GLOBALS['tickets_dir'].'/'.$ticket)) {
    header("HTTP/1.1 404");
  } else {
    $login = file_get_contents($GLOBALS['tickets_dir'].'/'.$ticket);
    header("HTTP/1.1 200");
    header('Content-Type: text/plain; charset=utf-8');    
    echo $login;
  }
  
}

/**
 * Remove expired tickets from the directory
 */
function cleanupExpiredTickets() {
  $tdir = $GLOBALS['tickets_dir'];
  // create the ticket directory if necessary
  if (!is_dir($tdir)) {
    mkdir($tdir);
    file_put_contents($tdir.'/.htaccess', "order deny,allow\ndeny from all");
  }
  // loop on the tickets and remove expired ones
  if ($handle = opendir($tdir)) {
    while (false !== ($entry = readdir($handle))) {
      if ($entry != "." && $entry != ".." && $entry != ".htaccess") {
        $e = explode('_', $entry);
        $t = (integer)$e[1];
        if ($t < time() - $GLOBALS['tickets_expiration']) {
          unlink($tdir.'/'.$entry);
        }
      }
    }
    closedir($handle);
  }
}

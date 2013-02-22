<?php
/**
 * Script in charge of checking server configuration and returns a status
 */

$status = array();

// check that server/data/ folder is writable
if (!is_writable(dirname(__FILE__).'/server/data/')) {
  $status[] = basename(dirname(__FILE__)).'/server/data/ folder is not writable by your Web server. Please adjust folder permissions to 777.';
}

// check that php version dependency is respected
if (version_compare(PHP_VERSION, '5.3.0', '<')) {
  $status[] = 'Your php version '.PHP_VERSION.' must be >= 5.3.0';
}

// check that Slim framework is installed
if (!file_exists(dirname(__FILE__).'/server/lib/Slim/Slim/Slim.php')) {
  $status[] = 'Slim framework is not installed.<br/> It should be installed here: '.basename(dirname(__FILE__)).'/server/lib/Slim/<br/>Please run "make setup" to install it or download/unzip it yourself.';
}

if (!function_exists('microtime')) {
  $status[] = 'microtime PHP function is not enable. phpFreeChat needs it';
}

header("HTTP/1.1 200");
header('Content-Type: application/json; charset=utf-8');
echo json_encode($status);

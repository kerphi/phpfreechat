<?php

function macallback($pfccomet) {
  return $pfccomet->formatResponse('test macallback');
}

require_once 'pfccomet.class.php';
$pfccomet = new pfcComet();
$pfccomet->pfccometjs_url = './pfccomet.js';
$pfccomet->prototypejs_url = '../../../data/public/js/prototype.js';
$pfccomet->backend_url = './tester.php';
$pfccomet->backend_callback = 'macallback';
$pfccomet->run();

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <title>pfcComet tester</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <?php $pfccomet->printJavascript(); ?>
  </head>
  <body>
tester
  </body>
</html>
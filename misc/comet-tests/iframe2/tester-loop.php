<?php

function macallback($pfccomet) {
  return $pfccomet->formatResponse(time());
}

require_once 'pfccomet.class.php';
$pfccomet = new pfcComet();
$pfccomet->pfccometjs_url = './pfccomet.js';
$pfccomet->prototypejs_url = '../../../data/public/js/prototype.js';
$pfccomet->backend_loop = true;
$pfccomet->backend_url = './tester-loop.php';
$pfccomet->backend_callback    = 'macallback';
$pfccomet->onresponse_callback = 'update_servertime_area';
$pfccomet->run();

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <title>pfcComet tester</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

<script type="text/javascript">
function update_servertime_area(comet,time)
{
  document.getElementById('date').innerHTML = time;
}
</script>

<?php $pfccomet->printJavascript(); ?>
    

  </head>
  <body>

  <div id="date">here will be displayed the server time</div>

  </body>
</html>
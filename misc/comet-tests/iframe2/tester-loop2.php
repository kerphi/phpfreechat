<?php

function macallback($pfccomet) {
    return array(time(),'blabla');
}

require_once 'pfccomet.class.php';
$pfccomet = new pfcComet();
$pfccomet->backend_loop        = true;
$pfccomet->backend_loop_sleep  = 500000; // 100000 microsec = 100 milisec
$pfccomet->backend_url         = './'.basename(__FILE__);
$pfccomet->backend_callback    = 'macallback';
$pfccomet->onresponse_callback = 'update_servertime_area';

$pfccomet->run();

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <title>pfcComet tester</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />


    <script type="text/javascript" src="../../../data/public/js/prototype.js"></script>
    <script type="text/javascript" src="./pfccomet.js"></script>

<script type="text/javascript">

Event.observe(window, "load", function() {
  pfccomet = new pfcComet({"url":"./<?php echo $pfccomet->backend_url; ?>?<?php echo $pfccomet->backend_url_flag; ?>"});
  pfccomet.onResponse = function(comet,data) {
    document.getElementById('date').innerHTML = data;
  };
  pfccomet.connect();
});

</script>
    

  </head>
  <body>

  <div id="date">here will be displayed the server time</div>
  <input type="button" value="Disconnect" onclick="pfccomet.disconnect()" />
  <input type="button" value="Connect" onclick="pfccomet.connect()" />

  </body>
</html>
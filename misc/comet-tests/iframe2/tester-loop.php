<?php

function macallback($pfccomet) {
  static $id;
  if (!isset($id)) $id = md5(uniqid(rand(), true));
  file_put_contents('/tmp/cometdebug',"id=".$id." ".time()."\n",FILE_APPEND|LOCK_EX);
  return time();
}

require_once 'pfccomet.class.php';
$params = array();
$params['pfccometjs_url']      = './pfccomet.js';
$params['prototypejs_url']     = '../../../data/public/js/prototype.js';
$params['instance_name']       = 'mypfccomet';
$params['backend_loop']        = true;
$params['backend_loop_sleep']  = 500000; // 100000 microsec = 100 milisec
$params['backend_url']         = './'.basename(__FILE__);
$params['backend_callback']    = 'macallback';
$params['onresponse_callback'] = 'update_servertime_area';
$pfccomet = new pfcComet($params);
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
  <input type="button" value="Disconnect" onclick="<?php echo $params['instance_name']; ?>.disconnect()" />
  <input type="button" value="Connect" onclick="<?php echo $params['instance_name']; ?>.connect()" />

  </body>
</html>
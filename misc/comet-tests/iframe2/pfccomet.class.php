<?php

class pfcComet {
  var $pfccometjs_url   = './pfccomet.js';
  var $prototypejs_url  = './prototype.js';
  var $backend_url      = '';
  var $backend_param    = 'backend';
  var $backend_callback = null;
  var $backend_loop       = false;
  var $backend_loop_sleep = 1;
  
  function pfcComet()
  {
    if ($this->backend_url == '')
      $this->backend_url = $_SERVER['PHP_SELF'];
  }

  function run()
  {
    if (isset($_REQUEST[$this->backend_param]))
    {
      header("Cache-Control: no-cache, must-revalidate");
      header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
      flush();
      echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
  <title>pfcComet backend iframe</title>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
</head>
<script type="text/javascript">
  // KHTML browser don\'t share javascripts between iframes
  var is_khtml = navigator.appName.match("Konqueror") || navigator.appVersion.match("KHTML");
  if (is_khtml)
  {
    var prototypejs = document.createElement("script");
    prototypejs.setAttribute("type","text/javascript");
    prototypejs.setAttribute("src","'.$this->prototypejs_url.'");
    var head = document.getElementsByTagName("head");
    head[0].appendChild(prototypejs);
  }
  // load the comet object
  var pfccomet = window.parent.pfccomet;
</script>';
      flush();

      // trigger the onConnect callback
      echo '<script type="text/javascript">pfccomet._onConnect();</script>';
      flush();

      // trigger the backend callback
      do {
        if (is_callable($this->backend_callback))
        {
          $func = $this->backend_callback;
          if ( is_array($func) ){
            echo $func[0]->$func[1]($this);
          } else {
            echo $func($this);
          }
        }
        flush();
        sleep($this->backend_loop_sleep);
      } while($this->backend_loop);

      // trigger the onDisconnect callback
      echo '<script type="text/javascript">pfccomet._onDisconnect();</script>';
      flush();

      die();
    }
  }

  function formatResponse($data)
  {
    return '<script type="text/javascript">pfccomet._onResponse(\''.addslashes($data).'\');</script>';
  }
  
  function printJavascript($return = false)
  {
    $output  = '<script type="text/javascript" src="'.$this->prototypejs_url.'"></script>'."\n";
    $output .= '<script type="text/javascript" src="'.$this->pfccometjs_url.'"></script>'."\n";
    $output .= '<script type="text/javascript">
Event.observe(window, "load", function() {
  pfccomet = new pfcComet({"url":"'.$this->backend_url.'?'.$this->backend_param.'"});
  pfccomet.onConnect     = function(comet) { alert("connected"); };
  pfccomet.onDisconnect  = function(comet) { alert("disconnected"); };
  pfccomet.onResponse    = function(comet,data) { alert("response:"+data); };
  pfccomet.connect();
});
</script>'."\n";
    if ($return)
      return $output;
    else
      echo $output;
  }

}

?>
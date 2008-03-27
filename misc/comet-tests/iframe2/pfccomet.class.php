<?php

class pfcComet {
    private $pfccometjs_url        = './pfccomet.js';
    private $prototypejs_url       = './prototype.js';
    private $instance_name         = 'pfccomet'; 
    private $backend_url           = '';
    private $backend_url_flag      = 'backend';
    private $backend_callback      = null;
    private $backend_loop          = false;
    private $backend_loop_sleep    = 1000000; // 1000000 microseconds = 1 second
    private $onresponse_callback   = null;
    private $onconnect_callback    = null;
    private $ondisconnect_callback = null;
  
    public function __construct($params = array())
    {
        foreach(get_object_vars($this) as $k => $v)
        {
            if (isset($params[$k]))
                $this->$k = $params[$k];
        }
        if ($this->backend_url == '')
            $this->backend_url = $_SERVER['PHP_SELF'];
    }

    public function run()
    {
        if (isset($_REQUEST[$this->backend_url_flag]))
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
//   // KHTML browser don\'t share javascripts between iframes
//    var is_khtml = navigator.appName.match("Konqueror") || navigator.appVersion.match("KHTML");
//    if (is_khtml)
//    {
//      var prototypejs = document.createElement("script");
//      prototypejs.setAttribute("type","text/javascript");
//      prototypejs.setAttribute("src","'.$this->prototypejs_url.'");
//      var head = document.getElementsByTagName("head");
//      head[0].appendChild(prototypejs);
//    }
  // load the comet object
  var '.$this->instance_name.' = window.parent.'.$this->instance_name.';
</script>
<body>
';
            flush();

            // trigger the onConnect callback
            echo '<script type="text/javascript">'.$this->instance_name.'._onConnect();</script>'."\n";
            flush();

            // trigger the backend callback
            do {
                if (is_callable($this->backend_callback))
                {
                    $func = $this->backend_callback;
                    if ( is_array($func) ){
                        echo $this->_formatResponse($func[0]->$func[1]($this));
                    } else {
                        echo $this->_formatResponse($func($this));
                    }
                }
                flush();
                if ($this->backend_loop)  // do not sleep if the loop is finished
                    usleep($this->backend_loop_sleep);
            } while($this->backend_loop);

            // trigger the onDisconnect callback
            echo '<script type="text/javascript">'.$this->instance_name.'._onDisconnect();</script>'."\n";
            echo '</body></html>';
            flush();

            die();
        }
    }

    protected function _formatResponse($data)
    {
        return '<script type="text/javascript">'.$this->instance_name.'._onResponse('.json_encode($data).');</script>'."\n";
    }
  
    public function printJavascript($return = false)
    {
        $output  = '<script type="text/javascript" src="'.$this->prototypejs_url.'"></script>'."\n";
        $output .= '<script type="text/javascript" src="'.$this->pfccometjs_url.'"></script>'."\n";
        $output .= '<script type="text/javascript">
Event.observe(window, "load", function() {
  '.$this->instance_name.' = new pfcComet({"url":"'.$this->backend_url.'?'.$this->backend_url_flag.'"});'."\n";
        if ( $this->onresponse_callback )
            $output .= '  '.$this->instance_name.'.onResponse = '.$this->onresponse_callback.';'."\n";
        if ( $this->onconnect_callback )
            $output .= '  '.$this->instance_name.'.onConnect = '.$this->onconnect_callback.';'."\n";
        if ( $this->ondisconnect_callback )
            $output .= '  '.$this->instance_name.'.onDisconnect = '.$this->ondisconnect_callback.';'."\n";
        $output .= '
  '.$this->instance_name.'.connect();
});
</script>'."\n";
        if ($return)
            return $output;
        else
            echo $output;
    }

}

?>
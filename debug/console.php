<?php

require_once dirname(__FILE__)."/../lib/xajax_0_1_beta4/xajax.inc.php";

function getnewlog($section = "")
{
  $filename = dirname(__FILE__)."/debug".$section.".log";
  $xml_reponse = new xajaxResponse();
  if (file_exists($filename))
  {
    $fp = fopen($filename, "r");
    $html = "<pre>";
    $html .= fread($fp, filesize($filename));
    $html .= "</pre>";
    fclose($fp);
    unlink($filename);
    $xml_reponse->addAppend("debug".$section, "innerHTML", $html);
  }
  $xml_reponse->addScript("window.setTimeout('phpxchat_getnewlog(\'".$section."\')', 1000);");
  return $xml_reponse->getXML();
}
$xajax = new xajax("", "phpxchat_");
//$xajax->debugOn();
$xajax->registerFunction("getnewlog");
$xajax->processRequests();


?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN"
      "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
  <meta http-equiv="content-type" content="text/html; charset=iso-8859-1" />
  <title>phpXChat debug console</title>
  <?php $xajax->printJavascript(); ?>

  <style type="text/css">
<!--
  * { margin:0; padding:0; }
div#debug {
 position: absolute;
 top: 2px;
 left: 1%;
 overflow:auto;
 width:48.5%;
 height:49%;
 border:1px solid black;
}
div#debugsession {
 position: absolute;
 top: 2px;
 right: 1%;
 overflow:auto;
 width:48.5%;
 height:49%;
 border:1px solid black;
}
div#debugchatconfig {
 position: absolute;
 bottom: 2px;
 left: 1%;
 overflow:auto;
 width:48.5%;
 height:49%;
 border:1px solid black;
}
div#debugchat {
 position: absolute;
 bottom: 2px;
 right: 1%;
 overflow:auto;
 width:48.5%;
 height:49%;
 border:1px solid black;
}
-->
  </style>

</head>

<body>

  <div id="debug"></div>
  <script type="text/javascript"><!--
  phpxchat_getnewlog();
  --></script>

  <div id="debugsession"></div>
  <script type="text/javascript"><!--
  phpxchat_getnewlog('session');
  --></script>


  <div id="debugchatconfig"></div>
  <script type="text/javascript"><!--
  phpxchat_getnewlog('chatconfig');
  --></script>

  <div id="debugchat"></div>
  <script type="text/javascript"><!--
  phpxchat_getnewlog('chat');
  --></script>

</body>
</html>
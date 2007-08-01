<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html>
 <head>
  <meta http-equiv="content-type" content="text/html; charset=utf-8" />
  <title>pfcComet example</title>
  <script type="text/javascript" src="../../../data/public/js/prototype.js"></script>
  <script type="text/javascript" src="../pfccomet.js"></script>
 </head>
 <body>

<div id="chat">
<script type="text/javascript">
var comet = new pfcComet({'read_url': './comet-getdata.php'});
comet.onDisconnect  = function(comet) {
  $('btn_connect').show();
  $('btn_disconnect').hide();
  $('ajaxlogo').hide();
};
comet.onConnect     = function(comet) {
  $('btn_disconnect').show();
  $('btn_connect').hide();
  $('ajaxlogo').show();
};
comet.onResponse    = function(req)   { new Insertion.Top($('content'),'<p>'+req['data']+'</p>'); };
$('btn_connect').show();
$('btn_disconnect').hide();
$('ajaxlogo').hide();
</script>

<p>
  <form action="" method="get" onsubmit="new Ajax.Request('./comet-postdata.php', { method: 'get', parameters: { 'msg' : $('nick').value + ': ' + $('word').value } } ); $('word').value = ''; return false;">
    <input type="button" value="Connect"    id="btn_connect"    onclick="comet.connect()" />
    <input type="button" value="Disconnect" id="btn_disconnect" onclick="comet.disconnect()" />
    <br/>
    <input type="text" name="nick" id="nick" value="" />
    <input type="text" name="word" id="word" value="" />
    <input type="submit" name="submit" value="Send" />
  </form>
</p>

<div id="content">
</div>

<p><img id="ajaxlogo" src="http://bulletproofajax.com/code/chapter06/loading/people/loading.gif" alt="" /></p>

<script type="text/javascript">
$('btn_connect').show();
$('btn_disconnect').hide();
$('ajaxlogo').hide();
</script>

</div>

 </body>
</html>
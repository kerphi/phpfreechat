<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html>
 <head>
  <meta http-equiv="content-type" content="text/html; charset=utf-8" />
  <title>pfcComet example</title>
  <script type="text/javascript" src="../../../data/public/js/prototype.js"></script>
  <script type="text/javascript" src="../pfccomet.js"></script>
 </head>
 <body>


<script type="text/javascript">
var comet = new pfcComet({'url': './cometbackend.php', 'id': 1});
comet.onResponse = function(req) { /*alert('id:'+req['id']+' response:'+req['data']);*/ };
</script>

<p onclick="comet.connect()">login</p>
<p onclick="comet.disconnect()">logout</p>

 </body>
</html>
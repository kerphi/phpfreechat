//function alert() {}

<?php include($c->getFilePathFromTheme('templates/chat-pre.js.tpl.php')); ?>

/* create our client which will do all the work on the client side ! */
var pfc = new pfcClient();

<?php if ($connect_at_startup) { ?>
pfc.connect_disconnect();
<?php } ?>

<?php if ($debugxajax) { ?>
xajax.DebugMessage = function(text)
{
  var s = new String(text);
  text = s.escapeHTML();
  rx  = new RegExp('&lt;','g');
  text = text.replace(rx, '\n&lt;');
  $('debugxajax').innerHTML += '\n---------------\n' + text;
}
<?php } ?>

<?php include($c->getFilePathFromTheme('templates/chat-post.js.tpl.php')); ?>

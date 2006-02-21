
document.body.onunload = onunloadCallback_content;
document.getElementById('<?php echo $prefix; ?>words').onkeydown = onkeydownCallback_words;
document.getElementById('<?php echo $prefix; ?>words').onfocus = onfocusCallback_words;
document.getElementById('<?php echo $prefix; ?>handle').onkeydown = onkeydownCallback_handle;
document.getElementById('<?php echo $prefix; ?>handle').onchange = onchangeCallback_handle;
document.getElementById('<?php echo $prefix; ?>container').onmousedown = onmousedownCallback_container;
document.getElementById('<?php echo $prefix; ?>container').onmousemove = onmousemoveCallback_container;
document.getElementById('<?php echo $prefix; ?>container').onmouseup = onmouseupCallback_container;

var <?php echo $prefix; ?>isdraging = false;
function onmouseupCallback_container(e)
{
  if (!<?php echo $prefix; ?>isdraging)
  {
    var w = document.getElementById('<?php echo $prefix; ?>words');
    if (w && !<?php echo $prefix; ?>minmax_status) w.focus();
  }
}
function onmousemoveCallback_container(e)
{
  <?php echo $prefix; ?>isdraging = true;
}
function onmousedownCallback_container(e)
{
  <?php echo $prefix; ?>isdraging = false;
}

function onunloadCallback_content(e)
{
  if (!<?php echo $prefix; ?>login_status) return false;
  <?php echo $prefix; ?>handleRequest('/quit '+ <?php echo $prefix; ?>clientid );
}

function onfocusCallback_words(e)
{
  var h = document.getElementById('<?php echo $prefix; ?>handle');
  if (h && h.value == '' && !<?php echo $prefix; ?>minmax_status)
    h.focus();
}

function onkeydownCallback_words(e)
{
  if (!<?php echo $prefix; ?>login_status) return false;
  <?php echo $prefix; ?>ClearError(Array('<?php echo $prefix; ?>words'));
  if (!e) var e = window.event;
  var code = e.keyCode;
  if (code == 13) /* enter key */
  {
    var w = document.getElementById('<?php echo $prefix; ?>words');
    var wval = w.value;

    re = new RegExp("^(\/[a-z]+)( (.*)|)");
    if (wval.match(re))
    {
      /* a user command */
      wval = wval.replace(re, '$1 '+ <?php echo $prefix; ?>clientid +' $2');
      <?php echo $prefix; ?>handleRequest(wval.substr(0,<?php echo $max_text_len; ?> + <?php echo $prefix; ?>clientid.length));
    }
    else
    {
      /* a classic 'send' command*/
      <?php echo $prefix; ?>handleRequest('/send ' + <?php echo $prefix; ?>clientid + ' ' + wval.substr(0,<?php echo $max_text_len; ?>));
    }
    w.value = '';
    return false;
  }
  else if (code == 39) /* right direction */
  {
    var w = document.getElementById('<?php echo $prefix; ?>words');
    var nick_src = w.value.substring(w.value.lastIndexOf(' ')+1,w.value.length);
    if (nick_src != '')
    {
      var ul_online = document.getElementById('<?php echo $prefix; ?>online').firstChild;
      for (var i=0; i<ul_online.childNodes.length; i++)
      {
	var nick = ul_online.childNodes[i].innerHTML;
	if (nick.indexOf(nick_src) == 0)
	  w.value = w.value.replace(nick_src, nick);
      }
    }
  }
  else
  {
  }
}

var nick_changed = false;
function onkeydownCallback_handle(e)
{
  if (!<?php echo $prefix; ?>login_status) return false;
  <?php echo $prefix; ?>ClearError(Array('<?php echo $prefix; ?>handle'));
  if (!e) var e = window.event;
  var code = e.keyCode;
  if (code == 13)
  {
    nick_changed = false;
    var h = document.getElementById('<?php echo $prefix; ?>handle').value;
    <?php echo $prefix; ?>handleRequest('/nick '+ <?php echo $prefix; ?>clientid + ' ' + h.substr(0,<?php echo $max_nick_len; ?>) );
  }  
  else
    nick_changed = true;
}
function onchangeCallback_handle(e)
{
  if (!<?php echo $prefix; ?>login_status) return false;
  if (nick_changed)
  {
    nick_changed = false;
    <?php echo $prefix; ?>ClearError(Array('<?php echo $prefix; ?>handle'));
    var h = document.getElementById('<?php echo $prefix; ?>handle').value;
    <?php echo $prefix; ?>handleRequest('/nick '+ <?php echo $prefix; ?>clientid + ' ' + h.substr(0,<?php echo $max_nick_len; ?>) );
  }
}

/* preload smileys */
preloadImages(
  <?php foreach ($smileys as $s_file => $s_str) { ?>
   '<?php echo $s_file; ?>',
  <?php } ?>
  ''
);

<?php if ($connect_at_startup) { ?>
  <?php echo $prefix; ?>connect_disconnect();
<?php } ?>

<?php echo $prefix; ?>refresh_loginlogout();
<?php echo $prefix; ?>refresh_nickmarker();
<?php echo $prefix; ?>refresh_clock();
<?php echo $prefix; ?>refresh_minimize_maximize();

<div id="pfc_loader">

<div style="width:250px;background-color:#FFF;border:1px solid #000;padding:10px;position:relative;margin:auto">
  <p style="padding:0;margin:0;text-align:center;">
    <?php echo _pfc("Chat loading ..."); ?><br style="margin:0"/><?php echo _pfc("Please wait"); ?>
  </p>
</div>

<script type="text/javascript" src="<?php echo $c->data_public_url; ?>/js/compat.js"></script>
<script type="text/javascript" src="<?php echo $c->data_public_url; ?>/js/md5.js"></script>
<script type="text/javascript" src="<?php echo $c->data_public_url; ?>/js/cookie.js"></script>
<script type="text/javascript" src="<?php echo $c->data_public_url; ?>/js/image_preloader.js"></script>
<script type="text/javascript" src="<?php echo $c->data_public_url; ?>/js/myprototype.js"></script>
<script type="text/javascript" src="<?php echo $c->prototypejs_url; ?>"></script>
<script type="text/javascript" src="<?php echo $c->data_public_url; ?>/js/regex.js"></script>
<script type="text/javascript" src="<?php echo $c->data_public_url; ?>/js/utf8.js"></script>
<script type="text/javascript" src="<?php echo $c->data_public_url; ?>/js/sprintf2.js"></script>
<script type="text/javascript" src="<?php echo $c->data_public_url; ?>/js/activity.js"></script>
<script type="text/javascript" src="<?php echo $c->data_public_url; ?>/js/mousepos.js"></script>
<script type="text/javascript" src="<?php echo $c->data_public_url; ?>/js/createstylerule.js"></script>
<script type="text/javascript" src="<?php echo $c->data_public_url; ?>/js/pfcclient.js"></script>
<script type="text/javascript" src="<?php echo $c->data_public_url; ?>/js/pfcgui.js"></script>
<script type="text/javascript" src="<?php echo $c->data_public_url; ?>/js/pfcresource.js"></script>
<script type="text/javascript" src="<?php echo $c->data_public_url; ?>/js/pfcprompt.js"></script>

<script type="text/javascript">
  // <![CDATA[
<?php
require_once dirname(__FILE__).'/../../src/pfcjson.class.php';
$json = new pfcJSON();
?>
<?php $nick = $u->getNickname() != '' ? $json->encode($u->getNickname()) : $json->encode($c->nick); ?>

var pfc                       = null; // will contains a pfcClient instance
var pfc_nickname              = <?php echo ($GLOBALS["output_encoding"]=="UTF-8" ? $nick : iconv("UTF-8", $GLOBALS["output_encoding"],$nick)); ?>;
var pfc_nickid                = <?php echo $json->encode($u->nickid); ?>;
var pfc_version               = <?php echo $json->encode($version); ?>;
var pfc_clientid              = <?php echo $json->encode(md5(uniqid(rand(), true))); ?>;
var pfc_title                 = <?php echo $json->encode($title); ?>;
var pfc_refresh_delay         = <?php echo $json->encode($refresh_delay); ?>;
var pfc_refresh_delay_steps   = <?php echo $json->encode($refresh_delay_steps); ?>;
var pfc_start_minimized       = <?php echo $json->encode($start_minimized); ?>;
var pfc_nickmarker            = <?php echo $json->encode($nickmarker); ?>;
var pfc_clock                 = <?php echo $json->encode($clock); ?>;
var pfc_startwithsound        = <?php echo $json->encode($startwithsound); ?>;
var pfc_showsmileys           = <?php echo $json->encode($showsmileys); ?>;
var pfc_showwhosonline        = <?php echo $json->encode($showwhosonline); ?>;
var pfc_focus_on_connect      = <?php echo $json->encode($focus_on_connect); ?>;
var pfc_max_text_len          = <?php echo $json->encode($max_text_len); ?>;
var pfc_max_displayed_lines   = <?php echo $json->encode($max_displayed_lines); ?>;
var pfc_quit_on_closedwindow  = <?php echo $json->encode($quit_on_closedwindow); ?>;
var pfc_debug                 = <?php echo $json->encode($debug); ?>;
var pfc_btn_sh_smileys        = <?php echo $json->encode($btn_sh_smileys); ?>;
var pfc_btn_sh_whosonline     = <?php echo $json->encode($btn_sh_whosonline); ?>;
var pfc_displaytabimage       = <?php echo $json->encode($displaytabimage); ?>;
var pfc_displaytabclosebutton = <?php echo $json->encode($displaytabclosebutton); ?>;
var pfc_connect_at_startup    = <?php echo $json->encode($connect_at_startup); ?>;
var pfc_notify_window         = <?php echo $json->encode($notify_window); ?>;
var pfc_defaultchan           = <?php echo $json->encode($c->channels); ?>;
var pfc_userchan              = <?php $list = array(); foreach($u->channels as $item) {$list[] = $item["name"];} echo $json->encode($list); ?>;
var pfc_defaultprivmsg        = <?php echo $json->encode($c->privmsg); ?>;
var pfc_userprivmsg           = <?php $list = array(); foreach($u->privmsg as $item) {$list[] = $item["name"];} echo $json->encode($list); ?>;
var pfc_openlinknewwindow     = <?php echo $json->encode($openlinknewwindow); ?>;
var pfc_bbcode_color_list     = <?php $list = array(); foreach($bbcode_colorlist as $v) {$list[] = substr($v,1);} echo $json->encode($list); ?>;
var pfc_nickname_color_list   = <?php echo $json->encode($nickname_colorlist); ?>;
var pfc_theme                 = <?php echo $json->encode($theme); ?>;
var pfc_isready               = false;
var pfc_server_script_url     = <?php echo $json->encode($c->server_script_url); ?>;
var pfc_nickmeta_key_to_hide  = <?php echo $json->encode($c->nickmeta_key_to_hide); ?>;

Event.observe(window, 'load', function() {
  pfc = new pfcClient();
  if (pfc_isready) pfc.loadChat(pfc_theme);
});

<?php if ($debug) { ?>
var pfc_debug_color = true;
function trace(text) {
  var s = new String(text);
  text = s.escapeHTML();
  rx  = new RegExp('&lt;','g');
  text = text.replace(rx, '\n&lt;');
  var color = '';
  if (pfc_debug_color)
  {
    color = '#BBB';
    pfc_debug_color = false;
  }
  else
  {
    color = '#DDD';
    pfc_debug_color = true;
  }
  $('pfc_debug').innerHTML = '<p style="margin:0;border-bottom:1px solid #555;background-color:'+color+'">' + text + '</p>' + $('pfc_debug').innerHTML ;
}
<?php } ?>

  // ]]>
</script>

<div id="pfc_notloading" style="width:270px;background-color:#FFF;color:#000;border:1px solid #000;text-align:center;margin:5px auto 0 auto;font-size:10px;">
<noscript>
<p><?php echo _pfc("%s appears to be either disabled or unsupported by your browser.","JavaScript"); ?> <?php echo _pfc("This web application requires %s to work properly.","JavaScript"); ?> <?php echo _pfc("Please enable %s in your browser settings, or upgrade to a browser with %s support and try again.","JavaScript","JavaScript"); ?></p>
</noscript>
<p><script type="text/javascript">
  // <![CDATA[
if (!browserSupportsCookies())
  document.write('<?php echo _pfc("%s appears to be either disabled or unsupported by your browser.","Cookies"); ?> <?php echo _pfc("This web application requires %s to work properly.","Cookies"); ?> <?php echo _pfc("Please enable %s in your browser settings, or upgrade to a browser with %s support and try again.","Cookies","Cookies"); ?>');
else if (!browserSupportsAjax())
  document.write('<?php echo _pfc("%s appears to be either disabled or unsupported by your browser.","Ajax"); ?> <?php echo _pfc("This web application requires %s to work properly.","Ajax"); ?> <?php echo _pfc("Please upgrade to a browser with %s support and try again.","Ajax"); ?>');
else if (!ActiveXEnabledOrUnnecessary())
  document.write('<?php echo _pfc("%s appears to be either disabled or unsupported by your browser.","ActiveX"); ?> <?php echo _pfc("This web application requires %s to work properly.","Ajax"); ?> <?php echo _pfc("In Internet Explorer versions earlier than 7.0, Ajax is implemented using ActiveX. Please enable ActiveX in your browser security settings or upgrade to a browser with Ajax support and try again."); ?>');
else
{
  $('pfc_notloading').style.display = 'none';
  pfc_isready = true;
}
  // ]]>
</script></p>
<a href="http://www.phpfreechat.net" style="text-align:center"><img src="http://www.phpfreechat.net/pub/logo2_80x15.gif" alt="PHP FREE CHAT [powered by phpFreeChat-<?php echo $version ?>]" /></a>
</div> <!-- pfc_notloading -->

</div> <!-- pfc_loader -->

<div id="pfc_container"><!-- Will contains chat.html.tpl.php --></div>

<?php if ($debug) { ?>
  <div id="pfc_debug"></div>
<?php } ?>

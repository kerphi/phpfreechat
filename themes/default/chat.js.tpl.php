<script type="text/javascript">
  // <![CDATA[
<?php
require_once(dirname(__FILE__)."/../../lib/json/JSON.php");
$json = new Services_JSON();
?>
<?php $nick = $u->nick != "" ? $json->encode($u->nick) : $json->encode($c->nick); ?>

var pfc                       = null; // will contains a pfcClient instance
var pfc_nickname              = <?php echo ($GLOBALS["output_encoding"]=="UTF-8" ? $nick : iconv("UTF-8", $GLOBALS["output_encoding"],$nick)); ?>;
var pfc_nickid                = <?php echo $json->encode($u->nickid); ?>;
var pfc_version               = <?php echo $json->encode($version); ?>;
var pfc_clientid              = <?php echo $json->encode(md5(uniqid(rand(), true))); ?>;
var pfc_title                 = <?php echo $json->encode($title); ?>;
var pfc_refresh_delay         = <?php echo $json->encode($refresh_delay); ?>;
var pfc_max_refresh_delay     = <?php echo $json->encode($max_refresh_delay); ?>;
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
var pfc_defaultchan = <?php echo $json->encode($c->channels); ?>;
var pfc_userchan = <?php $list = array(); foreach($u->channels as $item) {$list[] = $item["name"];} echo $json->encode($list); ?>;
var pfc_defaultprivmsg = <?php echo $json->encode($c->privmsg); ?>;
var pfc_userprivmsg = <?php $list = array(); foreach($u->privmsg as $item) {$list[] = $item["name"];} echo $json->encode($list); ?>;
var pfc_openlinknewwindow = <?php echo $json->encode($openlinknewwindow); ?>;
var pfc_bbcode_color_list = <?php $list = array(); foreach($bbcode_colorlist as $v) {$list[] = substr($v,1);} echo $json->encode($list); ?>;
var pfc_nickname_color_list = <?php echo $json->encode($nickname_colorlist); ?>;
var pfc_proxy_url = '<?php echo $data_public_url."/".$serverid."/proxy.php"; ?>';
var pfc_theme = <?php echo $json->encode($theme); ?>;


var xajaxConfig = {
  requestURI: "<?php echo $c->server_script_url.(isset($_SERVER["QUERY_STRING"]) && $_SERVER["QUERY_STRING"] != "" ? "?".$_SERVER["QUERY_STRING"] : ""); ?>",
  debug: false,
  statusMessages: false,
  waitCursor: false,
  legacy: false
};
var xajaxLoaded=false;
function pfc_handleRequest(){return xajax.call("handleRequest", {parameters: arguments});}
function pfc_loadStyles(){return xajax.call("loadStyles", {parameters: arguments});}
function pfc_loadScripts(){return xajax.call("loadScripts", {parameters: arguments});}
function pfc_loadInterface(){return xajax.call("loadInterface", {parameters: arguments});}
function pfc_loadChat(){return xajax.call("loadChat", {parameters: arguments});}

window.onload = function () {
  pfc = new pfcClient();
  pfc_loadChat(pfc_theme);
}

<?php if ($debugxajax) { ?>
xajax.DebugMessage = function(text)
{
  var s = new String(text);
  text = s.escapeHTML();
  rx  = new RegExp('&lt;','g');
  text = text.replace(rx, '\n&lt;');
  $('pfc_debugxajax').innerHTML += '\n---------------\n' + text;
}
<?php } ?>

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


<script type="text/javascript" src="<?php echo $c->data_public_url; ?>/js/xajax.js"></script>
<script type="text/javascript" src="<?php echo $c->data_public_url; ?>/js/md5.js"></script>
<script type="text/javascript" src="<?php echo $c->data_public_url; ?>/js/cookie.js"></script>
<script type="text/javascript" src="<?php echo $c->data_public_url; ?>/js/image_preloader.js"></script>
<script type="text/javascript" src="<?php echo $c->data_public_url; ?>/js/myprototype.js"></script>
<script type="text/javascript" src="<?php echo $c->data_public_url; ?>/js/regex.js"></script>
<script type="text/javascript" src="<?php echo $c->data_public_url; ?>/js/utf8.js"></script>
<script type="text/javascript" src="<?php echo $c->data_public_url; ?>/js/sprintf2.js"></script>
<script type="text/javascript" src="<?php echo $c->data_public_url; ?>/js/activity.js"></script>
<script type="text/javascript" src="<?php echo $c->data_public_url; ?>/js/mousepos.js"></script>
<script type="text/javascript" src="<?php echo $c->data_public_url; ?>/js/createstylerule.js"></script>
<script type="text/javascript" src="<?php echo $c->data_public_url; ?>/js/pfcclient.js"></script>
<script type="text/javascript" src="<?php echo $c->data_public_url; ?>/js/pfcgui.js"></script>
<script type="text/javascript" src="<?php echo $c->data_public_url; ?>/js/pfcresource.js"></script>

<div id="pfc_container">

<div style="width:250px;background-color:#FFF;border:1px solid #000;padding:10px;position:relative;margin:auto">
  <p style="padding:0;margin:0;text-align:center;">
    <img src="http://img327.imageshack.us/img327/8071/indicatormediumgb6.gif"
         alt=""
         style="float:left;margin:0;"/>
    Chat loading ...<br style="margin:0"/>Please wait
</p>
  <img src="http://img332.imageshack.us/img332/1756/helpiv1.gif"
alt="help"
style="position:absolute;bottom:2px;right:2px;margin:0;padding:0;cursor:help"
onmouseover="document.getElementById('pfc_notloading').style.display='block';"
/>
</div>

<div id="pfc_notloading" style="width:270px;background-color:#FFF;border:1px solid #000;text-align:center;display:none;margin:5px auto 0 auto">
<p>
<?php echo _pfc("Error: the chat cannot be loaded! two possibilities: your browser doesn't support javascript or you didn't setup correctly the server directories rights - don't hesitate to ask some help on the forum"); ?> <a href="http://www.phpfreechat.net/forum/">www.phpfreechat.net/forum</a>
<a href="http://www.phpfreechat.net"><img src="http://www.phpfreechat.net/pub/logo_80x15.gif" alt="PHP FREE CHAT [powered by phpFreeChat-<?php echo $version ?>]" /></a>
</p>
</div>

</div>


<?php /* ?>
<p><?php echo _pfc("Error: the chat cannot be loaded! two possibilities: your browser doesn't support javascript or you didn't setup correctly the server directories rights - don't hesitate to ask some help on the forum"); ?> <a href="http://www.phpfreechat.net/forum/">www.phpfreechat.net/forum</a></p>
<a href="http://www.phpfreechat.net"><img src="http://www.phpfreechat.net/pub/logo_80x15.gif" alt="PHP FREE CHAT [powered by phpFreeChat-<?php echo $version ?>]" /></a>
<?php */ ?>

</div>

<?php if ($debug) { ?>
  <div id="pfc_debug"></div>
<?php } ?>
<?php if ($debugxajax) { ?>
  <div id="pfc_debugxajax"></div>
<?php } ?>
<?php $nick = $u->nick != "" ? addslashes($u->nick) : addslashes($c->nick); ?>
var pfc_nickname              = '<?php echo ($GLOBALS["output_encoding"]=="UTF-8" ? $nick : iconv("UTF-8", $GLOBALS["output_encoding"],$nick)); ?>';
var pfc_version               = '<?php echo $version; ?>';
var pfc_clientid              = '<?php echo md5(uniqid(rand(), true)); ?>';
var pfc_title                 = '<?php echo addslashes($title); ?>';
var pfc_refresh_delay         = <?php echo $refresh_delay; ?>;
var pfc_start_minimized       = <?php echo $start_minimized ? "true" : "false"; ?>;
var pfc_nickmarker            = <?php echo $nickmarker ? "true" : "false"; ?>;
var pfc_clock                 = <?php echo $clock ? "true" : "false"; ?>;
var pfc_showsmileys           = <?php echo $showsmileys ? "true" : "false"; ?>;
var pfc_showwhosonline        = <?php echo $showwhosonline ? "true" : "false"; ?>;
var pfc_focus_on_connect      = <?php echo $focus_on_connect ? "true" : "false"; ?>;
var pfc_max_text_len          = <?php echo $max_text_len; ?>;
var pfc_quit_on_closedwindow  = <?php echo $quit_on_closedwindow ? "true" : "false"; ?>;
var pfc_debug                 = <?php echo $debug ? "true" : "false"; ?>;
var pfc_btn_sh_smileys        = <?php echo $btn_sh_smileys ? "true" : "false"; ?>;
var pfc_btn_sh_whosonline     = <?php echo $btn_sh_whosonline ? "true" : "false"; ?>;
var pfc_connect_at_startup    = <?php echo $connect_at_startup ? "true" : "false"; ?>;
var pfc_notify_window         = <?php echo $notify_window ? "true" : "false"; ?>;
var pfc_defaultchan = Array(<?php
                         function quoteandescape($v) { return "'".addslashes($v)."'"; }
                         $list = array(); foreach($c->channels as $ch) {$list[] = $ch; }
                         $list = array_map("quoteandescape",$list);
                         echo implode(",", $list);
                         ?>);
var pfc_userchan = Array(<?php
                         $list = array(); foreach($u->channels as $ch) {$list[] = $ch["name"];}
                         $list = array_map("quoteandescape",$list);
                         echo implode(",", $list);
                         ?>);
var pfc_privmsg = Array(<?php
                        $list = array(); foreach($u->privmsg as $pv) {$list[] = $pv["name"];}
                        $list = array_map("quoteandescape",$list);
                        echo implode(",", $list);
                        ?>);
var pfc_openlinknewwindow = <?php echo $openlinknewwindow ? "true" : "false"; ?>;
var pfc_bbcode_color_list = Array(<?php
                                  $list = array(); foreach($bbcode_colorlist as $v) {$list[] = substr($v,1);}
                                  $list = array_map("quoteandescape",$list);
                                  echo implode(",", $list);
                                  ?>);
var pfc_nickname_color_list = Array(<?php
                                    $list = array(); foreach($nickname_colorlist as $v) {$list[] = $v;}
                                    $list = array_map("quoteandescape",$list);
                                    echo implode(",", $list);
                                    ?>);
var pfc_proxy_url = '<?php echo $data_public_url."/".$serverid."/proxy.php"; ?>';


/* create our client which will do all the work on the client side ! */
var pfc = new pfcClient();
<?php

$labels_to_load =
array( "Do you really want to leave this room ?", // _pfc
       "Hide nickname marker", // _pfc
       "Show nickname marker", // _pfc
       "Hide dates and hours", // _pfc
       "Show dates and hours", // _pfc
       "Disconnect", // _pfc
       "Connect", // _pfc
       "Magnify", // _pfc
       "Cut down", // _pfc
       "Hide smiley box", // _pfc
       "Show smiley box", // _pfc
       "Hide online users box", // _pfc
       "Show online users box", // _pfc
       "Please enter your nickname", // _pfc
       "Private message", // _pfc
       "Close this tab", // _pfc
       "Enter your message here", // _pfc
       "Enter your nickname here", // _pfc
       "Bold", // _pfc
       "Italics", // _pfc
       "Underline", // _pfc
       "Delete", // _pfc
       "Mail", // _pfc
       "Color", // _pfc
       "PHP FREE CHAT [powered by phpFreeChat-%s]", // _pfc
       "Enter the text to format", // _pfc
       "Configuration has been rehashed", // _pfc
       "A problem occurs during rehash", // _pfc
       "Choosen nickname is allready used", // _pfc
       "phpfreechat current version is %s", // _pfc
       "Maximum number of joined channels has been reached", // _pfc
       "Maximum number of private chat has been reached", // _pfc
       "Click here to send your message", // _pfc
       "Send", // _pfc
       );
foreach($labels_to_load as $l)
{
  echo "pfc.res.setLabel('".$l."','".addslashes(_pfc2($l))."');\n";
}

$fileurl_to_load =
array( 'images/ch.gif',
       'images/pv.gif',
       'images/tab_remove.gif',
       'images/ch-active.gif',
       'images/pv-active.gif',
       'images/user.gif',
       'images/user-me.gif',
       'images/color-on.gif',
       'images/color-off.gif',
       'images/clock-on.gif',
       'images/clock-off.gif',
       'images/logout.gif',
       'images/login.gif',
       'images/maximize.gif',
       'images/minimize.gif',
       'images/smiley-on.gif',
       'images/smiley-off.gif',
       'images/online-on.gif',
       'images/online-off.gif',
       'images/bt_strong.gif',
       'images/bt_em.gif',
       'images/bt_ins.gif',
       'images/bt_del.gif',
       'images/bt_mail.gif',
       'images/bt_color.gif',
       'images/color_transparent.gif',
       );

foreach($fileurl_to_load as $f)
{
  echo "pfc.res.setFileUrl('".$f."',pfc_proxy_url+'".$c->getFileUrlByProxy($f,false)."');\n";
}

foreach($smileys as $s_file => $s_str) { 
  for($j = 0; $j<count($s_str) ; $j++) {
    echo "pfc.res.setSmiley('".$s_str[$j]."',pfc_proxy_url+'".$c->getFileUrlByProxy($s_file,false)."');\n";
  }
}

?>    

pfc.gui.buildChat();
pfc.connectListener();
pfc.refreshGUI();
if (pfc_connect_at_startup) pfc.connect_disconnect();

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

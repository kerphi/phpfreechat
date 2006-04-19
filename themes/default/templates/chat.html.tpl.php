<div id="<?php echo $prefix; ?>container">
  <img id="<?php echo $prefix; ?>minmax" onclick="pfc.swap_minimize_maximize()" src="<?php echo $c->getFileUrlFromTheme('images/'.($start_minimized?'maximize':'minimize').'.gif'); ?>" alt=""/>
  <h2 id="<?php echo $prefix; ?>title"><?php echo $title; ?></h2>

  <div id="<?php echo $prefix; ?>content_expandable">

  <div id="<?php echo $prefix; ?>content">
    <div id="<?php echo $prefix; ?>chat"></div>
    <div id="<?php echo $prefix; ?>online"></div>
    <div id="<?php echo $prefix; ?>smileys">
      <?php foreach($smileys as $s_file => $s_str) { ?>
      <img src="<?php echo $s_file; ?>" alt="<?php echo $s_str[0]; ?>" title="<?php echo $s_str[0]; ?>" onclick="pfc.insertSmiley('<?php echo $s_str[0]; ?>');" />
      <?php } ?>
    </div>
    <div id="<?php echo $prefix; ?>misc1"></div>
    <div id="<?php echo $prefix; ?>misc2"></div>
    <div id="<?php echo $prefix; ?>misc3"></div>
  </div>

  <div id="<?php echo $prefix; ?>input_container">
    <input id="<?php echo $prefix; ?>words" type="text" title="<?php echo _pfc("Enter your message here"); ?>" maxlength="<?php echo $max_text_len-25; ?>" />
    <div id="<?php echo $prefix; ?>cmd_container">
      <a href="http://www.phpfreechat.net" id="<?php echo $prefix; ?>logo"<?php if($openlinknewwindow) echo ' target="_blank"'; ?>><img src="http://www.phpfreechat.net/pub/logo_80x15.gif" alt="<?php echo _pfc("PHP FREE CHAT [powered by phpFreeChat-%s]", $version); ?>" title="<?php echo _pfc("PHP FREE CHAT [powered by phpFreeChat-%s]", $version); ?>" /></a>
      <input id="<?php echo $prefix; ?>handle" type="button" title="<?php echo _pfc("Enter your nickname here"); ?>" maxlength="<?php echo $max_nick_len; ?>" value="<?php echo $nick; ?>" onclick="if (!pfc.isconnected) return false; pfc.el_words.focus(); pfc.handleRequest('/asknick');" />
      <div class="<?php echo $prefix; ?>btn"><img src="<?php echo $c->getFileUrlFromTheme('images/logout.gif'); ?>" alt="" title="" id="<?php echo $prefix; ?>loginlogout" onclick="pfc.connect_disconnect()" /></div>
      <div class="<?php echo $prefix; ?>btn"><img src="<?php echo $c->getFileUrlFromTheme('images/color-on.gif'); ?>" alt="" title="" id="<?php echo $prefix; ?>nickmarker" onclick="pfc.nickmarker_swap()" /></div>
      <div class="<?php echo $prefix; ?>btn"><img src="<?php echo $c->getFileUrlFromTheme('images/clock-on.gif'); ?>" alt="" title="" id="<?php echo $prefix; ?>clock" onclick="pfc.clock_swap()" /></div>
      <?php if ($c->btn_sh_smileys) { ?>
      <div class="<?php echo $prefix; ?>btn"><img src="<?php echo $c->getFileUrlFromTheme('images/smiley-on.gif'); ?>" alt="" title="" id="<?php echo $prefix; ?>showHideSmileysbtn" onclick="pfc.showHideSmileys()" /></div>
      <?php } ?>
      <?php if ($c->btn_sh_whosonline) { ?>
      <div class="<?php echo $prefix; ?>btn"><img src="<?php echo $c->getFileUrlFromTheme('images/online-on.gif'); ?>" alt="" title="" id="<?php echo $prefix; ?>showHideWhosOnlineBtn" onclick="pfc.showHideWhosOnline()" /></div>
      <?php } ?>
    </div>

    <div id="<?php echo $prefix; ?>bbcode_container">
      <div class="<?php echo $prefix; ?>btn"><img src="<?php echo $c->getFileUrlFromTheme('images/bt_strong.gif'); ?>" alt="<?php echo _pfc("Bold"); ?>" title="<?php echo _pfc("Bold"); ?>" class="<?php echo $prefix; ?>bt_strong" onclick="pfc.insert_text('[b]','[/b]')" /></div>
      <div class="<?php echo $prefix; ?>btn"><img src="<?php echo $c->getFileUrlFromTheme('images/bt_em.gif'); ?>" alt="<?php echo _pfc("Italics"); ?>" title="<?php echo _pfc("Italics"); ?>" class="<?php echo $prefix; ?>bt_strong" onclick="pfc.insert_text('[i]','[/i]')" /></div>
      <div class="<?php echo $prefix; ?>btn"><img src="<?php echo $c->getFileUrlFromTheme('images/bt_ins.gif'); ?>" alt="<?php echo _pfc("Underline"); ?>" title="<?php echo _pfc("Underline"); ?>" class="<?php echo $prefix; ?>bt_strong" onclick="pfc.insert_text('[u]','[/u]')" /></div>
      <div class="<?php echo $prefix; ?>btn"><img src="<?php echo $c->getFileUrlFromTheme('images/bt_del.gif'); ?>" alt="<?php echo _pfc("Delete"); ?>" title="<?php echo _pfc("Delete"); ?>" class="<?php echo $prefix; ?>bt_strong" onclick="pfc.insert_text('[s]','[/s]')" /></div>
<?php /*      <div class="<?php echo $prefix; ?>btn"><img src="<?php echo $c->getFileUrlFromTheme('images/bt_pre.gif'); ?>" alt="<?php echo _pfc("Pre"); ?>" title="<?php echo _pfc("Pre"); ?>" class="<?php echo $prefix; ?>bt_strong" onclick="pfc.insert_text('[pre]','[/pre]')" /></div> */ ?>
      <div class="<?php echo $prefix; ?>btn"><img src="<?php echo $c->getFileUrlFromTheme('images/bt_mail.gif'); ?>" alt="<?php echo _pfc("Mail"); ?>" title="<?php echo _pfc("Mail"); ?>" class="<?php echo $prefix; ?>bt_strong" onclick="pfc.insert_text('[email]','[/email]')" /></div>
      <div class="<?php echo $prefix; ?>btn"><img src="<?php echo $c->getFileUrlFromTheme('images/bt_color.gif'); ?>" alt="<?php echo _pfc("Color"); ?>" title="<?php echo _pfc("Color"); ?>" id="<?php echo $prefix; ?>bt_color" onclick="pfc.minimize_maximize('<?php echo $prefix; ?>colorlist','inline')" /></div>
      <div id="<?php echo $prefix; ?>colorlist">
<?php
  $bbcolor = array();
  $bbcolor[] = "FFFFFF";
  $bbcolor[] = "000000";
  $bbcolor[] = "000055";
  $bbcolor[] = "008000";
  $bbcolor[] = "FF0000";
  $bbcolor[] = "800000";
  $bbcolor[] = "800080";
  $bbcolor[] = "FF5500";
  $bbcolor[] = "FFFF00";
  $bbcolor[] = "00FF00";
  $bbcolor[] = "008080";
  $bbcolor[] = "00FFFF";
  $bbcolor[] = "0000FF";
  $bbcolor[] = "FF00FF";
  $bbcolor[] = "7F7F7F";
  $bbcolor[] = "D2D2D2";

  foreach($bbcolor as $bbc)
  {
    echo '<img src="'.$c->getFileUrlFromTheme('images/color_'.$bbc.'.gif').'" alt="color '.$bbc.'" onclick="pfc.switch_text_color(\''.$bbc.'\')" id="'.$prefix.'color_'.$bbc.'" class="'.$prefix.'color" /> ';
  }
?>
      </div>
		
    </div>
  </div>

  <p id="<?php echo $prefix; ?>errors"></p>

  <div id="<?php echo $prefix; ?>misc4"></div>
  <div id="<?php echo $prefix; ?>misc5"></div>
  <div id="<?php echo $prefix; ?>misc6"></div>
  		
  <script type="text/javascript">
 // <![CDATA[
  <?php  include($c->getFilePathFromTheme('templates/chat.js.tpl.php')); ?>
 // ]]
  </script>
  </div>
</div>

<?php if ($debug) { ?>
<p>Debug is on, you can <a href="<?php echo $debugurl; ?>/console.php?chatid=<?php echo $serverid; ?>">open the debugging console</a>.</p>
<?php } ?>

<?php if ($debugxajax) { ?>
<h2>XAJAX debug</h2>
<pre id="debugxajax" style="border: 1px solid red; background-color: #FEE;"></pre>
<?php } ?>

<?php
// a cleaner workeround has been found (see pfcclient.js.php) :
// if (document.recalc) setTimeout('document.recalc(true);', 0);

/*
<!--[if lt IE 7]>
<script type="text/javascript">
  // this is a IE6 workeround (IE7 works well) to resize correctly the smiley and online boxes
  // this is ugly but I didn't found a cleaner way to fix the problem...
  var src = "http://www.phpfreechat.net/blank.js?";
  for (var i=0; i < 46 ; i++)
    src = src + "0000000000111111111122222222223333333333444444444455555555556666666666777777777788888888889999999999";
  document.writeln('<img src="'+src+'" alt="phpMyVisites" style="border:0; display: none;" />');
</script>
<![endif]-->

*/
?>

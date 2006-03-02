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
    <input id="<?php echo $prefix; ?>words" type="text" title="<?php echo _pfc("Enter your message here"); ?>" maxlength="<?php echo $max_text_len; ?>" />
    <div id="<?php echo $prefix; ?>cmd_container">
      <a href="http://www.phpfreechat.net" id="<?php echo $prefix; ?>logo"><img src="http://www.phpfreechat.net/pub/logo_80x15.gif" alt="<?php echo _pfc("PHP FREE CHAT [powered by phpFreeChat-%s]", $version); ?>" title="<?php echo _pfc("PHP FREE CHAT [powered by phpFreeChat-%s]", $version); ?>" /></a>
      <input id="<?php echo $prefix; ?>handle" type="button" title="<?php echo _pfc("Enter your nickname here"); ?>" maxlength="<?php echo $max_nick_len; ?>" value="<?php echo $nick; ?>" onclick="if (!pfc.isconnected) return false; pfc.handleRequest('/asknick');" />
      <div class="<?php echo $prefix; ?>btn"><img src="<?php echo $c->getFileUrlFromTheme('images/logout.gif'); ?>" alt="" title="" id="<?php echo $prefix; ?>loginlogout" onclick="pfc.connect_disconnect()" /></div>
      <div class="<?php echo $prefix; ?>btn"><img src="<?php echo $c->getFileUrlFromTheme('images/color-on.gif'); ?>" alt="" title="" id="<?php echo $prefix; ?>nickmarker" onclick="pfc.nickmarker_swap()" /></div>
      <div class="<?php echo $prefix; ?>btn"><img src="<?php echo $c->getFileUrlFromTheme('images/clock-on.gif'); ?>" alt="" title="" id="<?php echo $prefix; ?>clock" onclick="pfc.clock_swap()" /></div>
      <?php if ($c->btn_sh_smileys && $c->showsmileys) { ?>
      <div class="<?php echo $prefix; ?>btn"><img src="<?php echo $c->getFileUrlFromTheme('images/smiley-on.gif'); ?>" alt="" title="" id="<?php echo $prefix; ?>showHideSmileysbtn" onclick="pfc.showHideSmileys()" /></div>
      <?php } ?>
      <?php if ($c->btn_sh_whosonline && $c->showwhosonline) { ?>
      <div class="<?php echo $prefix; ?>btn"><img src="<?php echo $c->getFileUrlFromTheme('images/online-on.gif'); ?>" alt="" title="" id="<?php echo $prefix; ?>showHideWhosOnlineBtn" onclick="pfc.showHideWhosOnline()" /></div>
      <?php } ?>
    </div>

    <div id="<?php echo $prefix; ?>bbcode_container">
      <div class="<?php echo $prefix; ?>btn"><img src="<?php echo $c->getFileUrlFromTheme('images/bt_strong.gif'); ?>" alt="<?php echo _pfc("Bold"); ?>" title="<?php echo _pfc("Bold"); ?>" id="<?php echo $prefix; ?>bt_strong" onclick="pfc.insert_text('[b]','[/b]')" /></div>
      <div class="<?php echo $prefix; ?>btn"><img src="<?php echo $c->getFileUrlFromTheme('images/bt_em.gif'); ?>" alt="<?php echo _pfc("Italics"); ?>" title="<?php echo _pfc("Italics"); ?>" id="<?php echo $prefix; ?>bt_strong" onclick="pfc.insert_text('[i]','[/i]')" /></div>
      <div class="<?php echo $prefix; ?>btn"><img src="<?php echo $c->getFileUrlFromTheme('images/bt_ins.gif'); ?>" alt="<?php echo _pfc("Underline"); ?>" title="<?php echo _pfc("Underline"); ?>" id="<?php echo $prefix; ?>bt_strong" onclick="pfc.insert_text('[u]','[/u]')" /></div>
      <div class="<?php echo $prefix; ?>btn"><img src="<?php echo $c->getFileUrlFromTheme('images/bt_del.gif'); ?>" alt="<?php echo _pfc("Delete"); ?>" title="<?php echo _pfc("Delete"); ?>" id="<?php echo $prefix; ?>bt_strong" onclick="pfc.insert_text('[s]','[/s]')" /></div>
<?php /*      <div class="<?php echo $prefix; ?>btn"><img src="<?php echo $c->getFileUrlFromTheme('images/bt_pre.gif'); ?>" alt="<?php echo _pfc("Pre"); ?>" title="<?php echo _pfc("Pre"); ?>" id="<?php echo $prefix; ?>bt_strong" onclick="pfc.insert_text('[pre]','[/pre]')" /></div> */ ?>
      <div class="<?php echo $prefix; ?>btn"><img src="<?php echo $c->getFileUrlFromTheme('images/bt_mail.gif'); ?>" alt="<?php echo _pfc("Mail"); ?>" title="<?php echo _pfc("Mail"); ?>" id="<?php echo $prefix; ?>bt_strong" onclick="pfc.insert_text('[email]','[/email]')" /></div>
      <div class="<?php echo $prefix; ?>btn"><img src="<?php echo $c->getFileUrlFromTheme('images/bt_color.gif'); ?>" alt="<?php echo _pfc("Color"); ?>" title="<?php echo _pfc("Color"); ?>" id="<?php echo $prefix; ?>bt_color" onclick="pfc.minimize_maximize()" /></div>
      <div id="<?php echo $prefix; ?>color">
		  <img src="<?php echo $c->getFileUrlFromTheme('images/color_FFFFFF.gif'); ?>" alt="" title="" id="<?php echo $prefix; ?>color" onclick="pfc.insert_text('[color=#FFFFFF]','[/color]')" />
		  <img src="<?php echo $c->getFileUrlFromTheme('images/color_000000.gif'); ?>" alt="" title="" id="<?php echo $prefix; ?>color" onclick="pfc.insert_text('[color=#000000]','[/color]')" />
		  <img src="<?php echo $c->getFileUrlFromTheme('images/color_000055.gif'); ?>" alt="" title="" id="<?php echo $prefix; ?>color" onclick="pfc.insert_text('[color=#000055]','[/color]')" />
		  <img src="<?php echo $c->getFileUrlFromTheme('images/color_008000.gif'); ?>" alt="" title="" id="<?php echo $prefix; ?>color" onclick="pfc.insert_text('[color=#008000]','[/color]')" />
		  <img src="<?php echo $c->getFileUrlFromTheme('images/color_FF0000.gif'); ?>" alt="" title="" id="<?php echo $prefix; ?>color" onclick="pfc.insert_text('[color=#FF0000]','[/color]')" />
		  <img src="<?php echo $c->getFileUrlFromTheme('images/color_800000.gif'); ?>" alt="" title="" id="<?php echo $prefix; ?>color" onclick="pfc.insert_text('[color=#800000]','[/color]')" />
		  <img src="<?php echo $c->getFileUrlFromTheme('images/color_800080.gif'); ?>" alt="" title="" id="<?php echo $prefix; ?>color" onclick="pfc.insert_text('[color=#800080]','[/color]')" />
		  <img src="<?php echo $c->getFileUrlFromTheme('images/color_FF5500.gif'); ?>" alt="" title="" id="<?php echo $prefix; ?>color" onclick="pfc.insert_text('[color=#FF5500]','[/color]')" />
		  <img src="<?php echo $c->getFileUrlFromTheme('images/color_FFFF00.gif'); ?>" alt="" title="" id="<?php echo $prefix; ?>color" onclick="pfc.insert_text('[color=#FFFF00]','[/color]')" />
		  <img src="<?php echo $c->getFileUrlFromTheme('images/color_00FF00.gif'); ?>" alt="" title="" id="<?php echo $prefix; ?>color" onclick="pfc.insert_text('[color=#00FF00]','[/color]')" />
		  <img src="<?php echo $c->getFileUrlFromTheme('images/color_008080.gif'); ?>" alt="" title="" id="<?php echo $prefix; ?>color" onclick="pfc.insert_text('[color=#008080]','[/color]')" />
		  <img src="<?php echo $c->getFileUrlFromTheme('images/color_00FFFF.gif'); ?>" alt="" title="" id="<?php echo $prefix; ?>color" onclick="pfc.insert_text('[color=#00FFFF]','[/color]')" />
		  <img src="<?php echo $c->getFileUrlFromTheme('images/color_0000FF.gif'); ?>" alt="" title="" id="<?php echo $prefix; ?>color" onclick="pfc.insert_text('[color=#0000FF]','[/color]')" />
		  <img src="<?php echo $c->getFileUrlFromTheme('images/color_FF00FF.gif'); ?>" alt="" title="" id="<?php echo $prefix; ?>color" onclick="pfc.insert_text('[color=#FF00FF]','[/color]')" />
		  <img src="<?php echo $c->getFileUrlFromTheme('images/color_7F7F7F.gif'); ?>" alt="" title="" id="<?php echo $prefix; ?>color" onclick="pfc.insert_text('[color=#7F7F7F]','[/color]')" />
		  <img src="<?php echo $c->getFileUrlFromTheme('images/color_D2D2D2.gif'); ?>" alt="" title="" id="<?php echo $prefix; ?>color" onclick="pfc.insert_text('[color=#D2D2D2]','[/color]')" />
      </div>
		
    </div>
  </div>

  <p id="<?php echo $prefix; ?>errors"></p>

  <div id="<?php echo $prefix; ?>misc4"></div>
  <div id="<?php echo $prefix; ?>misc5"></div>
  <div id="<?php echo $prefix; ?>misc6"></div>
  		
  <script type="text/javascript">
  <!--
  
  <?php include($c->getFileUrlFromTheme('templates/chat.js.tpl.php')); ?>
  
  -->
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
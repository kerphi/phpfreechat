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
      <input id="<?php echo $prefix; ?>handle" type="button" title="<?php echo _pfc("Enter your nickname here"); ?>" maxlength="<?php echo $max_nick_len; ?>" value="<?php echo $nick; ?>" onclick="if (!pfc.login_status) return false; pfc.handleRequest('/asknick');" />
      <div class="<?php echo $prefix; ?>btn"><img src="<?php echo $c->getFileUrlFromTheme('images/logout.gif'); ?>" alt="" title="" id="<?php echo $prefix; ?>loginlogout" onclick="pfc.connect_disconnect()" /></div>
      <div class="<?php echo $prefix; ?>btn"><img src="<?php echo $c->getFileUrlFromTheme('images/color-on.gif'); ?>" alt="" title="" id="<?php echo $prefix; ?>nickmarker" onclick="pfc.nickmarker_swap()" /></div>
      <div class="<?php echo $prefix; ?>btn"><img src="<?php echo $c->getFileUrlFromTheme('images/clock-on.gif'); ?>" alt="" title="" id="<?php echo $prefix; ?>clock" onclick="pfc.clock_swap()" /></div>

    </div>
  </div>

  <p id="<?php echo $prefix; ?>errors"></p>

  <div id="<?php echo $prefix; ?>misc4"></div>
  <div id="<?php echo $prefix; ?>misc5"></div>
  <div id="<?php echo $prefix; ?>misc6"></div>
  		
  <script type="text/javascript">
  <!--
  
  <?php include("javascript2.js.tpl.php"); ?>
  
  -->
  </script>
  </div>
</div>

<?php if ($debug) { ?>
<p>Debug is on, you can <a href="<?php echo $debugurl; ?>/console.php?chatid=<?php echo $serverid; ?>">open the debugging console</a>.</p>
<?php } ?>

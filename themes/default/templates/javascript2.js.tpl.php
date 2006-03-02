<?php include($c->getFileUrlFromTheme('templates/chat-pre.js.tpl.php')); ?>

/* preload smileys */
preloadImages(
  <?php foreach ($smileys as $s_file => $s_str) { ?>
   '<?php echo $s_file; ?>',
  <?php } ?>
  ''
);

/* create our client which will to all the work ! */
var pfc = new pfcClient();

<?php if ($connect_at_startup) { ?>
pfc.connect_disconnect();
<?php } ?>
pfc.refresh_loginlogout();
pfc.refresh_nickmarker();
pfc.refresh_clock();
pfc.refresh_minimize_maximize();
pfc.refresh_Smileys();
pfc.refresh_WhosOnline();

<?php include($c->getFileUrlFromTheme('templates/chat-post.js.tpl.php')); ?>
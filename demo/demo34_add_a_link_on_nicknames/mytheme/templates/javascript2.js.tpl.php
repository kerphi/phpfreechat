/* preload smileys */
preloadImages(
  <?php foreach ($smileys as $s_file => $s_str) { ?>
   '<?php echo $s_file; ?>',
  <?php } ?>
  ''
);

pfcClient.prototype.updateNickList = function(lst)
{
  this.nicklist = lst;
  var nicks   = lst;
  var nickdiv = this.el_online;
  var ul = document.createElement('ul');
  for (var i=0; i<nicks.length; i++)
  {
    var li = document.createElement('li');
    var a = document.createElement('a');
    a.setAttribute('href','http://www.google.com/search?q='+nicks[i]);
    a.setAttribute('target','_blank');
    li.setAttribute('class', '<?php echo $prefix; ?>nickmarker <?php echo $prefix; ?>nick_'+ hex_md5(nicks[i]));
    var txt = document.createTextNode(nicks[i]);
    a.appendChild(txt);
    li.appendChild(a);
    ul.appendChild(li);
  }
  var fc = nickdiv.firstChild;
  if (fc)
    nickdiv.replaceChild(ul,fc);
  else
    nickdiv.appendChild(ul,fc);
  this.colorizeNicks(nickdiv);
}

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

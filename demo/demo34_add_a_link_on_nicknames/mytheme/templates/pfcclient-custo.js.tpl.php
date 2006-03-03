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
    a.setAttribute('class', '<?php echo $prefix; ?>nickmarker <?php echo $prefix; ?>nick_'+ hex_md5(nicks[i]));
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
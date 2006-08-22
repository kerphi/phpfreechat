pfcClient.prototype.updateNickList = function(tabid,lst)
{
  this.nicklist[tabid] = lst;
  var nicks   = lst;
  var nickdiv = this.gui.getOnlineContentFromTabId(tabid).firstChild;
  var ul = document.createElement('ul');
  for (var i=0; i<nicks.length; i++)
  {
    var li = document.createElement('li');
    if (nicks[i] != this.nickname)
    {
      // this is someone -> create a privmsg link
      var img = document.createElement('img');
      img.setAttribute('src', this.res.getFileUrl('images/user.gif'));
      img.alt = this.res.getLabel('Private message');
      img.title = img.alt;
      img.style.marginRight = '5px';
      var a = document.createElement('a');
      a.setAttribute('href', '');
      a.pfc_nick = nicks[i];
      a.onclick = function(){pfc.sendRequest('/privmsg', this.pfc_nick); return false;}
      a.appendChild(img);
      li.appendChild(a);
    }
    else
    {
      // this is myself -> do not create a privmsg link
      var img = document.createElement('img');
      img.setAttribute('src', this.res.getFileUrl('images/user-me.gif'));
      img.alt = '';
      img.title = img.alt;
      img.style.marginRight = '5px';
      li.appendChild(img);
    }
    

    // nobr is not xhtml valid but it's a workeround 
    // for IE which doesn't support 'white-space: pre' css rule
    var nobr = document.createElement('nobr');
    var a = document.createElement('a');
    a.pfc_nick = nicks[i];
    a.setAttribute('href','http://www.google.com/search?q='+nicks[i]);
    a.setAttribute('target','_blank');
    //a.onclick = function(){pfc.insert_text(this.pfc_nick+", ","",false); return false;}
    a.appendChild(document.createTextNode(nicks[i]));
    a.setAttribute('class', 'pfc_nickmarker pfc_nick_'+ hex_md5(_to_utf8(nicks[i])));
    a.setAttribute('className', 'pfc_nickmarker pfc_nick_'+ hex_md5(_to_utf8(nicks[i]))); // for IE6

    nobr.appendChild(a);
    li.appendChild(nobr);
    li.style.borderBottom = '1px solid #AAA';
    
    ul.appendChild(li);
  }
  var fc = nickdiv.firstChild;
  if (fc)
    nickdiv.replaceChild(ul,fc);
  else
    nickdiv.appendChild(ul,fc);
  this.colorizeNicks(nickdiv);
}
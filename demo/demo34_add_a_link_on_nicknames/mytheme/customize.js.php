pfcClient.prototype.buildNickItem = function(nickid)
{
    var className = (! is_ie) ? 'class' : 'className';

    var nick = this.getUserMeta(nickid, 'nick');
    var isadmin = this.getUserMeta(nickid, 'isadmin');
    if (isadmin == '') isadmin = false;

    var li = document.createElement('li');

    var a = document.createElement('a');
    a.pfc_nick   = nick;
    a.pfc_nickid = nickid;
    a.setAttribute('target','_blank');
    a.setAttribute('href','http://www.google.com/search?q='+nick);
    /*
    a.onclick = function(evt){
      var d = pfc.getNickWhoisBox(this.pfc_nickid);
      document.body.appendChild(d);
      d.style.display = 'block';
      d.style.zIndex = '400';
      d.style.position = 'absolute';
      d.style.left = (mousePosX(evt)-5)+'px';
      d.style.top  = (mousePosY(evt)-5)+'px';
      return false;
    }
    */
    li.appendChild(a);

    var img = document.createElement('img');
    if (isadmin)
      img.setAttribute('src', this.res.getFileUrl('images/user-admin.gif'));
    else
      img.setAttribute('src', this.res.getFileUrl('images/user.gif'));
    img.style.marginRight = '5px';
    img.setAttribute(className, 'pfc_nickbutton');
    a.appendChild(img);

    // nobr is not xhtml valid but it's a workeround 
    // for IE which doesn't support 'white-space: pre' css rule
    var nobr = document.createElement('nobr');
    var span = document.createElement('span');
    span.setAttribute(className, 'pfc_nickmarker pfc_nick_'+nickid);
    span.appendChild(document.createTextNode(nick));
    nobr.appendChild(span);
    a.appendChild(nobr);

    return li;
}

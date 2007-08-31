pfcClient.prototype.updateNickWhoisBox = function(nickid)
{
    var className = (! is_ie) ? 'class' : 'className';

    var usermeta = this.getAllUserMeta(nickid);

    var div  = document.createElement('div');
    div.setAttribute(className, 'pfc_nickwhois');

    var p = document.createElement('p');
    p.setAttribute(className, 'pfc_nickwhois_header');
    div.appendChild(p);

    // add the close button
    var img = document.createElement('img');
    img.setAttribute(className, 'pfc_nickwhois_close');
    img.pfc_parent = div;
    img.onclick = function(evt){
      this.pfc_parent.style.display = 'none';
      return false;
    }
    img.setAttribute('src', this.res.getFileUrl('images/close-whoisbox.gif'));
    img.alt = this.res.getLabel('Close');
    p.appendChild(img);
    p.appendChild(document.createTextNode(usermeta['nick'])); // append the nickname text in the title

    // add the whois information table
    var table = document.createElement('table');
    var tbody = document.createElement('tbody');
    table.appendChild(tbody);
    var um_keys = usermeta.keys();
    var msg = '';
    for (var i=0; i<um_keys.length; i++)
    {
      var k = um_keys[i];
      var v = usermeta[k];
      if (v && k != 'nickid'
            && k != 'nick' // useless because it is displayed in the box title
            && k != 'isadmin' // useless because of the gold shield icon
            && k != 'floodtime'
            && k != 'flood_nbmsg'
            && k != 'flood_nbchar'
            && k != 'avatar'
         )
      {
        var tr = document.createElement('tr');
        var td1 = document.createElement('td');
        td1.setAttribute(className, 'pfc_nickwhois_c1');
        var td2 = document.createElement('td');
        td2.setAttribute(className, 'pfc_nickwhois_c2');
        td1.appendChild(document.createTextNode(k));
        td2.appendChild(document.createTextNode(v));
        tr.appendChild(td1);
        tr.appendChild(td2);
        tbody.appendChild(tr);
      }
    }
    div.appendChild(table);

    // append the avatar image
    if (this.getUserMeta(nickid,'avatar'))
    {
      var img = document.createElement('img');
      img.setAttribute('src',this.getUserMeta(nickid,'avatar'));
      img.setAttribute(className, 'pfc_nickwhois_avatar');
      div.appendChild(img);
    }
    
    // add the privmsg link (do not add it if this button is yourself)
    if (pfc.getUserMeta(nickid,'nick') != this.nickname)
    {
      var p = document.createElement('p');
      p.setAttribute(className, 'pfc_nickwhois_pv');
      var a = document.createElement('a');
      a.setAttribute('href', '');
      a.pfc_nickid = nickid;
      a.pfc_parent = div;
      a.onclick = function(evt){
        var nick = pfc.getUserMeta(this.pfc_nickid,'nick');
        pfc.sendRequest('/privmsg "'+nick+'"');
        this.pfc_parent.style.display = 'none';
        return false;
      }
      var img = document.createElement('img');
      img.setAttribute('src', this.res.getFileUrl('images/openpv.gif'));
      img.alt = document.createTextNode(this.res.getLabel('Private message'));
      a.appendChild(img);
      a.appendChild(document.createTextNode(this.res.getLabel('Private message')));
      p.appendChild(a);
      div.appendChild(p);
    }

    this.nickwhoisbox[nickid] = div;  
}

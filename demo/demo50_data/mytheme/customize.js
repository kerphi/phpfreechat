pfcClient.prototype.updateNickWhoisBox = function(nickid)
  {
    var div  = document.createElement('div');
    div.setAttribute('class',     'pfc_nickwhois');
    div.setAttribute('className', 'pfc_nickwhois'); // for IE6

    var ul = document.createElement('ul');
    div.appendChild(ul);

    // add the close button
    var li = document.createElement('li');
    li.setAttribute('class',     'pfc_nickwhois_close');
    li.setAttribute('className', 'pfc_nickwhois_close'); // for IE6
    ul.appendChild(li);
    var a = document.createElement('a');
    a.setAttribute('href', '');
    a.pfc_parent = div;
    a.onclick = function(evt){
      this.pfc_parent.style.display = 'none';
      return false;
    }
    var img = document.createElement('img');
    img.setAttribute('src', this.res.getFileUrl('images/close-whoisbox.gif'));
    img.alt = document.createTextNode(this.res.getLabel('Close'));
    a.appendChild(img);
    li.appendChild(a);

    // add the privmsg link (do not add it if this button is yourself)
    if (pfc.getUserMeta(nickid,'nick') != this.nickname)
    {
      var li = document.createElement('li');
      li.setAttribute('class',     'pfc_nickwhois_pv');
      li.setAttribute('className', 'pfc_nickwhois_pv'); // for IE6
      ul.appendChild(li);
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
      li.appendChild(a);
    }


    // add the whois information table
    var table = document.createElement('table');
//    table.setAttribute('cellspacing',0);
//    table.setAttribute('cellpadding',0);
//    table.setAttribute('border',0);
    var tbody = document.createElement('tbody');
    table.appendChild(tbody);
    var um = this.getAllUserMeta(nickid);
    var um_keys = um.keys();
    var msg = '';
    for (var i=0; i<um_keys.length; i++)
    {
      var k = um_keys[i];
      var v = um[k];
      if (v && k != 'nickid'
            && k != 'floodtime'
            && k != 'flood_nbmsg'
            && k != 'flood_nbchar'
            && k != 'avatar'
         )
      {
        var tr = document.createElement('tr');
        var td1 = document.createElement('td');
        td1.setAttribute('class',     'pfc_nickwhois_c1');
        td1.setAttribute('className', 'pfc_nickwhois_c1'); // for IE6
        var td2 = document.createElement('td');
        td2.setAttribute('class',     'pfc_nickwhois_c2');
        td2.setAttribute('className', 'pfc_nickwhois_c2'); // for IE6
        td1.appendChild(document.createTextNode(k));
        td2.appendChild(document.createTextNode(v));
        tr.appendChild(td1);
        tr.appendChild(td2);
        tbody.appendChild(tr);
      }
    }
    div.appendChild(table);

    // append the avatar image
    var img = document.createElement('img');
    img.setAttribute('src',this.getUserMeta(nickid,'avatar'));
    img.setAttribute('class',     'pfc_nickwhois_avatar');
    img.setAttribute('className', 'pfc_nickwhois_avatar'); // for IE6
    div.appendChild(img);

    this.nickwhoisbox[nickid] = div;
  }

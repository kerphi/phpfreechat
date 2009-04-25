pfcClient.prototype.updateNickWhoisBox_ignored_field = function(k)
{
      return ( k == 'nickid' ||
               k == 'nick' || // useless because it is displayed in the box title
               k == 'isadmin' || // useless because of the gold shield icon
               k == 'floodtime' ||
               k == 'flood_nbmsg' ||
               k == 'flood_nbchar' ||
               k == 'avatar'
               );
}
    
pfcClient.prototype.updateNickWhoisBox_append_html = function(nickid, div)
{
    var className = (! is_ie) ? 'class' : 'className';
    
    // append the avatar image
    if (this.getUserMeta(nickid,'avatar'))
    {
      var img = document.createElement('img');
      img.setAttribute('src',this.getUserMeta(nickid,'avatar'));
      img.setAttribute(className, 'pfc_nickwhois_avatar');
      div.appendChild(img);
    }
}

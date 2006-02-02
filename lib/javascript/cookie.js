function EcrireCookie(nom, valeur)
{
  var argv=EcrireCookie.arguments;
  var argc=EcrireCookie.arguments.length;
  var expires=(argc > 2) ? argv[2] : null;
  var path=(argc > 3) ? argv[3] : null;
  var domain=(argc > 4) ? argv[4] : null;
  var secure=(argc > 5) ? argv[5] : false;
  document.cookie=nom+"="+escape(valeur)+
  ((expires==null) ? "" : ("; expires="+expires.toGMTString()))+
  ((path==null) ? "" : ("; path="+path))+
  ((domain==null) ? "" : ("; domain="+domain))+
  ((secure==true) ? "; secure" : "");
}
function getCookieVal(offset)
{
  var endstr=document.cookie.indexOf (";", offset);
  if (endstr==-1) endstr=document.cookie.length;
  return unescape(document.cookie.substring(offset, endstr));
}
function LireCookie(nom)
{
  var arg=nom+"=";
  var alen=arg.length;
  var clen=document.cookie.length;
  var i=0;
  while (i<clen)
  {
    var j=i+alen;
    if (document.cookie.substring(i, j)==arg) return getCookieVal(j);
    i=document.cookie.indexOf(" ",i)+1;
    if (i==0) break;
  }
  return null;
}
function EffaceCookie(nom)
{
  date=new Date;
  date.setFullYear(date.getFullYear()-1);
  EcrireCookie(nom,null,date);
}
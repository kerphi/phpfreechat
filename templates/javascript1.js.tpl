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



/* define the JS variable used to store timer and nicknames list */
var ~[$prefix]~timeout;
var ~[$prefix]~nicklist = Array();

var cookie = '';
cookie = LireCookie('~[$prefix]~nickmarker');
var ~[$prefix]~nickmarker = (cookie == 'true'); if (cookie == null) ~[$prefix]~nickmarker = true;
cookie = LireCookie('~[$prefix]~clock');
var ~[$prefix]~clock = (cookie == 'true'); if (cookie == null) ~[$prefix]~clock = true;

/* unique client id for each windows used to identify a open window
   this id is passed every time the JS communicate with server */
var ~[$prefix]~clientid = '~[$clientid]~';
var ~[$prefix]~colorlist = Array(
'#CCCCCC',
'#000000',
'#3636B2',
'#2A8C2A',
'#C33B3B',
'#C73232',
'#80267F',
'#66361F',
'#D9A641',
'#3DCC3D',
'#1A5555',
'#2F8C74',
'#4545E6',
'#B037B0',
'#4C4C4C',
'#959595'
);
var ~[$prefix]~nickcolor = Array();

/* show error area and assign to it an error message and start the blinking of given fields */
function ~[$prefix]~SetError(str, ids)
{
  document.getElementById('~[$prefix]~errors').innerHTML = str;
  document.getElementById('~[$prefix]~errors').style.display = 'block';
  for (var i=0;i<ids.length;i++)
    ~[$prefix]~Blink(ids[i], 'start');
}

/* hide error area and stop blinking fields */
function ~[$prefix]~ClearError(ids)
{ 
  document.getElementById('~[$prefix]~errors').style.display = 'none';
  for (var i=0;i<ids.length;i++)
    ~[$prefix]~Blink(ids[i], 'stop');
}

/* blink routines used by Error functions */
var blinktmp = Array();
var blinkloop = Array();
var blinktimeout = Array();
function ~[$prefix]~Blink(id, action)
 {
  clearTimeout(blinktimeout[id]);
   if (action == 'start')
   {
    blinktmp[id] = document.getElementById(id).style.backgroundColor;
	   clearTimeout(blinktimeout[id]);  	
	   blinktimeout[id] = setTimeout('~[$prefix]~Blink(\'' + id + '\',\'loop\')', 500);
	 }
   if (action == 'stop')
   {
    document.getElementById(id).style.backgroundColor = blinktmp[id];
	 }  	
   if (action == 'loop')
   {
	   if (blinkloop[id] == 1)
	   {
      document.getElementById(id).style.backgroundColor = '#FFDFC0';
	     blinkloop[id] = 2;
  	 } else {
      document.getElementById(id).style.backgroundColor = '#FFFFFF';
	     blinkloop[id] = 1;
  	 }
	   blinktimeout[id] = setTimeout('~[$prefix]~Blink(\'' + id + '\',\'loop\')', 500);
   }  	
 }

/* insert a smiley */
function ~[$prefix]~insertSmiley(s)
{
  document.getElementById('~[$prefix]~words').value += s;
  document.getElementById('~[$prefix]~words').focus();
}

/* fill the nickname list with connected nicknames */
function ~[$prefix]~updateNickList()
{
  var nicks = ~[$prefix]~nicklist;
  var nickdiv = document.getElementById('~[$prefix]~online');
  var ul = document.createElement('ul');
  for (var i=0; i<nicks.length; i++)
  {
    var li = document.createElement('li');
    var txt = document.createTextNode(nicks[i]);
    li.appendChild(txt);
    ul.appendChild(li);
  }
  var fc = nickdiv.firstChild;
  if (fc)
    nickdiv.replaceChild(ul,fc);
  else
    nickdiv.appendChild(ul,fc);
}
/* clear the nickname list */
function ~[$prefix]~clearNickList()
{
  var nickdiv = document.getElementById('~[$prefix]~online');
  var fc = nickdiv.firstChild;
  nickdiv.removeChild(fc);
}

/* clear the message list history */
function ~[$prefix]~clearMessages()
{
  var msgdiv = document.getElementById('~[$prefix]~chat');
  msgdiv.innerHTML = '';
}

/* parse message and append it to the message list */
function ~[$prefix]~parseAndPost(id, date, heure, nick, words, cmd, fromtoday, oldmsg)
{
  var msgdiv = document.getElementById('~[$prefix]~chat');

  /* format and post message */
  var line = '';
  line += '<div id="~[$prefix]~msg'+ id +'" class="~[$prefix]~'+ cmd +' ~[$prefix]~message';
  if (oldmsg == 1) line += ' ~[$prefix]~oldmsg';
  line += '">';
  line += '<span class="~[$prefix]~date';
  if (fromtoday == 1) line += ' ~[$prefix]~invisible';
  line += '">'+ date +'</span> ';
  line += '<span class="~[$prefix]~heure">'+ heure +'</span> ';
  if (cmd == 'cmd_msg')
  {
    line += ' <span class="~[$prefix]~nick">';
    line += '&#x2039;';
    line += '<span class="~[$prefix]~nickmarker ~[$prefix]~nick_'+ nick +'">';
    line += nick;
    line += '</span>';
    line += '&#x203A;';
    line += '</span> ';
  }
  if (cmd == 'cmd_notice' || cmd == 'cmd_me')
    line += '<span class="~[$prefix]~words">* '+ words +'</span> ';
  else
    line += '<span class="~[$prefix]~words">'+ words +'</span> ';
  line += '</div>';

  /* create a dummy div to avoid konqueror bug when setting nickmarkers */
  var m = document.createElement('div');
  m.innerHTML = line;
  msgdiv.appendChild(m);

  ~[$prefix]~scrolldown('~[$prefix]~msg' + id);


  /* colorize messages nicknames */
  var root = document.getElementById('~[$prefix]~msg' + id);
  ~[$prefix]~colorizeNicks(root);
  ~[$prefix]~refresh_nickmarker(root);
  ~[$prefix]~refresh_clock(root);
}

/* scroll down from the posted message height */
function ~[$prefix]~scrolldown(id)
{
  var elttoscroll = document.getElementById(id);
  document.getElementById('~[$prefix]~chat').scrollTop += elttoscroll.offsetHeight+2;
}

/* apply nicknames color to the root childs */
function ~[$prefix]~colorizeNicks(root)
{
  for(var i = 0; i < ~[$prefix]~nicklist.length; i++)
  {
    var cur_nick = ~[$prefix]~nicklist[i];
    var cur_color = '';
    /* check the nickname is colorized or not */
    var allready_colorized = false;
    for(var j = 0; j < ~[$prefix]~nickcolor.length; j++)
    {
      if (~[$prefix]~nickcolor[j][0] == cur_nick)
      {
        allready_colorized = true;
        cur_color = ~[$prefix]~nickcolor[j][1];
      }
    }
    if (!allready_colorized)
    {
      /* take the next color from the list and colorize this nickname */
      var cid = Math.round(Math.random()*(~[$prefix]~colorlist.length-1));
      cur_color = ~[$prefix]~colorlist[cid];
      ~[$prefix]~colorlist.splice(cid,1);
      ~[$prefix]~nickcolor.push(new Array(cur_nick, cur_color));
    }
    ~[$prefix]~applyNickColor(root, cur_nick, cur_color);
  }
}

function ~[$prefix]~applyNickColor(root, nick, color)
{
  var nicktochange = getElementsByClassName(root, '~[$prefix]~nick_'+ nick, '')
  for(var i = 0; nicktochange.length > i; i++)
    nicktochange[i].style.color = color; 
}

function getElementsByClassName( root, clsName, clsIgnore ) {
   var i, matches=new Array();
   var els=root.getElementsByTagName('*');
   var rx1 = new RegExp('.*'+clsName+'.*');
   var rx2 = new RegExp('.*'+clsIgnore+'.*');

   for(i=0; i<els.length; i++) {
      if(els.item(i).className.match(rx1) &&
         (clsIgnore == '' || !els.item(i).className.match(rx2)) ) {
         matches.push(els.item(i));
      }
   }
   return matches;
}

function showClass(root, clsName, clsIgnore, show)
{
  var elts = getElementsByClassName(root, clsName, clsIgnore);
  for(var i = 0; elts.length > i; i++)
    if (show)
      elts[i].style.display = 'inline';
    else
      elts[i].style.display = 'none';
}


/**
 * Nickname marker show/hide
 */
function ~[$prefix]~nickmarker_swap()
{
  if (~[$prefix]~nickmarker)
  {
    ~[$prefix]~nickmarker = false;
  }
  else
  {
    ~[$prefix]~nickmarker = true;
  }
  ~[$prefix]~refresh_nickmarker()
  EcrireCookie('~[$prefix]~nickmarker', ~[$prefix]~nickmarker);
}
function ~[$prefix]~refresh_nickmarker( root )
{
  var nickmarker_icon = document.getElementById('~[$prefix]~nickmarker');
  if (!root) root = document.getElementById('~[$prefix]~chat');
  if (~[$prefix]~nickmarker)
  {
    nickmarker_icon.src   = "~[$rootpath]~/misc/color-on.png";
    nickmarker_icon.alt   = "Hide nickname marker";
    nickmarker_icon.title = "Hide nickname marker";
    ~[$prefix]~colorizeNicks(root);
  }
  else
  {
    nickmarker_icon.src = "~[$rootpath]~/misc/color-off.png";
    nickmarker_icon.alt   = "Show nickname marker";
    nickmarker_icon.title = "Show nickname marker";
    var elts = getElementsByClassName(root, '~[$prefix]~nickmarker', '');
    for(var i = 0; elts.length > i; i++)
    {
      /* this is not supported in konqueror =>>>  elts[i].removeAttribute('style');*/
      elts[i].style.color = '';
    }
  }
}


/**
 * Date/Hour show/hide
 */
function ~[$prefix]~clock_swap()
{
  if (~[$prefix]~clock)
  {
    ~[$prefix]~clock = false;
  }
  else
  {
    ~[$prefix]~clock = true;
  }
  ~[$prefix]~refresh_clock()
  EcrireCookie('~[$prefix]~clock', ~[$prefix]~clock);
}
function ~[$prefix]~refresh_clock( root )
{
  var clock_icon = document.getElementById('~[$prefix]~clock');
  if (!root) root = document.getElementById('~[$prefix]~chat');
  if (~[$prefix]~clock)
  {
    clock_icon.src   = "~[$rootpath]~/misc/clock-on.png";
    clock_icon.alt   = "Hide date/hour";
    clock_icon.title = "Hide date/hour";
    showClass(root, '~[$prefix]~date', '~[$prefix]~invisible', true);
    showClass(root, '~[$prefix]~heure', '~[$prefix]~invisible', true);
  }
  else
  {
    clock_icon.src = "~[$rootpath]~/misc/clock-off.png";
    clock_icon.alt   = "Show date/hour";
    clock_icon.title = "Show date/hour";
    showClass(root, '~[$prefix]~date', '~[$prefix]~invisible', false);
    showClass(root, '~[$prefix]~heure', '~[$prefix]~invisible', false);
  }
}


/* define the JS variable used to store timer and nicknames list */
var ~[$prefix]~timeout;
var ~[$prefix]~nicklist = Array();
/* unique client id for each windows used to identify a open window
   this id is passed every time the JS communicate with server */
var ~[$prefix]~clientid = '~[$clientid]~';
var ~[$prefix]~colorlist = Array(
'#d24740',
'#d27540',
'#d29540',
'#d2ba40',
'#d2d240',
'#b0d240',
'#84d240',
'#53d240',
'#40d251',
'#40d273',
'#40d29a',
'#40d2c1',
'#40c1d2',
'#4098d2',
'#4073d2',
'#4042d2',
'#6e40d2',
'#9840d2',
'#bf40d2',
'#d240d2',
'#d240b2',
'#d2408b',
'#d24067',
'#d24045',
'#d84524',
'#7b7b7b',
'#ffff6d'
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
  line += '<span class="~[$prefix]~nick">';
  if (cmd == 'cmd_msg')
    line += '<span class="~[$prefix]~nick_'+ nick +'">@</span>&#x2039;'+ nick +'&#x203A;</span> ';
  if (cmd == 'cmd_notice' || cmd == 'cmd_me')
    line += '<span class="~[$prefix]~words">* '+ words +'</span> ';
  else
    line += '<span class="~[$prefix]~words">'+ words +'</span> ';
  line += '</div>';
  msgdiv.innerHTML += line;

  /* colorize messages nicknames */
  ~[$prefix]~colorizeNicks(document.getElementById('~[$prefix]~msg' + id));
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
  var nicktochange = getElementsByClassName(root, '~[$prefix]~nick_'+ nick)
  for(var i = 0; nicktochange.length > i; i++)
    nicktochange[i].style['color'] = color; 
}

function getElementsByClassName( root, clsName ) {
   var i, matches=new Array();
   var els=root.getElementsByTagName('*');

   for(i=0; i<els.length; i++) {
      if(els.item(i).className==clsName) {
         matches.push(els.item(i));
      }
   }
   return matches;
}
/* define the JS variable used to store timer and nicknames list */
var <?php echo $prefix; ?>timeout;
var <?php echo $prefix; ?>nicklist = Array();

var cookie = '';
cookie = getCookie('<?php echo $prefix; ?>nickmarker');
var <?php echo $prefix; ?>nickmarker = (cookie == 'true');
if (cookie == null)
  <?php echo $prefix; ?>nickmarker = <?php if ($nickmarker) { ?>true<?php } else { ?>false<?php } ?>;
cookie = getCookie('<?php echo $prefix; ?>clock');
var <?php echo $prefix; ?>clock = (cookie == 'true');
if (cookie == null)
  <?php echo $prefix; ?>clock = <?php if ($clock) { ?>true<?php } else { ?>false<?php } ?>;

/* unique client id for each windows used to identify a open window
   this id is passed every time the JS communicate with server */
var <?php echo $prefix; ?>clientid = '<?php
    // generate a unique client id (stored with JS: client side)
    // this id is used to identify client window
    // (2 clients can use the same session: then only the nickname is shared)
    echo md5(uniqid(rand(), true)); ?>';
var <?php echo $prefix; ?>colorlist = Array();
var <?php echo $prefix; ?>nickcolor = Array();

/* show error area and assign to it an error message and start the blinking of given fields */
function <?php echo $prefix; ?>SetError(str, ids)
{
  document.getElementById('<?php echo $prefix; ?>errors').innerHTML = str;
  document.getElementById('<?php echo $prefix; ?>errors').style.display = 'block';
  for (var i=0;i<ids.length;i++)
    <?php echo $prefix; ?>Blink(ids[i], 'start');
}

/* hide error area and stop blinking fields */
function <?php echo $prefix; ?>ClearError(ids)
{ 
  document.getElementById('<?php echo $prefix; ?>errors').style.display = 'none';
  for (var i=0;i<ids.length;i++)
    <?php echo $prefix; ?>Blink(ids[i], 'stop');
}

/* blink routines used by Error functions */
var blinktmp = Array();
var blinkloop = Array();
var blinktimeout = Array();
function <?php echo $prefix; ?>Blink(id, action)
 {
  clearTimeout(blinktimeout[id]);
   if (action == 'start')
   {
    blinktmp[id] = document.getElementById(id).style.backgroundColor;
	   clearTimeout(blinktimeout[id]);  	
	   blinktimeout[id] = setTimeout('<?php echo $prefix; ?>Blink(\'' + id + '\',\'loop\')', 500);
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
	   blinktimeout[id] = setTimeout('<?php echo $prefix; ?>Blink(\'' + id + '\',\'loop\')', 500);
   }  	
 }

/* insert a smiley */
function <?php echo $prefix; ?>insertSmiley(s)
{
  document.getElementById('<?php echo $prefix; ?>words').value += s;
  document.getElementById('<?php echo $prefix; ?>words').focus();
}

/* fill the nickname list with connected nicknames */
function <?php echo $prefix; ?>updateNickList()
{
  var nicks = <?php echo $prefix; ?>nicklist;
  var nickdiv = document.getElementById('<?php echo $prefix; ?>online');
  var ul = document.createElement('ul');
  for (var i=0; i<nicks.length; i++)
  {
    var li = document.createElement('li');
    li.setAttribute('class', '<?php echo $prefix; ?>nickmarker <?php echo $prefix; ?>nick_'+ hex_md5(nicks[i]));
    var txt = document.createTextNode(nicks[i]);
    li.appendChild(txt);
    ul.appendChild(li);
  }
  var fc = nickdiv.firstChild;
  if (fc)
    nickdiv.replaceChild(ul,fc);
  else
    nickdiv.appendChild(ul,fc);
  <?php echo $prefix; ?>colorizeNicks(nickdiv);
}
/* clear the nickname list */
function <?php echo $prefix; ?>clearNickList()
{
  var nickdiv = document.getElementById('<?php echo $prefix; ?>online');
  var fc = nickdiv.firstChild;
  nickdiv.removeChild(fc);
}

/* clear the message list history */
function <?php echo $prefix; ?>clearMessages()
{
  var msgdiv = document.getElementById('<?php echo $prefix; ?>chat');
  msgdiv.innerHTML = '';
}

/* parse message and append it to the message list */
function <?php echo $prefix; ?>parseAndPost(id, date, heure, nick, words, cmd, fromtoday, oldmsg)
{
  var msgdiv = document.getElementById('<?php echo $prefix; ?>chat');

  /* check the nickname is in the list or not */
  var nickfound = false;
  for(var i = 0; i < <?php echo $prefix; ?>nicklist.length && !nickfound; i++)
  {
    if (<?php echo $prefix; ?>nicklist[i] == nick)
      nickfound = true;
  }
  var nickcolor = '';
  if (nickfound) nickcolor = <?php echo $prefix; ?>getAndAssignNickColor(nick);

  /* format and post message */
  var line = '';
  line += '<div id="<?php echo $prefix; ?>msg'+ id +'" class="<?php echo $prefix; ?>'+ cmd +' <?php echo $prefix; ?>message';
  if (oldmsg == 1) line += ' <?php echo $prefix; ?>oldmsg';
  line += '">';
  line += '<span class="<?php echo $prefix; ?>date';
  if (fromtoday == 1) line += ' <?php echo $prefix; ?>invisible';
  line += '">'+ date +'</span> ';
  line += '<span class="<?php echo $prefix; ?>heure">'+ heure +'</span> ';
  if (cmd == 'cmd_msg')
  {
    line += ' <span class="<?php echo $prefix; ?>nick">';
    line += '&#x2039;';
    line += '<span ';
    if (nickcolor != '') line += 'style="color: ' + nickcolor + '" ';
    line += 'class="<?php echo $prefix; ?>nickmarker <?php echo $prefix; ?>nick_'+ hex_md5(nick) +'">';
    line += nick;
    line += '</span>';
    line += '&#x203A;';
    line += '</span> ';
  }
  if (cmd == 'cmd_notice' || cmd == 'cmd_me')
    line += '<span class="<?php echo $prefix; ?>words">* '+ words +'</span> ';
  else
    line += '<span class="<?php echo $prefix; ?>words">'+ words +'</span> ';
  line += '</div>';

  /* create a dummy div to avoid konqueror bug when setting nickmarkers */
  var m = document.createElement('div');
  m.innerHTML = line;
  msgdiv.appendChild(m);

  <?php echo $prefix; ?>scrolldown('<?php echo $prefix; ?>msg' + id);


  /* colorize messages nicknames */
  var root = document.getElementById('<?php echo $prefix; ?>msg' + id);
/*  <?php echo $prefix; ?>colorizeNicks(root);*/
  <?php echo $prefix; ?>refresh_nickmarker(root);
  <?php echo $prefix; ?>refresh_clock(root);
}

/* scroll down from the posted message height */
function <?php echo $prefix; ?>scrolldown(id)
{
  var elttoscroll = document.getElementById(id);
  document.getElementById('<?php echo $prefix; ?>chat').scrollTop += elttoscroll.offsetHeight+2;
}

/* apply nicknames color to the root childs */
function <?php echo $prefix; ?>colorizeNicks(root)
{
  for(var i = 0; i < <?php echo $prefix; ?>nicklist.length; i++)
  {
    var cur_nick = <?php echo $prefix; ?>nicklist[i];
    var cur_color = <?php echo $prefix; ?>getAndAssignNickColor(cur_nick);
    <?php echo $prefix; ?>applyNickColor(root, cur_nick, cur_color);
  }
}

function <?php echo $prefix; ?>reloadColorList()
{
  <?php echo $prefix; ?>colorlist = Array(
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
}

/* get the corresponding nickname color */
function <?php echo $prefix; ?>getAndAssignNickColor(nick)
{
  /* check the nickname is colorized or not */
  var allready_colorized = false;
  var nickcolor = '';
  for(var j = 0; j < <?php echo $prefix; ?>nickcolor.length; j++)
  {
    if (<?php echo $prefix; ?>nickcolor[j][0] == nick)
    {
      allready_colorized = true;
      nickcolor = <?php echo $prefix; ?>nickcolor[j][1];
    }
  }
  if (!allready_colorized)
  {
    /* reload the color stack if it's empty */
    if (<?php echo $prefix; ?>colorlist.length == 0) <?php echo $prefix; ?>reloadColorList();
    /* take the next color from the list and colorize this nickname */
    var cid = Math.round(Math.random()*(<?php echo $prefix; ?>colorlist.length-1));
    nickcolor = <?php echo $prefix; ?>colorlist[cid];
    <?php echo $prefix; ?>colorlist.splice(cid,1);
    <?php echo $prefix; ?>nickcolor.push(new Array(nick, nickcolor));
  }
  return nickcolor;
}

function <?php echo $prefix; ?>applyNickColor(root, nick, color)
{
  var nicktochange = getElementsByClassName(root, '<?php echo $prefix; ?>nick_'+ hex_md5(nick), '')
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
function <?php echo $prefix; ?>nickmarker_swap()
{
  if (<?php echo $prefix; ?>nickmarker)
  {
    <?php echo $prefix; ?>nickmarker = false;
  }
  else
  {
    <?php echo $prefix; ?>nickmarker = true;
  }
  <?php echo $prefix; ?>refresh_nickmarker()
  setCookie('<?php echo $prefix; ?>nickmarker', <?php echo $prefix; ?>nickmarker);
}
function <?php echo $prefix; ?>refresh_nickmarker( root )
{
  var nickmarker_icon = document.getElementById('<?php echo $prefix; ?>nickmarker');
  if (!root) root = document.getElementById('<?php echo $prefix; ?>chat');
  if (<?php echo $prefix; ?>nickmarker)
  {
    nickmarker_icon.src   = "<?php echo $rootpath; ?>/data/public/images/color-on.gif";
    nickmarker_icon.alt   = "Hide nickname marker";
    nickmarker_icon.title = "Hide nickname marker";
    <?php echo $prefix; ?>colorizeNicks(root);
    <?php echo $prefix; ?>colorizeNicks(document.getElementById('<?php echo $prefix; ?>online'));
  }
  else
  {
    nickmarker_icon.src = "<?php echo $rootpath; ?>/data/public/images/color-off.gif";
    nickmarker_icon.alt   = "Show nickname marker";
    nickmarker_icon.title = "Show nickname marker";
    var elts = getElementsByClassName(root, '<?php echo $prefix; ?>nickmarker', '');
    for(var i = 0; elts.length > i; i++)
    {
      /* this is not supported in konqueror =>>>  elts[i].removeAttribute('style');*/
      elts[i].style.color = '';
    }
    var elts = getElementsByClassName(document.getElementById('<?php echo $prefix; ?>online'), '<?php echo $prefix; ?>nickmarker', '');
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
function <?php echo $prefix; ?>clock_swap()
{
  if (<?php echo $prefix; ?>clock)
  {
    <?php echo $prefix; ?>clock = false;
  }
  else
  {
    <?php echo $prefix; ?>clock = true;
  }
  <?php echo $prefix; ?>refresh_clock()
  setCookie('<?php echo $prefix; ?>clock', <?php echo $prefix; ?>clock);
}
function <?php echo $prefix; ?>refresh_clock( root )
{
  var clock_icon = document.getElementById('<?php echo $prefix; ?>clock');
  if (!root) root = document.getElementById('<?php echo $prefix; ?>chat');
  if (<?php echo $prefix; ?>clock)
  {
    clock_icon.src   = "<?php echo $rootpath; ?>/data/public/images/clock-on.gif";
    clock_icon.alt   = "Hide date/hour";
    clock_icon.title = "Hide date/hour";
    showClass(root, '<?php echo $prefix; ?>date', '<?php echo $prefix; ?>invisible', true);
    showClass(root, '<?php echo $prefix; ?>heure', '<?php echo $prefix; ?>invisible', true);
  }
  else
  {
    clock_icon.src = "<?php echo $rootpath; ?>/data/public/images/clock-off.gif";
    clock_icon.alt   = "Show date/hour";
    clock_icon.title = "Show date/hour";
    showClass(root, '<?php echo $prefix; ?>date', '<?php echo $prefix; ?>invisible', false);
    showClass(root, '<?php echo $prefix; ?>heure', '<?php echo $prefix; ?>invisible', false);
  }
  /* browser automaticaly scroll up misteriously when showing the dates */
  document.getElementById('<?php echo $prefix; ?>chat').scrollTop += 30;
}

/**
 * Connect/disconnect button
 */
var <?php echo $prefix; ?>login_status = false;
function <?php echo $prefix; ?>connect_disconnect()
{
  if (<?php echo $prefix; ?>login_status)
  {
    <?php echo $prefix; ?>handleRequest('/quit ' + <?php echo $prefix; ?>clientid);
    <?php echo $prefix; ?>login_status = false;
    <?php echo $prefix; ?>clearNickList();
    <?php echo $prefix; ?>clearMessages();
  }
  else
  {
    <?php echo $prefix; ?>handleRequest('/connect ' + <?php echo $prefix; ?>clientid);
    <?php echo $prefix; ?>login_status = true;
    <?php echo $prefix; ?>updateNickList();
  }
  <?php echo $prefix; ?>refresh_loginlogout()
}
function <?php echo $prefix; ?>refresh_loginlogout()
{
  var loginlogout_icon = document.getElementById('<?php echo $prefix; ?>loginlogout');
  if (<?php echo $prefix; ?>login_status)
  {
    loginlogout_icon.src   = "<?php echo $rootpath; ?>/data/public/images/logout.gif";
    loginlogout_icon.alt   = "Disconnect";
    loginlogout_icon.title = "Disconnect";
  }
  else
  {
    loginlogout_icon.src = "<?php echo $rootpath; ?>/data/public/images/login.gif";
    loginlogout_icon.alt   = "Connect";
    loginlogout_icon.title = "Connect";
  }
}


/**
 * Minimize/Maximized the chat zone
 */
var <?php echo $prefix; ?>minmax_status = <?php if ($start_minimized) { ?>true<?php } else { ?>false<?php } ?>;
var cookie = getCookie('<?php echo $prefix; ?>minmax_status');
if (cookie != null) var <?php echo $prefix; ?>minmax_status = (cookie == 'true');
function <?php echo $prefix; ?>swap_minimize_maximize()
{
  if (<?php echo $prefix; ?>minmax_status)
  {
    <?php echo $prefix; ?>minmax_status = false;
  }
  else
  {
    <?php echo $prefix; ?>minmax_status = true;
  }
  setCookie('<?php echo $prefix; ?>minmax_status', <?php echo $prefix; ?>minmax_status);
  <?php echo $prefix; ?>refresh_minimize_maximize();
}
function <?php echo $prefix; ?>refresh_minimize_maximize()
{
  var content = document.getElementById('<?php echo $prefix; ?>content_expandable');
  var btn = document.getElementById('<?php echo $prefix; ?>minmax');
  if (<?php echo $prefix; ?>minmax_status)
  {
    btn.src = "<?php echo $rootpath; ?>/data/public/images/maximize.gif";
    btn.alt = "Maximize"; btn.title = btn.alt;
    content.style.display = 'none';
  }
  else
  {
    btn.src = "<?php echo $rootpath; ?>/data/public/images/minimize.gif";
    btn.alt = "Minimize"; btn.title = btn.alt;
    content.style.display = 'block';
  }
}

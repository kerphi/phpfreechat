
document.body.onunload = onunloadCallback_content;
document.getElementById('~[$prefix]~words').onkeydown = onkeydownCallback_words;
document.getElementById('~[$prefix]~words').onfocus = onfocusCallback_words;
document.getElementById('~[$prefix]~handle').onkeydown = onkeydownCallback_handle;
document.getElementById('~[$prefix]~handle').onchange = onchangeCallback_handle;
document.getElementById('~[$prefix]~container').onmouseup = onmouseupCallback_container;

function onmouseupCallback_container(e)
{
  var w = document.getElementById('~[$prefix]~words');
  w.focus();
}

function onunloadCallback_content(e)
{
  if (!~[$prefix]~login_status) return false;
  ~[$prefix]~handleRequest('/quit '+ ~[$prefix]~clientid );
}

function onfocusCallback_words(e)
{
  var h = document.getElementById('~[$prefix]~handle');
  if (h.value == '')
    h.focus();
}

function onkeydownCallback_words(e)
{
  if (!~[$prefix]~login_status) return false;
  ~[$prefix]~ClearError(Array('~[$prefix]~words'));
  if (!e) var e = window.event;
  var code = e.keyCode;
  if (code == 13) /* enter key */
  {
    var w = document.getElementById('~[$prefix]~words');
    var wval = w.value;

    re = new RegExp("^(\/[a-z]+)( (.*)|)");
    if (wval.match(re))
    {
      /* a user command */
      wval = wval.replace(re, '$1 '+ ~[$prefix]~clientid +' $2');
      ~[$prefix]~handleRequest(wval.substr(0,~[$max_text_len]~ + ~[$prefix]~clientid.length));
    }
    else
    {
      /* a classic 'send' command*/
      ~[$prefix]~handleRequest('/send ' + ~[$prefix]~clientid + ' ' + wval.substr(0,~[$max_text_len]~));
    }
    w.value = '';
    return false;
  }
  else if (code == 39) /* right direction */
  {
    var w = document.getElementById('~[$prefix]~words');
    var nick_src = w.value.substring(w.value.lastIndexOf(' ')+1,w.value.length);
    if (nick_src != '')
    {
      var ul_online = document.getElementById('~[$prefix]~online').firstChild;
      for (var i=0; i<ul_online.childNodes.length; i++)
      {
	var nick = ul_online.childNodes[i].innerHTML;
	if (nick.indexOf(nick_src) == 0)
	  w.value = w.value.replace(nick_src, nick);
      }
    }
  }
  else
  {
  }
}

var nick_changed = false;
function onkeydownCallback_handle(e)
{
  if (!~[$prefix]~login_status) return false;
  ~[$prefix]~ClearError(Array('~[$prefix]~handle'));
  if (!e) var e = window.event;
  var code = e.keyCode;
  if (code == 13)
  {
    nick_changed = false;
    var h = document.getElementById('~[$prefix]~handle').value;
    ~[$prefix]~handleRequest('/nick '+ ~[$prefix]~clientid + ' ' + h.substr(0,~[$max_nick_len]~) );
  }  
  else
    nick_changed = true;
}
function onchangeCallback_handle(e)
{
  if (!~[$prefix]~login_status) return false;
  if (nick_changed)
  {
    nick_changed = false;
    ~[$prefix]~ClearError(Array('~[$prefix]~handle'));
    var h = document.getElementById('~[$prefix]~handle').value;
    ~[$prefix]~handleRequest('/nick '+ ~[$prefix]~clientid + ' ' + h.substr(0,~[$max_nick_len]~) );
  }
}

/* preload smileys */
preloadImages(
  ~[foreach from=$smileys key=s_file item=s_str]~
   '~[$s_file]~',
  ~[/foreach]~
  ''
);

~[if $active]~
  ~[$prefix]~connect_disconnect();
~[/if]~

~[$prefix]~refresh_loginlogout();
~[$prefix]~refresh_nickmarker();
~[$prefix]~refresh_clock();
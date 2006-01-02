
document.body.onunload = onunloadCallback_content;
document.getElementById('~[$prefix]~words').onkeydown = onkeydownCallback_words;
document.getElementById('~[$prefix]~words').onfocus = onfocusCallback_words;
document.getElementById('~[$prefix]~handle').onkeydown = onkeydownCallback_handle;
document.getElementById('~[$prefix]~handle').onchange = onchangeCallback_handle;

function onunloadCallback_content(e)
{
  ~[$prefix]~handleRequest('/quit' );
}

function onfocusCallback_words(e)
{
  var h = document.getElementById('~[$prefix]~handle');
  if (h.value == '')
    h.focus();
}

function onkeydownCallback_words(e)
{
  ~[$prefix]~ClearError(Array('~[$prefix]~words'));
  if (!e) var e = window.event;
  var code = e.keyCode;
  if (code == 13) /* enter key */
  {
    var w = document.getElementById('~[$prefix]~words');
    ~[$prefix]~handleRequest(w.value.substr(0,~[$max_text_len]~));
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
	var pseudo = ul_online.childNodes[i].innerHTML;
	if (pseudo.indexOf(nick_src) == 0)
	  w.value = w.value.replace(nick_src, pseudo);
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
    ~[$prefix]~ClearError(Array('~[$prefix]~handle'));
  if (!e) var e = window.event;
  var code = e.keyCode;
  if (code == 13)
  {
    nick_changed = false;
    var h = document.getElementById('~[$prefix]~handle').value;
    ~[$prefix]~handleRequest('/nick '+ h.substr(0,~[$max_nick_len]~) );
  }  
  else
    nick_changed = true;
}
function onchangeCallback_handle(e)
{
  if (nick_changed)
  {
    nick_changed = false;
    ~[$prefix]~ClearError(Array('~[$prefix]~handle'));
    var h = document.getElementById('~[$prefix]~handle').value;
    ~[$prefix]~handleRequest('/nick '+ h.substr(0,~[$max_nick_len]~) );
  }
}

~[if $connect]~
~[$prefix]~handleRequest('/connect');
~[/if]~
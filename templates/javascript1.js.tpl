/* define the JS variable used to store timer and nicknames list */
var ~[$prefix]~timeout;
var ~[$prefix]~nicklist = Array();
/* unique client id for each windows used to identify a open window
   this id is passed every time the JS communicate with server */
var ~[$prefix]~clientid = '~[$clientid]~';

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
  var node = msgdiv.firstChild;
  var nodetmp;
  while (node!=null) {
    nodetmp = node.nextSibling;
    msgdiv.removeChild(node);
    if (nodetmp) node = nodetmp.nextSibling; else node = null;
  }
}

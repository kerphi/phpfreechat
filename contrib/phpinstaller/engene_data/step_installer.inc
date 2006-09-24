<?php
$content = <<<HTML
<h2> Installing... </h2>
<script type="text/javascript">
function updatepb(num,message){
	if(num>100){num=100;}
	if(num<0){num=0;}
	document.getElementById("inner").style.width = num+"%";
	document.getElementById("outer").style.backgroundImage = "";
	addMessage(message);
}
function updatepb_unknown(message){
	document.getElementById("inner").style.width = "0";
	document.getElementById("outer").style.backgroundImage = "url(?file=unknown)";
	addMessage(message);
}
function addMessage(message){
	var content = document.getElementById("install-content");
	var newdiv = document.createElement("div");
	newdiv.innerHTML = message;
	content.appendChild(newdiv);
	content.scrollTop = content.scrollHeight;
}
</script>
<div class="clr"> </div>         

<div style="border:inset 1px #666;width:100%;" id="outer"><div id="inner" style="background-color:#EE3333;width:0;height:14px;"></div></div>
<div style="border:inset 1px #666;width:100%;overflow:auto;height:500px;text-align:left;"><pre id="install-content">

</pre></div>
<div class="clr"> </div>
<div id="break"> </div>
</div>
HTML;
?>
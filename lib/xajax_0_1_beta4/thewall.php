<?php
// thewall.php demonstrates a xajax implementation of a graffiti wall
// using xajax version 0.1 beta4
// http://xajax.sourceforge.net

require ("xajax.inc.php");

if (!defined ('MAX_SCRIBBLES'))
{
	define ('MAX_SCRIBBLES', 5);
}

if (!defined ('DATA_FILE'))
{
	define ('DATA_FILE', "thewall.dta");
}

class graffiti
{
	var $html;
	var $isValid = false;
	
	function graffiti($sHandle, $sWords)
	{
		if (trim($sHandle) == "" || trim($sWords) == "")
		{
			return;
		}
		$this->html  = "\n<div style=\"font-weight: bold;text-align:".$this->getRandomAlignment();
		$this->html .= ";color:".$this->getRandomColor().";\">";
		$this->html .= "<span style=\"font-size:".$this->getRandomFontSize()."%;\">";
		$this->html .= strip_tags(stripslashes($sWords));
		$this->html .= "</span><br/><span style=\"font-size: small;\">";
		$this->html .= " ~ ".strip_tags(stripslashes($sHandle))." ".date("m/d/Y H:i:s")."</span></div>";
		
		$this->isValid = true;
	}
	
	function getRandomFontSize()
	{
		srand((double)microtime()*1000003);
		return rand(100,300);
	}
	
	function getRandomColor()
	{
		$sColor = "rgb(";
		srand((double)microtime()*1000003);
		$sColor .= rand(0,255).",";
		srand((double)microtime()*1000003);
		$sColor .= rand(0,255).",";
		$sColor .= rand(0,255).")";
		
		return $sColor;
	}
	
	function getRandomAlignment()
	{
		$sAlign = "";
		srand((double)microtime()*1000003);
		$textAlign = rand(0,2);
		switch($textAlign)
		{
			case 0: $sAlign = "left"; break;
			case 1: $sAlign = "right"; break;
			case 2: $sAlign = "center"; break;
			
		}
		return $sAlign;
	}
	
	function save()
	{
		if ($this->isValid)
		{
			$rFile = fopen(DATA_FILE,"a+");
			fwrite($rFile, $this->html);
			fclose($rFile);
			return null;
		}
		else
		{
			return "Please supply both a handle and some graffiti to scribble on the wall.";
		}
	}
}

function scribble($sHandle,$sWords)
{
	$objResponse = new xajaxResponse();
	
	$objGraffiti = new graffiti($sHandle,$sWords);
	$sErrMsg = $objGraffiti->save();
	if (!$sErrMsg)
	{
		$objResponse->addScript("xajax_updateWall();");
		$objResponse->addClear("words","value");
	}
	else
		$objResponse->addAlert($sErrMsg);
	
	return $objResponse->getXML();
}

function updateWall()
{
	$objResponse = new xajaxResponse();
	
	$aFile = file(DATA_FILE);
	
	$sHtmlSave = implode("\n",array_slice($aFile, -MAX_SCRIBBLES));
	$sHtmlSave=str_replace("\n\n","\n",$sHtmlSave);
	$rFile = fopen(DATA_FILE,"w+");
	fwrite($rFile, $sHtmlSave);
	fclose($rFile);
	
	$sHtml = implode("\n",array_reverse(array_slice($aFile, -MAX_SCRIBBLES)));
	
	$objResponse->addAssign("theWall","innerHTML",$sHtml);

	return $objResponse->getXML();
}

$xajax = new xajax();
//$xajax->debugOn();
$xajax->registerFunction("scribble");
$xajax->registerFunction("updateWall");
$xajax->processRequests();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
	<head>
		<title>The Graffiti Wall</title>
		<?php $xajax->printJavascript(); ?>
		<script>
		function update()
		{
			xajax_updateWall();
			setTimeout("update()", 30000);
		}
		</script>
		<style type="text/css">
		div.label{
			clear: both;
			float:left;
			width:60px;
			text-align:right;
			font-size: small;
		}
		#handle{
			font-size: x-small;
			width: 100px;
		}
		#words{
			font-size: x-small;
			width: 400px;
		}
		#post{
			font-size: small;
			margin-left: 390px;
		}
		#theWall{
			background-image: url('brick.jpg');
			height: 300px;
			padding: 50px;
			border: 3px outset black;
			overflow: auto;
		}
		.notice{
			font-size: small;
		}
		</style>
	</head>
	<body>
		<form>
			<div class="label">Handle:</div><input id="handle" type="text" /><div></div>
			<div class="label">Graffiti:</div><input id="words" type="text" maxlength="75"/><div></div>
			<input id="post" type="submit" value="scribble" onclick="xajax_scribble(document.getElementById('handle').value,document.getElementById('words').value);return false;" />
		</form>
		<div class="notice">To see xajax's UTF-8 support, try posting words in other languages.  You can copy and paste from <a href="http://www.unicode.org/iuc/iuc10/x-utf8.html" target="_new">here</a></div>
		<div id="theWall">
		</div>
		<div style="text-align:center;">
		powered by <a href="http://xajax.sourceforge.net">xajax</a>
		</div>
		<script>
			update();
		</script>
	</body>
</html>

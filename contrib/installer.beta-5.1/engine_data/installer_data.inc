<?php
#{CLASS_HEADERS}
define('HTTP_FILE',('http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF']));
define('PAD_LENGTH',66);

function get_install_method(){
	$method = 'write_'.gpv('method');
	return new $method();
}


function unlock_buttons($onend=false){
	if($onend){
		register_shutdown_function(__FUNCTION__);
		return;
	}
	echo "\n";
	echo '<script type="text/javascript">if(document.getElementById("stepNext")) document.getElementById("stepNext").disabled=false;if(document.getElementById("stepBack")) document.getElementById("stepBack").disabled=false;</script>';
	echo "\n";
}

$step = isset($_REQUEST['step'])?$_REQUEST['step']:1;


//if(isset($_REQUEST['stepNext'])){echo "$step++=";$step++;echo "$step\n";}
//else if(isset($_REQUEST['stepBack'])){echo "$step--=";$step--;echo "$step\n";}

if(isset($_REQUEST['stepNext'])) $step++;
else if(isset($_REQUEST['stepBack'])) $step--;



$defaults = array();
$hidden = array();
$pages = array();
$uses=array();
//constant
#{PAGES}
//end constant
//235
$defaults['path'] = getcwd().'/PPATH';

function gpv($name,$default=''){//getPostValue
	if(isset($_REQUEST[$name])){
		return stripslashes($_REQUEST[$name]);
	}else{
		global $defaults;
		if($default) return $default;
		else if($defaults[$name]) return $defaults[$name];
		else return $default;
	}
}
foreach($defaults as $dname=>$dvalue){
	$hidden[] = $dname;
	if(!isset($_REQUEST[$dname])) $_REQUEST[$dname]=addslashes($dvalue);
}
//var_export($_REQUEST['page']);
//var_export($defaults);

if(isset($_GET['file'])){
	switch($_GET['file']){
		#{METAFILES}
	}
	die;
}
$HTTP_FILE=HTTP_FILE;
echo <<<HTML
<?xml version="1.0" encoding="iso-8859-1"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en-us" lang="en-us">
<head>
<title>Program - Web Installer</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<link rel="shortcut icon" href="../../images/favicon.ico" />
<link rel="stylesheet" href="$HTTP_FILE?file=ss" type="text/css" />
<script type="text/javascript">
//<![CDATA[
var checkobj
function agreesubmit(el){
	checkobj=el
	if (document.all||document.getElementById){
		//for (i=0;i<checkobj.form.length;i++){  //hunt down submit button
		//var tempobj=checkobj.form.elements[i]
		//if(tempobj.type.toLowerCase()=="submit")
		//tempobj.disabled=!checkobj.checked
		//}
		document.getElementById('stepNext').disabled=!checkobj.checked;
	}
}

function defaultagree(el){
	if (!document.all&&!document.getElementById){
		if (window.checkobj&&checkobj.checked)
		return true
		else{
			alert("Please read/accept license to continue installation")
			return false
		}
	}
}
//]]>
</script>
</head>
<body>
<form action="$HTTP_FILE" method="post" id="installform">
<div id="wrapper">
<div id="header">
<div id="headertext">Install PROGRAM</div>
</div>
</div>
<div id="ctr" class="install">
<div id="stepbar">
HTML;
foreach($pages as $name=>$value){
	if(isset($value['hr']) && $value['hr']){echo '<hr />';}
	echo '<div class="step-'.($step==$name?'on':'off').'">'.$value['title'].'</div>';
}
echo '</div>';
echo '<div id="right">';
if(isset($pages[$step]['button_back']) && $pages[$step]['button_back']){
	echo '<div class="far-left"><input name="stepBack" id="stepBack" type="submit" class="button" value="&lt;&lt; Back"';
	if ($step==INSTALLERSTEP) echo ' disabled="disabled"';
	echo ' />&nbsp;</div>';
}
echo '<div class="far-right">';
if(isset($pages[$step]['button_next']) && $pages[$step]['button_next']){
	echo '<input name="stepNext" id="stepNext" type="submit" class="button" value="Next &gt;&gt;" />';
	if(isset($pages[$step]['disabled_buttons']) && $pages[$step]['disabled_buttons']){
		echo '<script type="text/javascript">document.getElementById("stepNext").disabled=true;</script>';
	}
}
echo '</div>'; //far-right
echo '<h1>'.$pages[$step]['title']."</h1>\n";
	
foreach($hidden as $value){
	if(!in_array($value,$uses)){
		echo '<input name="'.$value.'" id="'.$value.'" type="hidden" value="'.gpv($value).'" />'."\n";
	}
}
echo '<input name="step" id="step" type="hidden" value="'.$step.'" />'."\n";
echo '<div class="clr"></div>';
echo $content;
echo '<div class="clr"></div>';
echo '</div>';
echo '<div class="clr"></div>';
echo '</div>';
echo '</form></body></html>';
if($step==INSTALLERSTEP){
	function installwait(){echo str_repeat(' ',2560);}

	function updatepb($num=null,$message='...',$overwrite=false){
		$overwrite = $overwrite?'true':'false';
		$num = min(100,max(0,$num));
		$message = addslashes($message);
		echo "\n";
		if(is_numeric($num)) echo '<script type="text/javascript">updatepb('.$num.',"'.$message.'");</script>';
		else echo '<script type="text/javascript">updatepb(null,"'.$message.'");</script>';
		installwait();
		flush();
	}
	function updatepb_unknown($message='...',$overwrite=false){
		$overwrite = $overwrite?'true':'false';
		$message = addslashes($message);
		echo "\n";
		echo '<script type="text/javascript">updatepb_unknown("'.$message.'");</script>';
		installwait();
		flush();
	}
	function addMessage($message){
		echo '<script type="text/javascript">addMessage("'.addslashes($message).'");</script>';
		installwait();
		flush();
	}
	function step_download($action,&$step,$steptotal,$path){
		if(is_string($action[4])) $action[4]=explode(' ; ',$action[4]);
		foreach ($action[4] as $dlfile){
			if(is_file($dlfile)){
				$fname = $dlfile;
				break;
			}else{
				addMessage("file '$dlfile' does not exist, falling back...");
			}
		}
		if(!$fname){
			addMessage('<b>Could not find an existing file to download...</b>');
			return false;
		}
		$fp2 = fopen($tmpfile=tempnam(dirname(__FILE__), "webinstall"),'w');
		if(!$fp2){
			addMessage('Failed to create file');
			die;
		}
		
		set_time_limit (60);

		$extract_to=$action[3];
		$type=$action[2];
		$length=null;
		addMessage("Transfering File '$fname'...");
		if(!$fp=fopen($fname, "rb")){
			addMessage('<b>Failed to download file...</b>');
			fclose($fp);unlink($tmpfile);
			return false;
		}
		$headers=stream_get_meta_data ($fp);
		$contents = '';
		foreach ($headers['wrapper_data'] as $v){
			if(substr($v,0,14)=='Content-length'){
				$x = explode(' ',$v);
				$length = (int) $x[1];
				break;
			}
		}
		$i = 0;
		$j = 0;
		if(!$length) updatepb_unknown("...size of file is unknown...");
		while (!feof($fp)) {
			$bytes = 1024;
			$x=stream_get_meta_data ($fp);
			fwrite($fp2,fread($fp, $bytes));
			if($length){
				$i+=$bytes;
				$wait = ceil($length/$bytes/50);
				if(!($j++ % $wait)){
					installwait();
					updatepb( (($i/$length)*100) ,'');
				}
			
			}
		}
		
		$class = "extract_$type";
		addMessage("Extracting from $tmpfile... (using class $class)");
		$extract = new $class($tmpfile);
		$extract->extract($path.$extract_to);
		fclose($fp2);
		unlink($tmpfile);
		updatepb(100,$str);
	}
	function step_extract(){
		
	}
		
	$write = get_install_method();
	if(strlen($path=$write->path()) < 9){
		addMessage("Will not install into root directory ($path). Specify another path.");
		die;
	}else{
		addMessage("Writing files and folders to '$path'");
	}
	
	$files = $data[1];//get data from parent script
	$count = $files[3];//total files
	$sizes = $files[2];//file sizes
	
	$sections = count($files);
	$section = 0;
	//array(totalsteps,array(steps,attributes,...),array(steps,attributes,...));
	foreach($files as $_){
		$section++;
		addMessage("Starting Step $section of $sections...");
		$i = 0;
		$arr = $_;
		$size = array_shift($arr);
		foreach($arr as $step){
			$x=$i;
			switch($step[1]){
				case 0:$write->step_dir($step,$i,$size);break;
				case 1:$write->step_file($step,$i,$size);break;
				case 2:step_download($step,$i,$size,$write);break;
			}
			$i=$x+$step[0];
		}
	}
	//run post install stuff
	#{POSTINSTALL}
	unlock_buttons();
	addMessage('DONE COPYING FILES!');
}


?>
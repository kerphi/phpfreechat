<?php
if (version_compare(phpversion(), '5.0.0', '<')){
	die("PHP5 is required for this verson of the installer devkit");
}

define('HTTP_FILE',strtolower(strtok($_SERVER['SERVER_PROTOCOL'], '/')).'://'.$_SERVER['HTTP_HOST']. $_SERVER['PHP_SELF']);
define('VERSION','1.0-rc1');
define('RESULTFILE','installer.php');
include('engine.inc.php');

//Download the installer
if (isset($_REQUEST['download'])) {
	 header("HTTP/1.1 200 OK");
     header("Status: 200 OK");
     header('Content-Type: application/force-download'); 
     header('Content-Disposition: attachment; filename="'.RESULTFILE.'"'); 
     header('Content-Length: '.filesize(RESULTFILE));
     readfile(RESULTFILE); 
     exit(0);
}


function gpv($name,$default=null,$get=true){//getPostValue
	if(isset($_REQUEST[$name])){
		if($get){
			if(is_array($_REQUEST[$name])){
				return antislash($_REQUEST[$name]);
			}else if( ($n=unserialize(urldecode($_REQUEST[$name]))) !==false || $_REQUEST[$name]===urlencode(serialize(false))){
					return $n;
			}else{
				return antislash(antislash($_REQUEST[$name]));
			}
		}else{
			if(is_array($_REQUEST[$name])){
				return urlencode(serialize(antislash($_REQUEST[$name])));
			}else if( ($n=unserialize(urldecode($_REQUEST[$name]))) !==false || $_REQUEST[$name]===urlencode(serialize(false))){
					return urlencode(serialize($n));
			}else{
				return urlencode(serialize($_REQUEST[$name]));
			}
		}
	}else{
		return $get?urlencode(serialize($default)):$default;
	}
}

function antislash($var){
	if(is_string($var)) return stripslashes($var);
	if(is_array($var)){
		foreach($var as $n=>$v){
			$var[$n] = antislash($v);
		}
	}
	return $var;
}

$pages = array(
	array('title'=>'Welcome', 'file'=>'start'),
	array('title'=>'Applictation Name','file'=>'pages'),
	array('title'=>'Applictation Name','file'=>'appname'),
	array('title'=>'Files Setup', 'file'=>'files1'),
	array('title'=>'Ignore', 'file'=>'files2'),
	array('title'=>'Installer Definition' , 'file'=>'build')
);

echo "<?xml version=\"1.0\" encoding=\"iso-8859-1\"?".">";
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Installer Creater</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<link rel="shortcut icon" href="favicon.ico" />
<style>

body {
	margin: 0px;
	background-color: white;
	color : black;
	padding:1em;
}

h1,h2,h3,h4,h5{
	font-weight:bold;
	margin:1px;
	padding:1px;
}
h1{font-size:150%;}
h2{font-size:125%;}
h3{font-size:115%;}
h4{font-size:105%;}
div.margin{margin-left:1em;}
</style>
</head>
<body>
<form name="form" id="form" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
              <?php
			$defaults = array(
				'page'=>(isset($_REQUEST['page'])?$_REQUEST['page']:0),
				'packages'=>array(),
				'step_license'=>'',
				'rootpath'=>dirname(__FILE__),
				'exec'=>'',
				'appname'=>'',
				'appver'=>'',
				'ignore'=>''
				);
			foreach($defaults as $dname=>$dvalue){
				$hidden[] = $dname;
				if(!isset($_REQUEST[$dname])){
					$_REQUEST[$dname] = gpv($dname,$dvalue,true);
				}
			}
			$uses = array('page');
			$totalpages = 5;
			$page = gpv('page');
			
			if(isset($_REQUEST['page_next'])) $page++;
			else if(isset($_REQUEST['page_back'])) $page--;
			
			$button_prev = $page!=0;
			$button_next = $page!=$totalpages;
			if($file=$pages[$page]['file']){
				include("createinstaller/page.$file.php");
			}else{
				echo "Not a valid page (number $page)";
			}
			?>
              <div style="text-align:right;margin-top:0.5em;padding:0.5em;border-top:solid 1px black;"> 
                <?php if($button_next){	?>
                <input type="submit" name="page_next" id="page_next" value="Next" style="float:right;" />
                <?php	} ?>
                <?php if($button_prev){	?>
                <input type="submit" name="page_back" id="page_back" value="Back" style="float:left;" />
                <?php	} ?>
                <input type="hidden" name="page" id="page" value="<?php echo $page; ?>" />
              </div><br />
              <?php
            //echo '<table>';
			foreach($hidden as $value){
				if(!in_array($value,$uses)){
					echo '<input type="hidden" name="'.$value.'" id="'.$value.'" value="'.gpv($value,null,false).'" />';
					//echo "<tr><td>$value</td><td>";
					//var_dump(gpv($value));
					//echo "</td></tr>\n";
				}
			}
            //echo '</table>';
			?>
</form>
</body>
</html>
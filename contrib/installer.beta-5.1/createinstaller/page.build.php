<?php
$phpi = new phpInstaller();
$dataDir = realpath(dirname(__FILE__).'/../engene_data');
$phpi->dataDir($dataDir);
$phpi->appName = gpv('appname');
$phpi->appVersion = gpv('appver');
$phpi->compress = true;
$phpi->ignore[] = '/installer.php';
$phpi->ignore[] = '/phpinstaller/';
$phpi->ignore[] = 'tests';
$phpi->ignore[] = 'hi2';
foreach(explode("\n",gpv('ignore')) as $v){
	if($v=trim($v)) $phpi->ignore[] = $v;
}

$phpi->messageCallback = 'message';
function message($message,$state=null){
	if($state==2) echo '<div style="color:grey;">';
	else echo '<div>';
	
	if($state) echo htmlspecialchars(str_pad($message,74,'.')).'....';
	else echo htmlspecialchars($message);
	
	switch ($state) {
		case 1:echo '[<span style="color:red;">failed</span>]';break;
		case 2:echo '[<span style="font-weight:bold;">ignore</span>]';break;
		case 3:echo '[<span style="color:green;"> file </span>]';break;
		case 4:echo '[<span style="color:green;"> dir  </span>]';break; 
		case 5:echo '[<span style="color:green;"> done </span>]';break; 
	}
	echo '</div>';
}

echo '<div style="height:30em;overflow:auto;border:inset 1px grey;"><pre>';
//template files
		if(!$phpi->addMetaFile('ss','createinstaller/data/installer.css','text/css',$replace)){
			die('Could not add main.css to installer.<br />');
		}

foreach (gpv('packages') as $v){
	var_export($v);
	if(isset($v['url']) && isset($v['path'])){
		//$phpi->generatedata($v['path']);
		$phpi->addPathDownload($v['url'],$v['to']);
	}else if(isset($v['url'])){
		$phpi->addPathDownload($v['url'],$v['to'],$v['type']);
	}else if(isset($v['path'])){
		$phpi->addPath($v['path'],$v['to']);
	}
}
//$phpi->addPath(gpv('rootpath'));
$phpi->addPage('Pre-installation Check',file_get_contents('createinstaller/data/precheck.inc'));

//license page
if(gpv('step_license')){
	if($phpi->addMetaFile('license','createinstaller/data/license.html','text/html')){
		$phpi->addPage('License',file_get_contents('createinstaller/data/license.inc'),array(),array('disabled'=>true));
	}else{
		$phpi->message('The license could not be added. Check the file '.$lPath.'.');
	}
}
if(!$phpi->addMetaFile('unknown','createinstaller/data/unknown.gif','image/gif')){
	$phpi->message('The license could not be added. Check the file '.$lPath.'.');
}
if(!$phpi->addMetaFile('hb','createinstaller/data/installer.jpg','image/jpeg')){
	$phpi->message('The license could not be added. Check the file '.$lPath.'.');
}

$phpi->addInstallerPages();
$phpi->generate(RESULTFILE);
echo '<a href="?download">download</a>';
echo '</pre></div>';
?>
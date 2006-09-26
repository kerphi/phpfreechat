<?php
include('../engine.inc.php');
$phpi = new phpInstaller();

$dataDir = realpath(dirname(__FILE__).'/../engine_data');
$phpi->dataDir($dataDir);

$phpi->appName = 'Installer Dev Kit';

$phpi->appVersion = '1.0 RC1';

$phpi->compress = true;

$phpi->ignore[] = '/installer.php';
$phpi->ignore[] = '/phpinstaller/';
$phpi->ignore[] = 'tests';
$phpi->ignore[] = 'fanwork';

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
if(!$phpi->addMetaFile('ss','../createinstaller/data/installer.css','text/css',$replace)){
	die('Could not add main.css to installer.<br />');
}


$phpi->addPath(dirname(__FILE__).'/..');//add the directory below this one
$phpi->addPage('Pre-installation Check',file_get_contents('../createinstaller/data/precheck.inc'));

//license page
if($phpi->addMetaFile('license','../createinstaller/data/license.html','text/html')){
	$phpi->addPage('License',file_get_contents('../createinstaller/data/license.inc'),array(),array('disabled'=>true));
}else{
	$phpi->message('The license could not be added. Check the file '.$lPath.'.');
	die('</pre></div>');
}
	
if(!$phpi->addMetaFile('unknown','../createinstaller/data/unknown.gif','image/gif')){
	$phpi->message('The license could not be added. Check the file '.$lPath.'.');
	die('</pre></div>');
}
if(!$phpi->addMetaFile('hb','../createinstaller/data/installer.jpg','image/jpeg')){
	$phpi->message('The license could not be added. Check the file '.$lPath.'.');
	die('</pre></div>');
}

$phpi->addInstallerPages();
$phpi->generate('../tests/installer.beta-5.php');
$phpi->generateTarGz('../tests/installer.beta-5.tar.gz');

echo '</pre></div>';
?>
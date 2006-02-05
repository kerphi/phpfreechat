<?php
include('engene.inc.php');
$phpi = new phpInstaller();
$dataDir = realpath(dirname(__FILE__).'/../engene_data');
$phpi->dataDir($dataDir);
$phpi->appName = gpv('appname');

$phpi->ignore[] = 'test.';

$phpi->addPath(gpv('rootpath'));
$phpi->addPage('Pre-installation Check',file_get_contents('createinstaller/installer_pages/1.txt'));
if(gpv('step_license')){
	$lPath = $dataDir.'/license.html';
	if($phpi->addMetaFile('LICENSE',$lPath,'text/html')){
		$phpi->addPage('License',file_get_contents('createinstaller/installer_pages/2.txt'));
	}else{
		$phpi->message('The license could not be added. Check the file <var>'.$lPath.'</var>.');
	}
}

$phpi->generate();

?>
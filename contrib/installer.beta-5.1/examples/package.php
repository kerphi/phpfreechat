<?php
/////I would recommend using customoutput.php instead
///// Why? I don't know.

include('../engine.inc.php');
$phpi = new phpInstaller();

$dataDir = realpath(dirname(__FILE__).'/../engine_data');
$phpi->dataDir($dataDir);

$phpi->appName = 'PHP Installer Generator';

$phpi->appVersion = 'beta-6';

$phpi->compress = true;

$phpi->ignore[] = '/installer.php';
$phpi->ignore[] = '/phpinstaller/';
$phpi->ignore[] = '/fanwork/';
$phpi->ignore[] = 'tests';

echo '<pre>';

//template files
if(!$phpi->addMetaFile('ss','../createinstaller/data/installer.css','text/css',$replace)){
	$phpi->message('Could not add installer.css to installer.');
	die('</pre>');
}


$phpi->addPath(realpath(dirname(__FILE__)."/.."));


$phpi->addPage('Pre-installation Check',file_get_contents('../createinstaller/data/precheck.inc'));

//generate the license page
//first try to add the license file, than the license page
if($phpi->addMetaFile('license','../createinstaller/data/license.html','text/html')){
	$phpi->addPage('License',file_get_contents('../createinstaller/data/license.inc'),array(),array('disabled'=>true));
}else{
	$phpi->message('The license could not be added.');
	die('</pre>');
}

//add the unknown image
if(!$phpi->addMetaFile('unknown','../createinstaller/data/unknown.gif','image/gif')){
	$phpi->message('The unknown status image could not be added.');
	die('</pre>');
}
if(!$phpi->addMetaFile('hb','../createinstaller/data/installer.jpg','image/jpeg')){
	$phpi->message('An image installer.jpg could not be added.');
	die('</pre>');
}

$phpi->addInstallerPages();
$phpi->generate('../tests/installer.beta-5.1.php');
$phpi->generateTarGz('../tests/installer.beta-5.1.tar.gz');

echo '</pre>';
?>
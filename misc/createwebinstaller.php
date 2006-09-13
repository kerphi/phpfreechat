<?php

$version = isset($_SERVER["argv"][1]) ? $_SERVER["argv"][1] : file_get_contents(dirname(__FILE__)."/../version");
$archivename = 'phpfreechat-'.$version.'-setup.php';
$pfcpath = dirname(__FILE__).'/phpfreechat-'.$version;
if (!file_exists($pfcpath)) die("Dont find the directory $pfcpath");

include(dirname(__FILE__).'/../contrib/pfcInstaller/engene.inc.php');
$phpi = new phpInstaller();
$phpi->dataDir(realpath(dirname(__FILE__).'/../contrib/pfcInstaller/engene_data'));
$phpi->appName = 'phpFreeChat';
$phpi->appVersion = $version;
$phpi->ignore[] = '.svn';
$phpi->addInstallerPage();
$phpi->addPath($pfcpath);
$phpi->generate($archivename);

?>

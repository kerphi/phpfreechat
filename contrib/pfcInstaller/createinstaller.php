<?php

// on desactive le timeout car se script met bcp de temps a s'executer
ini_set('max_execution_time', -1);
// on desactive la limite de memoire car ce scrite peut manger bcp de memoire
ini_set('memory_limit', -1);

if (version_compare(phpversion(), '5.0.0', '<')){
	die("PHP5 is required for this verson of the installer devkit");
}
function gpv($name){//getPostValue
	if(isset($_REQUEST[$name])){
		return stripslashes($_REQUEST[$name]);
	}else{
		return null;
	}
}
echo "<?xml version=\"1.0\" encoding=\"iso-8859-1\"?".">";
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Installer Creater</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<link rel="shortcut icon" href="favicon.ico" />
<style>/* $Id: install.css,v 1.1 2004/03/01 20:51:26 diamondmagic Exp $
*
* CSS for administration
* @package Mambo Open Source
* @Copyright (C) 2000 - 2004 Miro International Pty Ltd
* @ All rights reserved
* @ Mambo Open Source is Free Software
* @ Released under GNU/GPL License : http://www.gnu.org/copyleft/gpl.html
* @version $Revision: 1.1 $
*/

BODY {
        margin: 0px 0px 0px 0px;
        background-color: #FFFFFF;
        color : Black;
        padding:4em;
}

.contentbackgr {
	background-image:  url(03_content_backgr.png);
	background-repeat: repeat-x;
	background-position: left top;
}

a {
	color : #FF9900;
	font-size : 11px;
	font-family: Verdana, Arial, Helvetica, sans-serif;
	font-weight : normal;
	text-decoration : none;
}

a:hover {
	color : #999999;
	font-size : 11px;
	font-family: Verdana, Arial, Helvetica, sans-serif;
	font-weight : normal;
	text-decoration : underline;
}

a:active {
	color : #FF9900;
	font-size : 11px;
	font-family: Verdana, Arial, Helvetica, sans-serif;
	font-weight : normal;
	text-decoration : underline;
}

td {
	color : #000000;
	/* background-color : #CCCCCC;*/
	font-family : Arial, Helvetica, sans-serif;
	font-size : 11px;
}

.button {
	border-style : solid;
	border-top : solid 1px #d5d5d5;
	border-right : solid 1px #808080;
	border-bottom : solid 1px #808080;
	border-left : solid 1px #d5d5d5;
	/* color : #000000; */
	font-family : Arial, Helvetica, sans-serif;
	font-style : normal;
	font-weight : bold;
	font-size : 11px;
}
 
.inputbox {
	border : 1px solid #999999;
	color : #ff6600;
	background : #FFFFFF;
	font-family : Courier, Helvetica, sans-serif;
	font-size : 12px;
	font-weight : normal;
	z-index : -3;
}

.small {
	color : #333333;
	font-family : Arial, Helvetica, sans-serif;
	font-size : 10px;
	font-weight : normal;
	text-decoration : none;
}

.smallgrey {
        color : #808080;
        font-family : Arial, Helvetica, sans-serif;
        font-size : 10px;
        font-weight : normal;
}

.error {
	color : #c40000;
	font-family : Arial, Helvetica, sans-serif;
	font-size : 12px;
	font-weight : bold;
}

.heading {
	font-family: Trebuchet MS, Verdana, Arial, Helvetica, sans-serif;
	font-size: 14px;
	font-weight: bold;
 	color: #6666FF;
	border-bottom: 1px dashed #6666FF;
}
	
select.options,
input.options {
	font-family : Arial, Helvetica, sans-serif;
	font-size: 8pt;
	font-weight: normal;
	border: 1px solid #999999;
}

/* standard form style table */
table.adminform {
	background-color: #f2f2f2;
	border: solid 1px #d5d5d5;
}

table.adminform th {
	background-color: #999999;
	color: #FFFFFF;
	font-family : Verdana, Arial, Helvetica, sans-serif;
	font-size: 10pt;
	text-align: left;
}

table.adminform th.info {
	background-color: #e5e5e5;
	color: #808080;
	font-family : Verdana, Arial, Helvetica, sans-serif;
	font-size: 10pt;
	border-bottom: solid 1px #808080;
	text-align: left;
}
table.adminform td {
	font-family: Verdana,geneva,arial,helvetica,sans-serif;
	font-size: 9pt;
}

table.adminform td.editor {
	color: #000000;
	font-family : Verdana, Arial, Helvetica, sans-serif;
	font-size: 9pt;
}

form {
	margin: 0px 0px 0px 0px;
}


.dottedline {
	border-top-width: 1px;
	border-right-width: 1px;
	border-bottom-width: 1px;
	border-left-width: 1px;
	border-bottom-style: dashed;
	border-top-color: #CCCCCC;
	border-right-color: #CCCCCC;
	border-bottom-color: #CCCCCC;
	border-left-color: #CCCCCC;
}

.installheader {

	color : #003399;
	font-family : "Trebuchet MS", Arial, Helvetica, sans-serif;
	font-size : 24px;
	font-weight: normal;
}

textarea {
	color : #0000dd;
	font-family : Courier;
	font-size : 11px;
	font-weight: normal;
	border-top-width: 1px;
	border-right-width: 1px;
	border-bottom-width: 1px;
	border-left-width: 1px;
}
.step-prev {
	font-size: 12px;
	color: #666666;
	font-weight: bolder;
}
.step-curr {
	font-size: 16px;
	font-weight: bolder;
	color: #000000;
}
.step-next {
	font-size: 12px;
	font-weight: bold;
	color: #CCCCCC;
}
h1,h2,h3,h4,h5{
	font-weight:bold;
}
h1{
	font-size:150%;
}
h2{
	font-size:125%;
}
h3{
	font-size:115%;
}
h4{
	font-size:105%;
}
div.margin{
	margin-left:1em;
}
</style>
</head>
<body>
<table height="100%" width="100%"><tr><td valign="middle">
<form name="form" id="form" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
        <table width="675" border="0" align="center" cellpadding="0" cellspacing="0">
          <tr> 
            <td width="10" height="10"><img src="/php_installer/test.php?data=top_left_corner.png" width="10" height="10" /></td>
            <td background="/php_installer/test.php?data=top_line.png"></td>
            <td width="10" height="10"><img src="/php_installer/test.php?data=top_right_corner.png" width="10" height="10" /></td>
          </tr>
          <tr> 
            <td rowspan="2" background="/php_installer/test.php?data=left_line.png"></td>
            <td valign="top" style="border:1px solid #000000;background-color:#EEEEEE;"><div style="width:100%;height:360px;overflow:auto;"> 
              <?php
			$defaults = array(
				'page'=>(isset($_REQUEST['page'])?$_REQUEST['page']:0),
				'pages'=>'0',
				'step_license'=>'',
				'rootpath'=>dirname(__FILE__),
				'exec'=>'',
				'appname'=>''
				);
			foreach($defaults as $name=>$value){
				$hidden[] = $name;
				if(!$_REQUEST[$name] && $_REQUEST[$name]!==false){
					$_REQUEST[$name] = addslashes($value);
				}
			}
			$uses = array('page');
			$totalpages = 4;
			$page = (int) isset($_REQUEST['page'])?$_REQUEST['page']:0;
			if($_REQUEST['page_next']){$page++;}else if($_REQUEST['page_back']){$page--;}
			$button_prev = $page!=0;
			$button_next = $page!=$totalpages;
			switch($page){
				case 0:
					include('createinstaller/page0.php');
				break;
				case 1:
					include('createinstaller/page1.php');
				break;
				case 2:
					include('createinstaller/page2.php');
				break;	
				case 3:
					include('createinstaller/page3.php');
				break;	
				case 4:
					include('createinstaller/page4.php');
				break;				
				default:
					echo 'not a page: '.$page;
				break;
			}
			?>
              <div style="text-align:right;"> </div>
            </td>
            <td background="/php_installer/test.php?data=right_line.png"></td>
          </tr>
          <tr> 
            <td valign="top" style="padding:2px;"> <div style="width:100%;> 
              <div style="text-align:right;"> 
                <?php if($button_next){	?>
                <input type="submit" name="page_next" id="page_next" value="Next" style="float:right;" />
                <?php	} ?>
                <?php if($button_prev){	?>
                <input type="submit" name="page_back" id="page_back" value="Back" style="float:left;" />
                <?php	} ?>
                <input type="hidden" name="page" id="page" value="<?php echo $page; ?>" />
              </div>
              <?php
			foreach($hidden as $value){
				if(!in_array($value,$uses)){
					echo '<input type="hidden" name="'.$value.'" id="'.$value.'" value="'.gpv($value).'" />';
					echo "\n";
				}
			}
			?>
            </td>
            <td background="/php_installer/test.php?data=right_line.png"></td>
          </tr>
          <tr> 
            <td width="10" height="10" background="/php_installer/test.php?data=bottom_left_corner.png"></td>
            <td background="/php_installer/test.php?data=bottom_line.png"></td>
            <td width="10" height="10" background="/php_installer/test.php?data=bottom_right_corner.png"></td>
          </tr>
        </table>
</form>
</td></tr></table>
</body>
</html>
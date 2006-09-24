<?php if (version_compare(phpversion(), "5.0.0", "<=")){die("PHP5 is required for this verson of the installer devkit");} ?>
<?php echo "<?xml version=\"1.0\" encoding=\"iso-8859-1\"?".">"; ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Installer Creater</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<link rel="shortcut icon" href="favicon.ico" />
<link rel="stylesheet" href="/php_installer/test.php?data=install.css" type="text/css" />
<style>
h1{
	font-size:15px;
}
</style>
</head>
<body>
<div style="text-align:left;"><h1>Welcome to the File Compiler.</h1></div>
<form enctype="multipart/form-data" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" name="upload" id="upload">
	<input type="hidden" name="MAX_FILE_SIZE" value="30000000" />
	Send this file: <input name="userfile" type="file" />
	<input type="submit" value="Send File" /><br />
	
	PHP Code<input name="type" type="radio" value="php" selected="selected" />
	Raw 64<input name="type" type="radio" value="raw" />
</form>
				<?php
if(@$_FILES){
	$filehandle = fopen($_FILES['userfile']['tmp_name'], "rb");
	if($_REQUEST['type']=='raw'){
		$show = base64_encode(fread($filehandle,filesize($_FILES['userfile']['tmp_name'])));
	}else{
		$data = explode("\n",chunk_split(base64_encode(fread($filehandle,filesize($_FILES['userfile']['tmp_name']))),64,"\n"));
		$show = '$data = "";'."\n";	foreach($data as $value){
			if(strlen($value)){
				$show .= "\$data .= '".$value."';\n";
			}
		}
	}
	echo '<hr /><table><tr><td>lines</td><td><input type="text" value="'.count($data).'" /></td></tr>';
	echo '<tr><td>strcount</td><td><input type="text" value="'.strlen($show).'" /></td></tr>';
	echo '<tr><td colspan="2"><textarea cols="76" rows="15">';
	//echo ascii2ebcdic(implode(file($_FILES['userfile']['tmp_name']),''));
	echo $show;
	echo '</textarea></td></tr></table>';
	
}
?>
</body>
</html>
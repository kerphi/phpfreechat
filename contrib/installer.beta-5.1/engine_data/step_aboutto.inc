<?php
$gpvobj = get_install_method();
$gpvpath = $gpvobj->path();

if(is_dir($gpvobj->is_writable())){
	echo <<<HTML
	<p><b>The directory you chose ($gpvpath) already exists. 
	This isn't a really bad thing, but a few errors may appear because the
	directories all ready exist, and any files in the list below will be overwritten.</b></p>
HTML;
}
echo <<<HTML

<p>Click next to install PROGRAM.</p><p>Please be sure that the folder you wanted to install to, <code>$gpvpath</code>,
is correct.	Be aware that it will overwrite any files with the same name.
No warnings will be thrown during install, so beware. (see below for existing files)</p>
HTML;

$files = $data[1];//get data from parent script

echo "\n<div style=\"border:inset 1px #666;width:100%;text-align:left;height:25em;max-height:25em;overflow:auto;\">\n";
echo '<b>The following files will be written:</b><br />';

foreach($files as $_){if(is_array($_)) foreach($_ as $action){
	if(isset($action[1])){
		switch($action[1]){
		case 1:
			echo $gpvobj->path($action[2]);
			if($gpvobj->exists($action[2])){
				echo ' <b><span style="color:red;">WARNING!</span><br />&nbsp;&gt;&gt;&nbsp;This file exists and will be overwritten!</b><br />';
			}
			echo "<br />\n";
		break;
		case 2:
			if($action[3]=='/') echo "Files in '<i>$action[4]</i>'";
			else echo "Files in '<i>$action[4]</i> to <i>$action[3]</i>'";
			echo "<br />\n";
		break;
		}
	}
}}
echo '</div>';
?>
<?php
$cwd = gpv('path');
$method = gpv('method');

$ftp_h = gpv('ftp_h');
$ftp_u = gpv('ftp_u');
$ftp_p = gpv('ftp_p');

$sftp_h = gpv('sftp_h');
$sftp_u = gpv('sftp_u');
$sftp_p = gpv('sftp_p');

$d_l = $method=='file'?' selected="selected"':'';
$d_f = $method=='ftp'?' selected="selected"':'';
$d_s = $method=='sftp'?' selected="selected"':'';

echo <<<HTML
<h2> Enter the path of your site: </h2>
<div>Type path to install your script. Files will go into this directory, too.
It should not exist, it should be somthing new.</div>
<div class="install-form">
<div class="form-block">
<table>
<tbody>
<tr>
	<td style="width:20%;">Install Method</td><td style="width:80%;">
		<select id="method" name="method" onchange="show(this);">
			<option value="file"$d_l>file://</option>
			<option value="ftp"$d_f>ftp://</option>
			<option value="sftp"$d_s>sftp://</option>
		</select>
	</td>
</tr>
<tr>
	<td style="width:20%;">Install path</td><td style="width:80%;"><input class="inputbox" name="path" id="path" style="width:100%;" value="$cwd" type="text"></td>
</tr>
<tr>
	<td>&nbsp;</td><td class="small" align="center"> e.g. /var/www/html/PPATH </td>
</tr>
</tbody>
<tbody id="ftp" style="display:none;">
<tr>
	<td style="width:20%;">FTP settings</td>
	<td style="width:80%;">
		<input class="inputbox" name="ftp_h" id="ftp_h" style="width:60%;" value="$ftp_h" type="text"> host <br />
		<input class="inputbox" name="ftp_u" id="ftp_u" style="width:60%;" value="$ftp_u" type="text"> username <br />
		<input class="inputbox" name="ftp_p" id="ftp_h" style="width:60%;" value="$ftp_p" type="text"> password <br />
	</td>
</tr>
</tbody>
<tbody id="sftp" style="display:none;">
<tr>
	<td style="width:20%;">SFTP settings</td>
	<td style="width:80%;">
		<input class="inputbox" name="sftp_h" id="sftp_h" style="width:60%;" value="$sftp_h" type="text"> host <br />
		<input class="inputbox" name="sftp_u" id="sftp_u" style="width:60%;" value="$sftp_u" type="text"> username <br />
		<input class="inputbox" name="sftp_p" id="sftp_p" style="width:60%;" value="$sftp_p" type="text"> password <br />
	</td>
</tr>
</tbody>
</table>
</div>
</div>
<div class="clr"> </div>
<div id="break"> </div>
</div>
<script type="text/javascript">
function d_s(id){document.getElementById(id).style.display='';}
function d_h(id){document.getElementById(id).style.display='none';}
function show(id){
	var value = id.options[id.selectedIndex].value;
			if(value=='file'){d_h('sftp');d_h('ftp');}
	else 	if(value=='ftp'){d_h('sftp');d_s('ftp');}
	else 	if(value=='sftp'){d_s('sftp');d_h('ftp');}
}
show(document.getElementById('method'));
</script>
HTML;
?>
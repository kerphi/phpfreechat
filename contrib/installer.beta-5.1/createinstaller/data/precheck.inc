<?php
/** Print text displaying status
  *
  * @param int $type + 1: Abailable/Unavailable
  *                  + 2: Pass/Fail
  * @param bool|int $value
  */
function status($value=null,$type)
{
	switch($type)
	{
		case null: $value=$type;break;
		case 'yes': return $value?'<span style="color:green;">Yes</span>':'<span style="color:red;">No</span>';
		case 'available':return $value?'<span style="color:green;">Available</span>':'<span style="color:red;">Unavailable</span>';
		case 'writeable': return $value?'<span style="color:green;">Writeable</span>':'<span style="color:red;">Not Writeable</span>';
		case 'on': return $value?'<span style="color:green;">ON</span>':'<span style="color:red;">OFF</span>';
		case 'support': return $value?'<span style="color:green;">Supported</span>':'<span style="color:red;">Unsupported</span>';
		case 'true': return $value?'<span style="color:green;">True</span>':'<span style="color:red;">False</span>';
		default: return 'ERR';
	}
}

//// edit this
$a = array(
	'file_get_contents'=>status(function_exists('file_get_contents'),'available'),
	'unserialize'=>status(function_exists('unserialize'),'available'),
	'GZ Compression'=>status(function_exists('gzuncompress'),'available')
);


$rows = '';
foreach ($a as $n=>$v){
	$rows.="<tr><td class=\"item\">$n</td><td align=\"left\"><b>$v</b></td></tr>";
}

echo <<<HTML
<div class="install-text">
These settings are recommended for PHP in order to ensure full
compatibility with PROGRAM.
<br />
However, it may still operate if your settings do not quite match the recommended
(In this case, the program will only offer, and thus say, 'Limited Functionality').
<div class="clr"></div>
</div>
<div class="install-form">
<div class="form-block">
<table class="content">
$rows
</table>
</div>
<div class="clr"></div>
</div>
HTML;
?>
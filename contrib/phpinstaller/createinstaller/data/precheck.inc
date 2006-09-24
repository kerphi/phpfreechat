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
		case 'yes': return '<span style="color:red;">No</span>';
		case 'available':return '<span style="color:green;">Available</span>';
		case 'writeable': return '<span style="color:green;">Writeable</span>';
		case 'on': return '<span style="color:green;">ON</span>';
		case 'support':
		case 'true': return '<span style="color:green;">True</span>';
		default: return 'ERR';
	}
	switch($value)
	{
		case 'yes': return '<span style="color:green;">Yes</span>';
		case 'no': return '<span style="color:red;">No</span>';
		case 'available':return '<span style="color:green;">Available</span>';
		case 'missing': return '<span style="color:red;">Missing</span>';
		case 'writeable': return '<span style="color:green;">Writeable</span>';
		case 'unwriteable': return '<span style="color:red;">Un-writeable</span>';
		case 'on': return '<span style="color:green;">ON</span>';
		case 'off': return '<span style="color:red;">OFF</span>';
		case 'support':
		case 'supported': return '<span style="color:green;">Supported</span>';
		case 'x': return '<span style="color:red;">X</span>';
		default:
		case 'true': return '<span style="color:green;">True</span>';
		case 'false': return '<span style="color:red;">False</span>';
	}
}
$a = array(
	status(function_exists('file_get_contents'),'available'),
	status(function_exists('unserialize'),'available'),
	status(function_exists('gzuncompress'),'available')
);

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
<tr><td class="item">file_get_contents</td><td align="left"><b>{$a[0]}</b></td></tr>
<tr><td class="item">unserialize</td><td align="left"><b>{$a[1]}</b></td></tr>
<tr><td class="item">GZ Compression</td><td align="left"><b>{$a[2]}</b></td></tr>
</table>
</div>
<div class="clr"></div>
</div>
HTML;
?>
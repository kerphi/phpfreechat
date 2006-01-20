<?php

$url = 'tools.php?p=dcchat';

$message = "";

buffer::str('<h1>dcChat - v'.XEmb_Config::GetAppVersion().'</h1>');

buffer::str("<h2>Etat de l'installation</h2>");
buffer::str('<ul>');

// detection du module php4-xslt
$img_check = '<img src="images/check_%s.png" alt="" />';
if (function_exists('session_id')) {
	buffer::str(
	'<li>'.sprintf($img_check,'on').' '.
	__('php sessions is enable.').
	'</li>'
	);
} else {
	buffer::str(
	'<li>'.sprintf($img_check,'off').' '.
	__('php sessions is missing.').
	'</li>'
	);
}

if (is_writable(dirname(__FILE__).'/../../../cache')) {
	buffer::str(
	'<li>'.sprintf($img_check,'on').' '.
	sprintf(__('Directory %s is writable.'),'<strong><code>'.dirname(__FILE__).'/../../../cache').'</code></strong>').
	'</li>'
	);
} else {
	buffer::str(
	'<li>'.sprintf($img_check,'off').' '.
	sprintf(__('Directory %s is not writable.'),'<strong><code>'.dirname(__FILE__).'/../../../cache').'</code></strong>').
	'</li>'
	);
}
buffer::str("</ul>");

?>

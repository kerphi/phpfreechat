<?php

require_once dirname(__FILE__)."/tools.php";

$url = 'tools.php?p=dcchat';

$message = "";

buffer::str('<h1>dcChat - v'.file_get_contents(dirname(__FILE__)."/phpfreechat/version").'</h1>');

buffer::str("<h2>Etat de l'installation</h2>");
buffer::str('<ul>');

// detection du support des sessions dans php
$img_check = '<img src="images/check_%s.png" alt="" />';
if (function_exists('session_id')) {
  buffer::str('<li>'.sprintf($img_check,'on').' '.
	      __('php sessions are enable.').
	      '</li>'
	      );
} else {
  buffer::str('<li>'.sprintf($img_check,'off').' '.
	      __('php sessions support is missing.').
	      '</li>'
	      );
}

/*
// verification des droits en écriture du répertoire partagé
$dir = cleanPath(dirname(__FILE__).'/../../../share');
$can_install = false;
if (is_writable($dir)) {
  buffer::str('<li>'.sprintf($img_check,'on').' '.
	      sprintf(__('Directory %s is writable.'),'<strong><code>'.$dir.'</code></strong>').
	      '</li>'
	      );
  $can_install = true;
} else {
  buffer::str('<li>'.sprintf($img_check,'off').' '.
	      sprintf(__('Directory %s is not writable.'),'<strong><code>'.$dir.'</code></strong>').
	      '</li>'
	      );
}
*/

/*
// verification de la presence de phpfreechat dans le repertoire partage
$dir = cleanPath(dirname(__FILE__).'/../../../share/dcchat/phpfreechat');
$dcchat_installed = false;
// check if versions match
if ( is_dir($dir) &&
     file_get_contents($dir."/version") ==
     file_get_contents(dirname(__FILE__)."/phpfreechat/version") )
{
  $dcchat_installed = true;
}

// installation si demandée
if ($can_install && !$dcchat_installed && isset($_GET["install"]))
{
  // on cree le repertoire de destination
  $dir = dirname(__FILE__).'/../../../share/dcchat/';
  mkdir($dir);
  $dir = dirname(__FILE__).'/../../../share/dcchat/phpfreechat';
  mkdir($dir);
  
  // copy phpfreechat to the shared directory
  require_once dirname(__FILE__)."/phpfreechat/src/phpfreechattools.class.php";
  phpFreeChatTools::CopyR(dirname(__FILE__)."/phpfreechat/", $dir);
  
  // reverification
  $dir = dirname(__FILE__).'/../../../share/dcchat/phpfreechat';
  $dcchat_installed = false;
  if ( is_dir($dir) &&
       file_get_contents($dir."/version") ==
       file_get_contents(dirname(__FILE__)."/phpfreechat/version") )
  {
    $dcchat_installed = true;
  }
}
if ($dcchat_installed)
{
  buffer::str('<li>'.sprintf($img_check,'on').' '.
	      __('dcChat is installed.').
	      '</li>'
	      );
}
else
{
  buffer::str('<li>'.sprintf($img_check,'off').' '.
	      __('dcChat is not installed.').($can_install?' (<a href="'.$url.'&amp;install">install it</a>)':'').
	      '</li>'
	      );
}
*/

/*
if (is_writable($dir)) {
  buffer::str('<li>'.sprintf($img_check,'on').' '.
	      sprintf(__('Directory %s is writable.'),'<strong><code>'.$dir.'</code></strong>').
	      '</li>'
	      );
} else {
  buffer::str('<li>'.sprintf($img_check,'off').' '.
	      sprintf(__('Directory %s is not writable.'),'<strong><code>'.$dir.'</code></strong>').
	      '</li>'
	      );
}
*/

buffer::str("</ul>");


?>

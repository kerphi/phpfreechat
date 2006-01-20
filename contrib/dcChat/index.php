<?php

$url = 'tools.php?p=dcchat';

$message = "";

buffer::str('<h1>dcChat - v'.XEmb_Config::GetAppVersion().'</h1>');

buffer::str("<h2>Etat de l'installation</h2>");
buffer::str('<ul>');

// detection du support des sessions dans php
$img_check = '<img src="images/check_%s.png" alt="" />';
if (function_exists('session_id')) {
  buffer::str('<li>'.sprintf($img_check,'on').' '.
	      __('php sessions is enable.').
	      '</li>'
	      );
} else {
  buffer::str('<li>'.sprintf($img_check,'off').' '.
	      __('php sessions is missing.').
	      '</li>'
	      );
}

// verification des droits en écriture du répertoire partagé
$dir = dirname(__FILE__).'/../../../share';
if (is_writable($dir) {
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

// verification de la presence de phpfreechat dans le repertoire partage
$dir = dirname(__FILE__).'/../../../share/dcchat/phpfreechat';
$dcchat_installed = false;
if (dir_exists($dir))
{
  // check if versions match
  if ( file_get_contents($dir."/version") ==
       file_get_contents(dirname(__FILE__)."/phpfreechat/version") )
  {
    $dcchat_installed = true;
  }
}
// installation si demandée
if (!$dcchat_installed && isset($_GET["install"]))
{
  // on cree le repertoire de destination
  $dir = dirname(__FILE__).'/../../../share/dcchat/';
  mkdir($dir);
  // copy phpfreechat to the shared directory
  require_once dirname(__FILE__)."/phpfreechat/src/phpfreechattools.class.php";
  phpFreeChatTools::CopyR(dirname(__FILE__)."/phpfreechat/", $dir);
  
  // reverification
  $dir = dirname(__FILE__).'/../../../share/dcchat/phpfreechat';
  $dcchat_installed = false;
  if (dir_exists($dir))
  {
    // check if versions match
    if ( file_get_contents($dir."/version") ==
	 file_get_contents(dirname(__FILE__)."/phpfreechat/version") )
    {
      $dcchat_installed = true;
    }
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
	      __('dcChat is not installed.').
	      '</li>'
	      );
}

if (is_writable($dir) {
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
buffer::str("</ul>");


?>

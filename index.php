<?php

require_once dirname(__FILE__)."/src/phpfreechat.class.php";
$params = array();
$params["title"] = "Quick chat";
$params["nick"] = "guest".rand(1,1000);  // setup the intitial nickname
$params["isadmin"] = true; // do not use it on production servers ;)
$params["serverid"] = md5(__FILE__); // calculate a unique id for this chat
//$params["debug"] = true;
$chat = new phpFreeChat( $params );

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html>
 <head>
  <meta http-equiv="content-type" content="text/html; charset=utf-8" />
  <title>phpFreeChat- Sources Index</title>
  <link rel="stylesheet" title="classic" type="text/css" href="style/generic.css" />
  <link rel="stylesheet" title="classic" type="text/css" href="style/header.css" />
  <link rel="stylesheet" title="classic" type="text/css" href="style/footer.css" />
  <link rel="stylesheet" title="classic" type="text/css" href="style/menu.css" />
  <link rel="stylesheet" title="classic" type="text/css" href="style/content.css" />  
 </head>
 <body>

<div class="header">
      <h1>phpFreeChat - Sources Index</h1>
      <img alt="logo bulle" src="style/bulle.gif" class="logo2" />
</div>

<div class="menu">
      <ul>
        <li class="sub title">General</li>
        <li>
          <ul class="sub">
            <li class="item">
              <a href="demo/">Demos</a>
            </li>
            <?php if (file_exists(dirname(__FILE__)."/checkmd5.php")) { ?>
            <li>
              <a href="checkmd5.php">Check md5</a>
            </li>
            <?php } ?>
            <!--
            <li class="item">
              <a href="admin/">Administration</a>
            </li>
            -->
          </ul>
        </li>
        <li class="sub title">Documentation</li>
        <li>
          <ul>
            <li class="item">
              <a href="http://www.phpfreechat.net/overview">Overview [en]</a>
            </li>
            <li class="item">
              <a href="http://www.phpfreechat.net/fr/overview">Overview [fr]</a>
            </li>
            <li class="item">
              <a href="http://www.phpfreechat.net/es/overview">Overview [es]</a>
            </li>
            <li class="item">
              <a href="http://www.phpfreechat.net/zh/overview">Overview [zh]</a>
            </li>
            <li class="item">
              <a href="http://www.phpfreechat.net/ar/overview">Overview [ar]</a>
            </li>
            <li class="item">
              <a href="http://www.phpfreechat.net/quickstart">Quickstart [en]</a>
            </li>
            <li class="item">
              <a href="http://www.phpfreechat.net/fr/quickstart">En avant ! [fr]</a>
            </li>
            <li class="item">
              <a href="http://www.phpfreechat.net/parameters">Parameters list [en]</a>
            </li>
            <li class="item">
              <a href="http://www.phpfreechat.net/faq">FAQ [en]</a>
            </li>
            <li class="item">
              <a href="http://www.phpfreechat.net/fr/faq">FAQ [fr]</a>
            </li>
            <li class="item">
              <a href="http://www.phpfreechat.net/advanced-configuration">Advanced configuration [en]</a>
            </li>
            <li class="item">
              <a href="http://www.phpfreechat.net/fr/advanced-configuration">Configuration avancée [fr]</a>
            </li>            
            <li class="item">
              <a href="http://www.phpfreechat.net/customize">Customize [en]</a>
            </li>
          </ul>
        </li>
      </ul>
      <p class="partner">
        <a href="http://www.phpfreechat.net"><img alt="phpfreechat.net" src="style/logo_88x31.gif" /></a><br/>
        <a href="http://www.jeu-gratuit.net/">jeu-gratuit</a><br/>
        <a href="http://jeux-flash.jeu-gratuit.net/">jeux-flash</a><br/>
        <a href="http://www.pronofun.com/">pronofun</a><br/>
        <a href="http://areno.jeu-gratuit.net/">areno</a><br/>
        <a href="http://www.micropolia.com/">micropolia</a><br/>
        <a href="http://www.zeitoun.net/">zeitoun</a><br/>
        <a href="http://www.hotscripts.com/?RID=N452772">hotscripts</a><br/>
      </p>
</div>

<div class="content">
  <?php $chat->printChat(); ?>
  <p style="color:red;font-weight:bold;">Warning: because of "isadmin" parameter, everybody is admin. Please modify this script before using it on production servers !</p>
</div>

</body></html>

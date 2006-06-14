<?php

require_once dirname(__FILE__)."/src/phpfreechat.class.php";
$params = array();
$params["nick"] = "guest".rand(1,10);  // setup the intitial nickname
$params["serverid"] = md5(__FILE__); // calculate a unique id for this chat
$chat = new phpFreeChat( $params );

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html>
 <head>
  <meta http-equiv="content-type" content="text/html; charset=utf-8" />
  <title>phpFreeChat Sources Index</title>
  <link rel="stylesheet" title="classic" type="text/css" href="style/generic.css" />
  <link rel="stylesheet" title="classic" type="text/css" href="style/header.css" />
  <link rel="stylesheet" title="classic" type="text/css" href="style/footer.css" />
  <link rel="stylesheet" title="classic" type="text/css" href="style/menu.css" />
  <link rel="stylesheet" title="classic" type="text/css" href="style/content.css" />  
  <?php $chat->printJavascript(); ?>
  <?php $chat->printStyle(); ?>  
 </head>
 <body>

<div class="header">
      <h1>phpFreeChat - Sources Index</h1>
      <img alt="logo bulle" src="style/bulle.png" class="logo2" />
</div>

<div class="menu">
      <ul>
        <li class="sub title">General</li>
        <li>
          <ul class="sub">
            <li class="item">
              <a href="demo/">Demos</a>
            </li>
            <li class="item">
              <a href="admin/">Administration</a>
            </li>
          </ul>
        </li>
        <li class="sub title">Documentation</li>
        <li>
          <ul>
            <li class="item">
              <a href="README.en">readme [en]</a>
            </li>
            <li class="item">
              <a href="README.fr">readme [fr]</a>
            </li>
            <li class="item">
              <a href="INSTALL.en">install [en]</a>
            </li>
            <li class="item">
              <a href="INSTALL.fr">install [fr]</a>
            </li>
          </ul>
        </li>
      </ul>
      <p class="partner">
        <a href="http://www.phpfreechat.net"><img alt="logo big" src="style/logo_88x31.gif" /></a>
      </p>
      
      <div class="rating">
        <form action="http://www.hotscripts.com/rate/56184.html" method="get">
          <input type="hidden" name="RID" value="N452772"/>
          <table>
            <tbody>
              <tr>
                <td>
                  <table>
                    <tbody>
                      <tr>
                        <td>If you like our script, please rate it! <input type="hidden" name="external" value="1"/>
                        </td>
                      </tr>
                      <tr>
                        <td>
                          <select name="rate" size="1">
                            <option value="5" selected="selected">Excellent!</option>
                            <option value="4">Very Good</option>
                            <option value="3">Good</option>
                            <option value="2">Fair</option>
                            <option value="1">Poor</option>
                          </select>
                          <input type="submit" name="submit" value="Cast My Vote!"/>
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </td>
              </tr>
            </tbody>
          </table>
        </form>
      </div>
</div>

<div class="content">
  <h2>See the quick demo</h2>
  <?php $chat->printChat(); ?>
  <?php if (file_exists(dirname(__FILE__)."/checkmd5.php")) require_once dirname(__FILE__)."/checkmd5.php"; ?>
</div>

<div class="footer">
      <div class="valid">
        <a href="http://validator.w3.org/check?uri=referer">
          <img alt="Valid XHTML 1.0!" src="style/valid-xhtml.png" />
        </a>
        <a href="http://jigsaw.w3.org/css-validator/check/referer">
          <img alt="Valid CSS!" src="style/valid-css.png" />
        </a>
      </div>
      <p>@2006 phpFreeChat</p>
    </div>
</body></html>

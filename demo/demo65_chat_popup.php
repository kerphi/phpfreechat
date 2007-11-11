<?php

require_once dirname(__FILE__)."/../src/phpfreechat.class.php";
$params['serverid'] = md5(__FILE__); // calculate a unique id for this chat
$params['nickmeta']['id'] = rand(1,1000);
$params['nickmeta']['profil'] = '<a href="demo65_metadata_and_html.php?profil='.$params['nickmeta']['id'].'" onclick="window.opener.location.href=this.href;return false;">open profil</a>';
$params['nickmeta']['avatar'] = '<a href="demo65_metadata_and_html.php?profil='.$params['nickmeta']['id'].'" onclick="window.opener.location.href=this.href;return false;"><img src="http://img217.imageshack.us/img217/5223/244bg4.png" alt=""/></a>';
$params['nickmeta_key_to_hide'] = array('profil','avatar');
$chat = new phpFreeChat( $params );

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
  <head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <title>phpFreeChat demo</title>
  </head>

  <body>
<?php $chat->printChat(); ?>
  </body>
</html>
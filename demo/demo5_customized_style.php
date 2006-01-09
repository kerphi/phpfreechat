<?php

require_once dirname(__FILE__)."/../src/phpfreechat.class.php";

$params =  array("title"          => "A chat with a customized stylesheet",
                 "height"         => "500px",
                 "width"          => "550px",
                 "max_msg"        => 21,
                 //"debug"          => true,
                 "css_file"       => dirname(__FILE__)."/demo5_customized_style_data/style.css.tpl",
                 );
$chat = new phpFreeChat( $params );

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
	<head>
		<title>phpFreeChat demo</title>

<?php $chat->printJavascript(); ?>
<?php $chat->printStyle(); ?>

	</head>
	<body>

<?php $chat->printChat(); ?>

	</body>
</html>
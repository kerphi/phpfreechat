<?php

require_once "../src/phpchat.class.php";
$chatconfig = new phpChatConfig( array("title"          => "A simple chat with user's parameters",
                                       "init_nick"      => "guest",
                                       "frozen_nick"    => false,
                                       "max_nick_len"   => 5,
                                       "max_text_len"   => 20,
                                       "refresh_delay"  => 1000,
                                       "max_msg"        => 15,
                                       "height"         => "230px",
                                       "width"          => "800px",
                                       //"debug"          => true,
                                       ) );
$chat = new phpChat( $chatconfig );

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
	<head>
		<title>phpChat demo</title>

<?php $chat->printJavascript(); ?>
<?php $chat->printStyle(); ?>

	</head>
	<body>

<?php $chat->printChat(); ?>

	</body>
</html>

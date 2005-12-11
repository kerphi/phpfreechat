<?php

require_once "../src/phpchat.class.php";

// sleep [1-5] seconds to simulate a random serveur lag
sleep(rand(1,5));

$chatconfig = new phpChatConfig( array("title" => "A chat simulating slow server (lag form 1 to 5 seconds)",
                                       "init_nick" => "guest",
                                       "refresh_delay" => 2000, // a fast refresh rate
                                       //"debug" => true,
                                       //"data_file"  => "chat_data/chat_simulate_slow_server.data",
                                       //"index_file" => "chat_data/chat_simulate_slow_server.index",
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
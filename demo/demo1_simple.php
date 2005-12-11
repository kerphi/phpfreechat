<?php

require_once "../src/phpchat.class.php";

$chat = new phpChat();

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

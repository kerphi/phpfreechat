<?php

require_once dirname(__FILE__)."/../src/phpfreechat.class.php";

$params = array();
//$params["connect"] = false;
//$params["debug"]   = true;
$chat = new phpFreeChat( $params );
$c =& phpFreeChatConfig::Instance();

//print_r($_SESSION);

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
	<head>
		<title>phpFreeChat testcase</title>

<?php $chat->printJavascript(); ?>
<?php $chat->printStyle(); ?>

	</head>
	<body>
<p><code>Cmd_nick</code> testcase : nickname should be changed to <strong>toto</strong></p>
<?php $chat->printChat(); ?>

  <script type="text/javascript">
  <?php echo $c->prefix."handleRequest('/nick toto');"; ?>
  </script>
        
	</body>
</html>
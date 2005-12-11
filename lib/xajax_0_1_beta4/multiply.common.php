<?php
// multiply.php, multiply.common.php, multiply.server.php
// demonstrate a very basic xajax implementation with separate server and
// client files
// using xajax version 0.1 beta4
// http://xajax.sourceforge.net

require("xajax.inc.php");
$xajax = new xajax("multiply.server.php");
$xajax->registerFunction("multiply");
?>

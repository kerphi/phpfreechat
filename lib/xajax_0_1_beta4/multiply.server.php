<?php
// multiply.php, multiply.common.php, multiply.server.php
// demonstrate a very basic xajax implementation with separate server and
// client files
// using xajax version 0.1 beta4
// http://xajax.sourceforge.net

function multiply($x, $y)
{
	$objResponse = new xajaxResponse();
	$objResponse->addAssign("z", "value", $x*$y);
	return $objResponse->getXML();
}

require("multiply.common.php");
$xajax->processRequests();
?>

<?php
/**
 * xajaxResponsePlugin.inc.php :: xajax abstract response plugin class
 *
 * xajax version 0.5 (Beta 1)
 * copyright (c) 2006 by Jared White & J. Max Wilson
 * http://www.xajaxproject.org
 *
 * xajax is an open source PHP class library for easily creating powerful
 * PHP-driven, web-based Ajax Applications. Using xajax, you can asynchronously
 * call PHP functions and update the content of your your webpage without
 * reloading the page.
 *
 * xajax is released under the terms of the BSD license
 * http://www.xajaxproject.org/bsd_license.txt
 * 
 * @package xajax
 * @version $Id: xajaxResponsePlugin.inc.php 259 2006-10-03 18:14:49Z gaeldesign $
 * @copyright Copyright (c) 2005-2006 by Jared White & J. Max Wilson
 * @license http://www.xajaxproject.org/bsd_license.txt BSD License
 */
 
 class xajaxResponsePlugin
 {
 	var $sCallName = "plugin";
 	
 	var $_objResponse;
 	
 	function __construct()
 	{
 		if (strtolower(get_class($this)) == "xajaxresponseplugin") {
 			trigger_error("The xajaxResponsePlugin class is abstract and cannot be instantiated. Please use a concrete subclass instead", E_USER_ERROR);
 		}
 	}
 	function xajaxResponsePlugin()
 	{
 		$aArgs = func_get_args();
 		call_user_func_array(array(&$this, "__construct"), $aArgs);
 	}
 	
 	function setResponseObject(&$objResponse)
 	{
 		$this->_objResponse =& $objResponse;
 	}
 	
 	function addCommand($aAttributes, $sData)
 	{
 		$this->_objResponse->addPluginCommand($this, $aAttributes, $sData);
 	}
}
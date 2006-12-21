<?php
/**
 * xajaxRequestProcessorPlugin.inc.php :: xajax abstract request processor
 *  plugin class
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
 * @version $Id: xajaxRequestProcessorPlugin.inc.php 259 2006-10-03 18:14:49Z gaeldesign $
 * @copyright Copyright (c) 2005-2006 by Jared White & J. Max Wilson
 * @license http://www.xajaxproject.org/bsd_license.txt BSD License
 */
 
 class xajaxRequestProcessorPlugin
 {
 	var $_objXajax;
 	
 	function __construct()
 	{
 		if (strtolower(get_class($this)) == "xajaxrequestprocessorplugin") {
 			trigger_error("The xajaxRequestProcessorPlugin class is abstract and cannot be instantiated. Please use a concrete subclass instead", E_USER_ERROR);
 		}
  	}
 	
 	function xajaxRequestProcessorPlugin()
 	{
 		$aArgs = func_get_args();
 		call_user_func_array(array(&$this, "__construct"), $aArgs);
 	}
 	
 	function setXajax(&$objXajax)
 	{
 		$this->_objXajax =& $objXajax;
 	}
 	
 	function getRequestMode()
 	{
 		trigger_error("The getRequestMode method is abstract and must be implemented in this subclass", E_USER_ERROR);
 	}
 	
 	function canProcessRequest()
 	{
 		trigger_error("The canProcessRequest method is abstract and must be implemented in this subclass", E_USER_ERROR);
 	}
 	
 	function processRequest()
 	{
 		trigger_error("The processRequest method is abstract and must be implemented in this subclass", E_USER_ERROR);
 	}
}
<?php
/**
 * xajaxIncludePlugin.inc.php :: xajax abstract script include plugin class
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
 * @version $Id: xajaxIncludePlugin.inc.php 259 2006-10-03 18:14:49Z gaeldesign $
 * @copyright Copyright (c) 2005-2006 by Jared White & J. Max Wilson
 * @license http://www.xajaxproject.org/bsd_license.txt BSD License
 */
 
 class xajaxIncludePlugin
 {
 	var $_objXajax;
 	var $_aFunctions;
 	
 	function __construct()
 	{
 		if (strtolower(get_class($this)) == "xajaxincludeplugin") {
 			trigger_error("The xajaxIncludePlugin class is abstract and cannot be instantiated. Please use a concrete subclass instead", E_USER_ERROR);
 		}
  	}
 	
 	function xajaxIncludePlugin()
 	{
 		$aArgs = func_get_args();
 		call_user_func_array(array(&$this, "__construct"), $aArgs);
 	}

 	function setXajax(&$objXajax)
 	{
 		$this->_objXajax =& $objXajax;
 	}
 	
 	function setFunctions($aFunctions)
 	{
 		$this->_aFunctions = $aFunctions;
 	}
 	
 	function getJavascript($sJsURI="", $sJsFile=NULL)
 	{
 		trigger_error("The getJavascript method is abstract and must be implemented in this subclass", E_USER_ERROR);
 	}
 	
 	function getJavascriptConfig()
 	{
 		trigger_error("The getJavascriptConfig method is abstract and must be implemented in this subclass", E_USER_ERROR);
 	}
 	
 	function getJavascriptInclude($sJsURI="", $sJsFile=NULL)
 	{
 		trigger_error("The getJavascriptInclude method is abstract and must be implemented in this subclass", E_USER_ERROR); 		
 	}
}
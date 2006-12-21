<?php
/**
 * xajaxDefaultIncludePlugin.inc.php :: xajax default script include plugin
 * class
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
 * @version $Id: xajaxDefaultIncludePlugin.inc.php 259 2006-10-03 18:14:49Z gaeldesign $
 * @copyright Copyright (c) 2005-2006 by Jared White & J. Max Wilson
 * @license http://www.xajaxproject.org/bsd_license.txt BSD License
 */
 
 class xajaxDefaultIncludePlugin extends xajaxIncludePlugin
 {
 	function getJavascript($sJsURI="", $sJsFile=NULL)
 	{
		$html = $this->getJavascriptConfig();
		$html .= $this->getJavascriptInclude($sJsURI, $sJsFile);
		
		return $html;
 	}
 	
 	function getJavascriptConfig()
 	{
		$html  = "\t<script type=\"text/javascript\"><!--\n";
		$html .= "var xajaxConfig = {\n";
		$html .= "requestURI: \"".$this->_objXajax->getRequestURI()."\",\n";
		$html .= "debug: ".($this->_objXajax->getFlag("debug")?"true":"false").",\n";
		$html .= "statusMessages: ".($this->_objXajax->getFlag("statusMessages")?"true":"false").",\n";
		$html .= "waitCursor: ".($this->_objXajax->getFlag("waitCursor")?"true":"false").",\n";
		$html .= "version: \"".$this->_objXajax->getVersion()."\",\n";
		$html .= "legacy: ".(is_a($this->_objXajax, "legacyXajax")?"true":"false")."\n";
		$html .= "};\nvar xajaxLoaded=false;\n";

		foreach(array_keys($this->_aFunctions) as $sFunction) {
			$html .= $this->_wrap($sFunction);
		}

		$html .= "\t--></script>\n";
		return $html;		
 	}
 	
 	function getJavascriptInclude($sJsURI="", $sJsFile=NULL)
 	{
		if ($sJsFile == NULL) $sJsFile = "xajax_js/xajax.js";
			
		if ($sJsURI != "" && substr($sJsURI, -1) != "/") $sJsURI .= "/";
		
		$html = "\t<script type=\"text/javascript\" src=\"" . $sJsURI . $sJsFile . "\"></script>\n";
		if ($this->_objXajax->getTimeout())
		{
			$html .= "\t<script type=\"text/javascript\"><!--\n";
			$html .= "window.setTimeout(function () { if (!xajaxLoaded) { alert('Error: the xajax Javascript file could not be included. Perhaps the URL is incorrect?\\nURL: {$sJsURI}{$sJsFile}'); } }, ".$this->_objXajax->getTimeout().");\n";
			$html .= "\t--></script>\n";
		}
		return $html;
 	}
 	
 	function _wrap($sFunction)
	{
		$js = "function ".$this->_objXajax->getWrapperPrefix()."$sFunction(){return xajax.";
		if (is_a($this->_objXajax, "legacyXajax")) {
			$js .= "advancedCall";
		}
		else {
			$js .= "call";
		}
		$js .= "(\"$sFunction\", {parameters: arguments});}\n";
		return $js;
	}
}
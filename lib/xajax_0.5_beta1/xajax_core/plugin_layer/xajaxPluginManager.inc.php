<?php
/**
 * xajaxPluginManager.inc.php :: xajax plugin manager
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
 * @version $Id: xajaxPluginManager.inc.php 259 2006-10-03 18:14:49Z gaeldesign $
 * @copyright Copyright (c) 2005-2006 by Jared White & J. Max Wilson
 * @license http://www.xajaxproject.org/bsd_license.txt BSD License
 */
 
class xajaxPluginManager
{
	var $aPluginFolders;
	var $aResponsePlugins;
	var $objRequestProcessorPlugin;
	var $objHeaderScriptPlugin;
	
	function &getInstance()
	{
		static $obj;
		if (!$obj) {
			$obj = new xajaxPluginManager();	
		}
		return $obj;
	}
	
	function addPluginFolder($sFolder)
	{
		if (!file_exists($sFolder)) return false;
		$this->aPluginFolders[$sFolder] = $sFolder;
	}
	
	function loadPluginFile($sPluginName)
	{
		$bPluginLoaded = false;
		foreach ($this->aPluginFolders as $sPluginFolder) {
			$sPluginPath = $sPluginFolder . '/' . $sPluginName . '.inc.php';
			if (file_exists($sPluginPath)) {
				require_once($sPluginPath);
				$bPluginLoaded = true;
			}
		}
		return $bPluginLoaded;
	}
	
	function loadAllPlugins()
	{
		// to do
	}
	
	function registerRequestProcessorPlugin(&$objPlugin)
	{
		if (is_subclass_of($objPlugin, "xajaxRequestProcessorPlugin")) {
			$this->objRequestProcessorPlugin = &$objPlugin;
		}
	}

	function &getRequestProcessorPlugin()
	{
		return $this->objRequestProcessorPlugin;
	}

	function registerIncludePlugin(&$objPlugin)
	{
		if (is_subclass_of($objPlugin, "xajaxIncludePlugin")) {
			$this->objIncludePlugin = &$objPlugin;
		}
	}

	function &getIncludePlugin()
	{
		return $this->objIncludePlugin;
	}
		
	function registerResponsePlugin(&$objPlugin)
	{
		if (is_subclass_of($objPlugin, "xajaxResponsePlugin")) {
			$sName = $objPlugin->sCallName;
			$this->aResponsePlugins[$sName] = &$objPlugin;
		}
	}
	
	function &getResponsePlugin($sName)
	{
		return $this->aResponsePlugins[$sName];
	}
}

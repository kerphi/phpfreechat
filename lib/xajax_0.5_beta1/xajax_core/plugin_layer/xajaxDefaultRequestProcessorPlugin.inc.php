<?php
/**
 * xajaxDefaultRequestProcessorPlugin.inc.php :: xajax default request
 *  processor plugin
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
 * @version $Id: xajaxDefaultRequestProcessorPlugin.inc.php 259 2006-10-03 18:14:49Z gaeldesign $
 * @copyright Copyright (c) 2005-2006 by Jared White & J. Max Wilson
 * @license http://www.xajaxproject.org/bsd_license.txt BSD License
 */

class xajaxDefaultRequestProcessorPlugin extends xajaxRequestProcessorPlugin
{
	/**
	 * @var array Array for parsing complex objects
	 */
	var $aObjArray;
	/**
	 * @var integer Position in $aObjArray
	 */
	var $iPos;
	
	function getRequestMode()
 	{
		if (!empty($_GET["xajax"]))
			return XAJAX_GET;
		
		if (!empty($_POST["xajax"]))
			return XAJAX_POST;
			
		return -1;
 	}
 	
 	function canProcessRequest()
 	{
		if ($this->getRequestMode() != -1) return true;
		return false;
 	}
 	
 	function processRequest()
 	{
		$requestMode = $this->getRequestMode();
		$sFunctionName = "";
		$bFoundFunction = true;
		$aArgs = array();
		$bEndRequest = false;
		
		$objResponse = $this->_objXajax->getGlobalResponse();
		$objTempResponse = NULL;
		$aFunctions = $this->_objXajax->getRegisteredFunctions();
		$aEvents = $this->_objXajax->getRegisteredEvents();
		$aCallableObjects = $this->_objXajax->getRegisteredCallableObjects();
		
		// Check to see if headers have already been sent out, in which case we can't do our job
		if (headers_sent($filename, $linenumber)) {
			echo "Output has already been sent to the browser at $filename:$linenumber.\nPlease make sure the command " . '$xajax->processRequests() is placed before this.';
			if ($this->_objXajax->getFlag("exitAllowed")) {
				exit();
			}
			else {
				return;
			}
		}
		
		// Load in the xajax function and arguments
		if ($requestMode == XAJAX_POST) {
			$sFunctionName = $_POST["xajax"];
			
			if (!empty($_POST["xajaxargs"])) 
				$aArgs = $_POST["xajaxargs"];
		} else {	
			header ("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
			header ("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
			header ("Cache-Control: no-cache, must-revalidate");
			header ("Pragma: no-cache");
			
			$sFunctionName = $_GET["xajax"];
			
			if (!empty($_GET["xajaxargs"])) 
				$aArgs = $_GET["xajaxargs"];
		}
		$aArgs = $this->_processInputArgs($aArgs);
				
		// Use xajax error handler if necessary
		if ($this->_objXajax->getFlag("errorHandler")) {
			$GLOBALS['xajaxErrorHandlerText'] = "";
			set_error_handler("xajaxErrorHandler");
		}
		
		// Call "beforeProcessing" events
		if (!empty($aEvents["beforeProcessing"])) {
			foreach ($aEvents["beforeProcessing"] as $callback)
			{
				$mReturn = call_user_func_array($callback, array($sFunctionName, $aArgs, &$this->_objXajax));
				if (is_array($mReturn)) {
					if ($mReturn[0] === false) {
						$bEndRequest = true;
					}
					$objTempResponse = $mReturn[1];
				}
				else {
					$objTempResponse = $mReturn;
				}
				if ($objTempResponse != $objResponse) {
					$objResponse->loadCommands($objTempResponse);
				}
			}
			if ($bEndRequest) {
				$this->_outputResponse($objResponse);
				return;
			}
		}
		
		// Check to see if a function can be found or not
		if (array_key_exists($sFunctionName, $aFunctions))
		{
			// Include any external dependencies associated with this function name
			if (isset($aFunctions[$sFunctionName]["include"])) {
				ob_start();
				include_once($aFunctions[$sFunctionName]["include"]);
				ob_end_clean();
			}
			// Call the function
			$callback = $aFunctions[$sFunctionName]["callback"];
			$objTempResponse = call_user_func_array($callback, $aArgs);
			if (!is_a($objTempResponse, 'xajaxResponse')) {
				$this->_outputError("No xajaxResponse Was Returned By Function $sFunctionName.");
				return;
			}
			if ($objTempResponse != $objResponse) {
				// Load any previous response commands into the new response object
				$objTempResponse->loadCommands($objResponse, true);
			}
			// We're successful; output the response now!
			$this->_outputResponse($objTempResponse);
			return;
 		}
 		
 		// A function was not found. Let's check callable objects
		if ($aCallableObjects) {
			foreach ($aCallableObjects as $object) {
				$names[] = get_class($object);
				if (method_exists($object, $sFunctionName) || method_exists($object, "__call")) {
					// Attemping to call the object
					$objTempResponse = call_user_func_array(array(&$object, $sFunctionName), $aArgs);
					if (!is_a($objTempResponse, 'xajaxResponse')) {
						$this->_outputError("No xajaxResponse Was Returned By Method $sFunctionName of " . get_class($object));
						return;
					}
					if ($objTempResponse != $objResponse) {
						// Load any previous response commands into the new response object
						$objTempResponse->loadCommands($objResponse, true);
					}
					// We're successful; output the response now!
					$this->_outputResponse($objTempResponse);
					return;
				}
			}
		}

		// Call "onMissingFunction" events
		if (!empty($aEvents["onMissingFunction"])) {
			$bEndRequest = false;
			foreach ($aEvents["onMissingFunction"] as $callback)
			{
				$bEndRequest = true;
				$objTempResponse = call_user_func_array($callback, array($sFunctionName, $aArgs, &$this->_objXajax));
				if ($objTempResponse != $objResponse) {
					$objResponse->loadCommands($objTempResponse);
				}
			}
			if ($bEndRequest) {
				$this->_outputResponse($objResponse);
				return;
			}
		}

		// We've made it this far and nothing has handled the request. Darn!
		$this->_outputError("The Registered Function $sFunctionName Could Not Be Found.\n\nIf there are any functions requiring an automatic PHP include, make sure the filenames are correct.");
 	}
 	
 	function _outputResponse($objResponse)
 	{
 		$sEncoding = $this->_objXajax->getCharEncoding();
 		$bErrorHandler = $this->_objXajax->getFlag("errorHandler");
 		$sLogFile = $this->_objXajax->getLogFile();
 		
 		// Output content type
 		$sContentHeader = "Content-type: ".$objResponse->getContentType().";";
		if ($sEncoding && strlen(trim($sEncoding)) > 0)
			$sContentHeader .= " charset=".$sEncoding;
		header($sContentHeader);

		// If there are errors recorded by xajax's error handler, output them
		if ($bErrorHandler && !empty($GLOBALS['xajaxErrorHandlerText'])) {
			$errorResponse = new xajaxResponse();
			$errorResponse->alert("** PHP Error Messages: **" . $GLOBALS['xajaxErrorHandlerText']);
			if ($sLogFile) {
				$fH = @fopen($sLogFile, "a");
				if (!$fH) {
					$errorResponse->alert("** Logging Error **\n\nxajax was unable to write to the error log file:\n" . $sLogFile);
				}
				else {
					fwrite($fH, "** xajax Error Log - " . strftime("%b %e %Y %I:%M:%S %p") . " **" . $GLOBALS['xajaxErrorHandlerText'] . "\n\n\n");
					fclose($fH);
				}
			}

			$objResponse->loadCommands($errorResponse, true);
		}
		
		// Clean buffer maybe
		if ($this->_objXajax->getFlag("cleanBuffer")) while (@ob_end_clean());
		
		// Output the response and exit (maybe!)
		if ($this->_objXajax->getFlag("allowBlankResponse") && $objResponse->getCommandCount() === 0) {
			// do nothing
		} else {
			print $objResponse->getOutput();
		}
		if ($bErrorHandler) restore_error_handler();
		
		if ($this->_objXajax->getFlag("exitAllowed")) exit();		
 	}
 	
 	function _outputError($errorMsg)
 	{
 		$objResponse = new xajaxResponse();
 		$objResponse->alert($errorMsg);
 		$this->_outputResponse($objResponse);
 	}
 	
	/**
	 * Converts the raw input arguments into proper xajax arguments.
	 * You can subclass xajax and extend this method to perform additional conversion
	 * steps such as input filtering.
	 *
	 * @param array the arguments to process
	 * @access protected
	 * @return array
	 */
	function _processInputArgs($aArgs) {
		for ($i = 0; $i < sizeof($aArgs); $i++)
		{
			// If magic quotes is on, then we need to strip the slashes from the args
			if (get_magic_quotes_gpc() == 1 && is_string($aArgs[$i])) {
			
				$aArgs[$i] = stripslashes($aArgs[$i]);
			}
			if (stristr($aArgs[$i],"<xjxobj>") != false)
			{
				$aArgs[$i] = $this->_xmlToArray("xjxobj",$aArgs[$i]);	
			}
			else if (stristr($aArgs[$i],"<xjxquery>") != false)
			{
				$aArgs[$i] = $this->_xmlToArray("xjxquery",$aArgs[$i]);	
			}
			else if ($this->_objXajax->getFlag("decodeUTF8Input"))
			{
				$aArgs[$i] = $this->_decodeUTF8Data($aArgs[$i]);	
			}
		}
		return $aArgs;
	}
	
	/**
	 * Takes a string containing xajax xjxobj XML or xjxquery XML and builds an
	 * array representation of it to pass as an argument to the PHP function
	 * being called.
	 * 
	 * @param string the root tag of the XML
	 * @param string XML to convert
	 * @access protected
	 * @return array
	 */
	function _xmlToArray($rootTag, $sXml)
	{
		$aArray = array();
		$sXml = str_replace("<$rootTag>","<$rootTag>|~|",$sXml);
		$sXml = str_replace("</$rootTag>","</$rootTag>|~|",$sXml);
		$sXml = str_replace("<e>","<e>|~|",$sXml);
		$sXml = str_replace("</e>","</e>|~|",$sXml);
		$sXml = str_replace("<k>","<k>|~|",$sXml);
		$sXml = str_replace("</k>","|~|</k>|~|",$sXml);
		$sXml = str_replace("<v>","<v>|~|",$sXml);
		$sXml = str_replace("</v>","|~|</v>|~|",$sXml);
		$sXml = str_replace("<q>","<q>|~|",$sXml);
		$sXml = str_replace("</q>","|~|</q>|~|",$sXml);
		
		$this->aObjArray = explode("|~|",$sXml);
		
		$this->iPos = 0;
		$aArray = $this->_parseObjXml($rootTag);
	    
		return $aArray;
	}
	
	/**
	 * A recursive function that generates an array from the contents of
	 * $this->aObjArray.
	 * 
	 * @param string the root tag of the XML
	 * @access protected
	 * @return array
	 */
	function _parseObjXml($rootTag)
	{
		$aArray = array();
		
		if ($rootTag == "xjxobj")
		{
			while(!stristr($this->aObjArray[$this->iPos],"</xjxobj>"))
			{
				$this->iPos++;
				if(stristr($this->aObjArray[$this->iPos],"<e>"))
				{
					$key = "";
					$value = null;
						
					$this->iPos++;
					while(!stristr($this->aObjArray[$this->iPos],"</e>"))
					{
						if(stristr($this->aObjArray[$this->iPos],"<k>"))
						{
							$this->iPos++;
							while(!stristr($this->aObjArray[$this->iPos],"</k>"))
							{
								$key .= $this->aObjArray[$this->iPos];
								$this->iPos++;
							}
						}
						if(stristr($this->aObjArray[$this->iPos],"<v>"))
						{
							$this->iPos++;
							while(!stristr($this->aObjArray[$this->iPos],"</v>"))
							{
								if(stristr($this->aObjArray[$this->iPos],"<xjxobj>"))
								{
									$value = $this->_parseObjXml("xjxobj");
									$this->iPos++;
								}
								else
								{
									$value .= $this->aObjArray[$this->iPos];
									if ($this->_objXajax->getFlag("decodeUTF8Input"))
									{
										$value = $this->_decodeUTF8Data($value);
									}
								}
								$this->iPos++;
							}
						}
						$this->iPos++;
					}
					//decode the CDATA stuff
					$key = str_replace(array('<![CDATA[', ']]>'), '', $key);
					$value = str_replace(array('<![CDATA[', ']]>'), '', $value);
					$aArray[$key]=$value;
				}
			}
		}
		
		if ($rootTag == "xjxquery")
		{
			$sQuery = "";
			$this->iPos++;
			while(!stristr($this->aObjArray[$this->iPos],"</xjxquery>"))
			{
				if (stristr($this->aObjArray[$this->iPos],"<q>") || stristr($this->aObjArray[$this->iPos],"</q>"))
				{
					$this->iPos++;
					continue;
				}
				$sQuery	.= $this->aObjArray[$this->iPos];
				$this->iPos++;
			}
			
			parse_str($sQuery, $aArray);
			if ($this->_objXajax->getFlag("decodeUTF8Input"))
			{
				foreach($aArray as $key => $value)
				{
					$aArray[$key] = $this->_decodeUTF8Data($value);
				}
			}
			// If magic quotes is on, then we need to strip the slashes from the
			// array values because of the parse_str pass which adds slashes
			if (get_magic_quotes_gpc() == 1) {
				$newArray = array();
				foreach ($aArray as $sKey => $sValue) {
					if (is_string($sValue))
						$newArray[$sKey] = stripslashes($sValue);
					else
						$newArray[$sKey] = $sValue;
				}
				$aArray = $newArray;
			}
		}
		
		return $aArray;
	}
	
	/**
	 * Decodes string data from UTF-8 to the current xajax encoding.
	 * 
	 * @param string data to convert
	 * @access protected
	 * @return string converted data
	 */
	function _decodeUTF8Data($sData)
	{
		$sValue = $sData;
		if ($this->_objXajax->getFlag("decodeUTF8Input"))
		{
			$sFuncToUse = NULL;
			
			if (function_exists('iconv'))
			{
				$sFuncToUse = "iconv";
			}
			else if (function_exists('mb_convert_encoding'))
			{
				$sFuncToUse = "mb_convert_encoding";
			}
			else if ($this->_objXajax->getCharEncoding() == "ISO-8859-1")
			{
				$sFuncToUse = "utf8_decode";
			}
			else
			{
				trigger_error("The incoming xajax data could not be converted from UTF-8", E_USER_NOTICE);
			}
			
			if ($sFuncToUse)
			{
				if (is_string($sValue))
				{
					if ($sFuncToUse == "iconv")
					{
						$sValue = iconv("UTF-8", $this->_objXajax->getCharEncoding().'//TRANSLIT', $sValue);
					}
					else if ($sFuncToUse == "mb_convert_encoding")
					{
						$sValue = mb_convert_encoding($sValue, $this->_objXajax->getCharEncoding(), "UTF-8");
					}
					else
					{
						$sValue = utf8_decode($sValue);
					}
				}
			}
		}
		return $sValue;	
	}
}

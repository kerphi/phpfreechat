<?php
/**
 * xajax.inc.php :: Main xajax class and setup file
 *
 * xajax version 0.5 (Beta 1)
 * copyright (c) 2005 by Jared White & J. Max Wilson
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
 * @version $Id: xajax.inc.php 259 2006-10-03 18:14:49Z gaeldesign $
 * @copyright Copyright (c) 2005-2006 by Jared White & J. Max Wilson
 * @license http://www.xajaxproject.org/bsd_license.txt BSD License
 */

/*
   ----------------------------------------------------------------------------
   | Online documentation for this class is available on the xajax wiki at:   |
   | http://wiki.xajaxproject.org/Documentation:xajax.inc.php                 |
   ----------------------------------------------------------------------------
*/

/**
 * Define XAJAX_DEFAULT_CHAR_ENCODING that is used by both
 * the xajax and xajaxResponse classes
 */
if (!defined ('XAJAX_DEFAULT_CHAR_ENCODING'))
{
	define ('XAJAX_DEFAULT_CHAR_ENCODING', 'utf-8' );
}

require_once(dirname(__FILE__)."/xajaxResponse.inc.php");
require_once(dirname(__FILE__)."/plugin_layer/xajaxPluginManager.inc.php");
require_once(dirname(__FILE__)."/plugin_layer/xajaxResponsePlugin.inc.php");
require_once(dirname(__FILE__)."/plugin_layer/xajaxRequestProcessorPlugin.inc.php");
require_once(dirname(__FILE__)."/plugin_layer/xajaxIncludePlugin.inc.php");

/**
 * Communication Method Defines
 */
if (!defined ('XAJAX_GET'))
{
	define ('XAJAX_GET', 0);
}
if (!defined ('XAJAX_POST'))
{
	define ('XAJAX_POST', 1);
}

/**
 * The xajax class uses a modular plug-in system to facilitate the processing
 * of special Ajax requests made by a PHP page. It generates Javascript that
 * the page must include in order to make requests, and it handles the output
 * of response objects (see {@link xajaxResponse}). Many different flags and settings
 * can be adjusted to alter the behavior of the xajax class as well as the
 * client-side Javascript.
 * 
 * 
 * @package xajax
 */ 
class xajax
{
	
	/**#@+
	 * @access protected
	 */
	/**
	 * @var array Array of PHP functions that will be callable through
	 *            Javascript wrappers. Format is key=function name, value is
	 *            assoc. array: "callback"=function or object/class method
	 *            callback, "include"=PHP file to include (optional)
	 *            
	 */
	var $aFunctions;
	/**
	 * @var array Objects with methods that can be called via xajax.call()
	 */
	var $aCallableObjects;
	/**
	 * @var array Array of callbacks for xajax events
	 */
	var $aEventCallbacks;
	/**
	 * @var string The URI for making requests to the xajax object
	 */
	var $sRequestURI;
	/**
	 * @var string The prefix to prepend to the javascript wraper function name
	 */
	var $sWrapperPrefix;
	/**
	 * @var boolean Show debug messages (default false)
	 */
	var $bDebug;
	/**
	 * @var boolean Show messages in the client browser's status bar (default false)
	 */
	var $bStatusMessages;    
	/**
	 * @var boolean Allow xajax to exit after processing a request (default true)
	 */
	var $bExitAllowed;
	/**
	 * @var boolean Use wait cursor in browser (default true)
	 */
	var $bWaitCursor;
	/**
	 * @var boolean Use an special xajax error handler so the errors are sent to the browser properly (default false)
	 */
	var $bErrorHandler;
	/**
	 * @var string Specify what, if any, file xajax should log errors to (and more information in a future release)
	 */
	var $sLogFile;
	/**
	 * @var boolean Clean all output buffers before outputting response (default false)
	 */
	var $bCleanBuffer;
	/**
	 * @var string String containing the character encoding used
	 */
	var $sEncoding;
	/**
	 * @var boolean Decode input request args from UTF-8 (default false)
	 */
	var $bDecodeUTF8Input;
	/**
	 * @var boolean Convert special characters to HTML entities (default false)
	 */
	var $bOutputEntities;
	/**
	 * @var integer The number of milliseconds to wait before checking if xajax is loaded in the client, or 0 to disable (default 6000)
	 */
	var $iTimeout;
	/**
	 * @var boolean Allow xajax to send a blank response back to the client (default false)
	 */
	var $bAllowBlankResponse;
	
	/**#@-*/
	
	/**
	 * Sets up the xajax object and the plugin system
	 * 
	 * @param string  optional request URI; defaults to the current browser URI
	 */
	function xajax($sRequestURI="")
	{
		$this->aFunctions = array();
		$this->aCallableObjects = array();
		$this->aEvents = array(
			"beforeProcessing" => array(),
			"afterProcessing" => array(),
			"onMissingFunction" => array(),
			"onProcessingError" => array()
		);
		$this->sRequestURI = $sRequestURI;
		if ($this->sRequestURI == "")
			$this->sRequestURI = $this->_detectURI();
		$this->sWrapperPrefix = "xajax_";
		$this->setFlags(array(
			"debug" => false,
			"statusMessages" => false,
			"waitCursor" => true,
			"exitAllowed" => true,
			"errorHandler" => false,
			"cleanBuffer" => false,
			"decodeUTF8Input" => false,
			"outputEntities" => false,
			"allowBlankResponse" => false));
		$this->sLogFile = "";
		$this->setCharEncoding(XAJAX_DEFAULT_CHAR_ENCODING);
		$this->iTimeout = 6000;
		
		// Setup plugin manager
		$oPluginManager =& xajaxPluginManager::getInstance();
		$sMandatoryPluginFolder = dirname(__FILE__) . "/plugin_layer";
		$oPluginManager->addPluginFolder($sMandatoryPluginFolder);

		$sOptionalPluginFolder = dirname(dirname(__FILE__))."/xajax_plugins";
		if ($oPluginManager->addPluginFolder($sOptionalPluginFolder)) {
			// TODO...load plugins?
		}
		
		$oPluginManager->loadPluginFile("xajaxDefaultRequestProcessorPlugin");
		$oPluginManager->registerRequestProcessorPlugin(new xajaxDefaultRequestProcessorPlugin());			
		$oPluginManager->loadPluginFile("xajaxDefaultIncludePlugin");
		$oPluginManager->registerIncludePlugin(new xajaxDefaultIncludePlugin());			
	}
	
	/**
	 * Returns an {@link xajaxResponse} object set up with this xajax object's
	 * encoding and entity settings. Use this for singleton-pattern response
	 * development.
	 * 
	 * @return xajaxResponse
	 */
	function &getGlobalResponse()
	{
		static $obj;
		if (!$obj) {
			$obj = new xajaxResponse($this->sEncoding, $this->bOutputEntities);	
		}
		return $obj;
	}
	
	/**
	 * Returns the current xajax version.
	 *
	 * @return string 
	 */
	function getVersion()
	{
		return 'xajax 0.5 Beta 1';
	}
	
	/**
	 * Sets multiple flags based on the supplied associative array (see
	 * {@link xajax::setFlag()} for flag names)
	 * 
	 * @param array
	 */
	function setFlags($flags)
	{
		foreach ($flags as $name => $value) {
			$this->setFlag($name, $value);
		}
	}
	
	/**
	 * Sets a flag (boolean true or false). Available flags with their defaults
	 * are as follows:
	 * 
	 * <ul>
	 * <li>debug: false</li>
	 * <li>statusMessages: false</li>
	 * <li>waitCursor: true</li>
	 * <li>exitAllowed: true</li>
	 * <li>errorHandler: false</li>
	 * <li>cleanBuffer: false</li>
	 * <li>decodeUTF8Input: false</li>
	 * <li>outputEntities: false</li>
	 * <li>allowBlankResponse: false</li>
	 * </ul>
	 * 
	 * @param string name of flag
	 * @param boolean
	 */
	function setFlag($name, $value)
	{
		$sVar = "b" . ucfirst($name);
		if (array_key_exists($sVar, get_object_vars($this))) {
			$this->$sVar = (boolean)$value;
		}
		else {
			trigger_error("The flag \"$name\" could not be found", E_USER_ERROR);
		}
	}
	
	/**
	 * Returns the value of the flag
	 * 
	 * @return boolean
	 */
	function getFlag($name)
	{
		$sVar = "b" . ucfirst($name);
		if (array_key_exists($sVar, get_object_vars($this))) {
			return $this->$sVar;
		}
		else {
			return NULL;
		}
	}
	
	/**
	 * Sets the timeout before xajax notifies the client that xajax has not been loaded
	 * <i>Usage:</i> <kbd>$xajax->setTimeout(6000);</kbd>
	 *
	 * @param integer the number of milliseconds, or 0 to disable
	 */
	function setTimeout($iTimeout)
	{
		$this->iTimeout = $iTimeout;
	}
	
	/**
	 * Returns the xajax Javascript timeout
	 * 
	 * @return integer the number of milliseconds (or 0 if disabled)
	 */
	function getTimeout()
	{
		return $this->iTimeout;
	}
	
	/**
	 * Sets the URI to which requests will be made.
	 * <i>Usage:</i> <kbd>$xajax->setRequestURI("http://www.xajaxproject.org");</kbd>
	 * 
	 * @param string the URI (can be absolute or relative) of the PHP script
	 *               that will be accessed when an xajax request occurs
	 */
	function setRequestURI($sRequestURI)
	{
		$this->sRequestURI = $sRequestURI;
	}
	
	/**
	 * Returns the current request URI
	 * 
	 * @return string
	 */
	function getRequestURI()
	{
		return $this->sRequestURI;
	}

	/**
	 * Sets the prefix that will be appended to the Javascript wrapper
	 * functions (default is "xajax_").
	 * 
	 * @param string
	 */ 
	function setWrapperPrefix($sPrefix)
	{
		$this->sWrapperPrefix = $sPrefix;
	}
	
	/**
	 * Returns the Javascript wrapper prefix
	 * 
	 * @return string
	 */
	function getWrapperPrefix()
	{
		return $this->sWrapperPrefix;
	}
		
	/**
	 * Specifies a log file that will be written to by xajax during a request
	 * (used only by the error handling system at present). If you don't invoke
	 * this method, or you pass in "", then no log file will be written to.
	 * <i>Usage:</i> <kbd>$xajax->setLogFile("/xajax_logs/errors.log");</kbd>
	 */
	function setLogFile($sFilename)
	{
		$this->sLogFile = $sFilename;
	}
	
	/**
	 * Returns the log file to use for error output (or "" if no log file is
	 * to be used)
	 * 
	 * @return string
	 */
	function getLogFile()
	{
		return $this->sLogFile;
	}
	
	/**
	 * Sets the character encoding for the HTTP output based on
	 * <kbd>$sEncoding</kbd>, which is a string containing the character
	 * encoding to use. You don't need to use this method normally, since the
	 * character encoding for the response gets set automatically based on the
	 * <kbd>XAJAX_DEFAULT_CHAR_ENCODING</kbd> constant.
	 * <i>Usage:</i> <kbd>$xajax->setCharEncoding("utf-8");</kbd>
	 *
	 * @param string the encoding type to use (utf-8, iso-8859-1, etc.)
	 */
	function setCharEncoding($sEncoding)
	{
		$this->sEncoding = $sEncoding;
	}
	
	/**
	 * Returns the character encoding for the HTTP output
	 * 
	 * @return string
	 */
	function getCharEncoding()
	{
		return $this->sEncoding;
	}

	/**
	 * Registers a PHP function or method to be callable through xajax in your
	 * Javascript. If you want to register a function, pass in the name of that
	 * function. If you want to register a static class method, pass in an
	 * array like so:
	 * <kbd>array("myFunctionName", "myClass", "myMethod")</kbd>
	 * For an object instance method, use an object variable for the second
	 * array element (and in PHP 4 make sure you put an & before the variable
	 * to pass the object by reference). Note: the function name is what you
	 * call via Javascript, so it can be anything as long as it doesn't
	 * conflict with any other registered function name.
	 * 
	 * <i>Usage:</i> <kbd>$xajax->registerFunction("myFunction");</kbd>
	 * or: <kbd>$xajax->registerFunction(array("myFunctionName", &$myObject, "myMethod"));</kbd>
	 * 
	 * @param mixed  contains the function name or an object callback array
	 * @param string a PHP file to include before the function is called
	 *               (optional)
	 */
	function registerFunction($mFunction,$sIncludeFile=null)
	{
		if (is_array($mFunction)) {
			$this->aFunctions[$mFunction[0]] = array("callback" => array_slice($mFunction, 1));
		}	
		else {
			$this->aFunctions[$mFunction] = array("callback" => $mFunction);
		}
		
		if ($sIncludeFile)
		{		
			if (is_array($mFunction)) {
				$this->aFunctions[$mFunction[0]]["include"] = $sIncludeFile;
			}
			else {
				$this->aFunctions[$mFunction]["include"] = $sIncludeFile;
			}
		}
	}
	
	/**
	 * Registers an object whose methods will be searched for a match to the
	 * incoming request function name. If more than one callable object is
	 * registered, the first object that contains a method having the same name
	 * as the incoming function will be called.
	 */
	function registerCallableObject(&$oObject)
	{
		if (is_object($oObject)) {
			$this->aCallableObjects[get_class($oObject)] = &$oObject;
		}
		else {
			trigger_error("The registerCallableObject method requires an object to be provided", E_USER_WARNING);
		}
	}
	
	/**
	 * Registers a callback with an xajax event. Available events are:
	 * 
	 * <ul>
	 * <li>beforeProcessing -- triggered before the request is processed</li>
	 * <li>afterProcessing -- triggered after the request is processed</li>
	 * <li>onMissingFunction -- triggered if no function/method could be found<li>
	 * <li>onProcessingError -- triggered if there were an error during the
	 *                          request processing</li>
	 * </ul>
	 * 
	 * @param mixed  standard PHP function or object callback
	 * @param string name of the event
	 */
	function registerEvent($mCallback, $sEventName)
	{
		if (isset($this->aEvents[$sEventName])) {
			$this->aEvents[$sEventName][] = $mCallback;			
		}
		else {
			trigger_error("The registerEvent method did not recongize the event name: $sEventName", E_USER_WARNING);
		}
	}
	
	/**
	 * Returns an associative array of registered function definitions
	 * 
	 * @return array
	 */
	function getRegisteredFunctions()
	{
		return $this->aFunctions;
	}

	/**
	 * Returns an associative array of callable objects
	 * 
	 * @return array
	 */
	function getRegisteredCallableObjects()
	{
		return $this->aCallableObjects;
	}
	
	/**
	 * Returns an associative array of event definitions
	 * 
	 * @return array
	 */
	function getRegisteredEvents()
	{
		return $this->aEvents;
	}
		
	/**
	 * Returns true if xajax can process the request, false if otherwise.
	 * You can use this to determine if xajax needs to process the request or
	 * not. (executes request processor plugin)
	 * 
	 * @return boolean
	 */ 
	function canProcessRequest()
	{
		$objPluginManager =& xajaxPluginManager::getInstance();
		$objRequestProcessor = $objPluginManager->getRequestProcessorPlugin();
		return $objRequestProcessor->canProcessRequest();
	}
	
	/**
	 * Returns the current request mode (XAJAX_GET or XAJAX_POST), or -1 if
	 * there is none. (executes request processor plugin)
	 * 
	 * @return mixed
	 */
	function getRequestMode()
	{
		$objPluginManager =& xajaxPluginManager::getInstance();
		$objRequestProcessor = $objPluginManager->getRequestProcessorPlugin();
		return $objRequestProcessor->getRequestMode();
	}
	
	/**
	 * This is the main communications engine of xajax. The engine handles all
	 * incoming xajax requests, calls the apporiate PHP functions (or
	 * class/object methods) and passes the response back to the
	 * Javascript response handler. If your RequestURI is the same as your Web
	 * page then this function should be called before any headers or HTML has
	 * been sent. (executes request processor plugin)
	 */
	function processRequest()
	{	
		$objPluginManager =& xajaxPluginManager::getInstance();
		$objRequestProcessor = $objPluginManager->getRequestProcessorPlugin();
		$objRequestProcessor->setXajax($this);
		if ($objRequestProcessor->canProcessRequest()) {
			$objRequestProcessor->processRequest();
		}
	}
			
	/**            
	 * Prints the xajax Javascript header and wrapper code into your page by
	 * printing the output of the getJavascript() method. It should only be
	 * called between the <pre><head> </head></pre> tags in your HTML page.
	 * Remember, if you only want to obtain the result of this function, use
	 * {@link xajax::getJavascript()} instead. (executes Javascript include
	 * plugin)
	 * 
	 * <i>Usage:</i>
	 * <code>
	 *  <head>
	 *        ...
	 *        < ?php $xajax->printJavascript(); ? >
	 * </code>
	 * 
	 * @param string the relative address of the folder where xajax has been
	 *               installed. For instance, if your PHP file is
	 *               "http://www.myserver.com/myfolder/mypage.php"
	 *               and xajax was installed in
	 *               "http://www.myserver.com/anotherfolder", then $sJsURI
	 *               should be set to "../anotherfolder". Defaults to assuming
	 *               xajax is in the same folder as your PHP file.
	 * @param string the relative folder/file pair of the xajax Javascript
	 *               engine located within the xajax installation folder.
	 *               Defaults to xajax_js/xajax.js.
	 */
	function printJavascript($sJsURI="", $sJsFile=NULL)
	{
		$objPluginManager =& xajaxPluginManager::getInstance();
		$objInclude = $objPluginManager->getIncludePlugin();
		$objInclude->setXajax($this);
		$objInclude->setFunctions($this->aFunctions);
		print $objInclude->getJavascript($sJsURI, $sJsFile);
	}
	
	/**
	 * Returns the xajax Javascript code that should be added to your HTML page
	 * between the <kbd><head> </head></kbd> tags. (executes Javascript include
	 * plugin)
	 * 
	 * <i>Usage:</i>
	 * <code>
	 *  < ?php $xajaxJSHead = $xajax->getJavascript(); ? >
	 *    <head>
	 *        ...
	 *        < ?php echo $xajaxJSHead; ? >
	 * </code>
	 * 
	 * @param string the relative address of the folder where xajax has been
	 *               installed. For instance, if your PHP file is
	 *               "http://www.myserver.com/myfolder/mypage.php"
	 *               and xajax was installed in
	 *               "http://www.myserver.com/anotherfolder", then $sJsURI
	 *               should be set to "../anotherfolder". Defaults to assuming
	 *               xajax is in the same folder as your PHP file.
	 * @param string the relative folder/file pair of the xajax Javascript
	 *               engine located within the xajax installation folder.
	 *               Defaults to xajax_js/xajax.js.
	 * @return string
	 */
	function getJavascript($sJsURI="", $sJsFile=NULL)
	{	
		$objPluginManager =& xajaxPluginManager::getInstance();
		$objInclude = $objPluginManager->getIncludePlugin();
		$objInclude->setXajax($this);
		$objInclude->setFunctions($this->aFunctions);
		return $objInclude->getJavascript($sJsURI, $sJsFile);
	}
	
	/**
	 * Returns a string containing inline Javascript that sets up the xajax
	 * runtime (typically called internally by xajax from get/printJavascript).
	 * (executes Javascript include plugin)
	 * 
	 * @return string
	 */
	function getJavascriptConfig()
	{
		$objPluginManager =& xajaxPluginManager::getInstance();
		$objInclude = $objPluginManager->getIncludePlugin();
		$objInclude->setXajax($this);
		$objInclude->setFunctions($this->aFunctions);
		return $objInclude->getJavascriptConfig();
	}
	
	/**
	 * Returns a string containing a Javascript include of the xajax.js file
	 * along with a check to see if the file loaded after six seconds
	 * (typically called internally by xajax from get/printJavascript).
	 * (executes Javascript include plugin)
	 * 
	 * @param string the relative address of the folder where xajax has been
	 *               installed. For instance, if your PHP file is
	 *               "http://www.myserver.com/myfolder/mypage.php"
	 *               and xajax was installed in
	 *               "http://www.myserver.com/anotherfolder", then $sJsURI
	 *               should be set to "../anotherfolder". Defaults to assuming
	 *               xajax is in the same folder as your PHP file.
	 * @param string the relative folder/file pair of the xajax Javascript
	 *               engine located within the xajax installation folder.
	 *               Defaults to xajax_js/xajax.js.
	 * @return string
	 */
	function getJavascriptInclude($sJsURI="", $sJsFile=NULL)
	{
		$objPluginManager =& xajaxPluginManager::getInstance();
		$objInclude = $objPluginManager->getIncludePlugin();
		$objInclude->setXajax($this);
		$objInclude->setFunctions($this->aFunctions);
		return $objInclude->getJavascriptInclude($sJsURI, $sJsFile);
	}

	/**
	 * This method can be used to create a new xajax.js file out of the
	 * xajax_uncompressed.js file (which will only happen if xajax.js doesn't
	 * already exist on the filesystem).
	 * 
	 * @param string an optional argument containing the full server file path
	 *               of xajax.js.
	 */
	function autoCompressJavascript($sJsFullFilename=NULL)
	{	
		$sJsFile = "xajax_js/xajax.js";
		
		if ($sJsFullFilename) {
			$realJsFile = $sJsFullFilename;
		}
		else {
			$realPath = realpath(dirname(dirname(__FILE__)));
			$realJsFile = $realPath . "/". $sJsFile;
		}

		// Create a compressed file if necessary
		if (!file_exists($realJsFile)) {
			$srcFile = str_replace(".js", "_uncompressed.js", $realJsFile);
			if (!file_exists($srcFile)) {
				trigger_error("The xajax uncompressed Javascript file could not be found in the <b>" . dirname($realJsFile) . "</b> folder. Error ", E_USER_ERROR);	
			}
			require(dirname(__FILE__)."/xajaxCompress.inc.php");
			$javaScript = implode('', file($srcFile));
			$compressedScript = xajaxCompressJavascript($javaScript);
			$fH = @fopen($realJsFile, "w");
			if (!$fH) {
				trigger_error("The xajax compressed javascript file could not be written in the <b>" . dirname($realJsFile) . "</b> folder. Error ", E_USER_ERROR);
			}
			else {
				fwrite($fH, $compressedScript);
				fclose($fH);
			}
		}
	}
	
	/**
	 * Returns the current URL based upon the SERVER vars.
	 * 
	 * @access private
	 * @return string
	 */
	function _detectURI() {
		$aURL = array();

		// Try to get the request URL
		if (!empty($_SERVER['REQUEST_URI'])) {
			$aURL = parse_url($_SERVER['REQUEST_URI']);
		}

		// Fill in the empty values
		if (empty($aURL['scheme'])) {
			if (!empty($_SERVER['HTTP_SCHEME'])) {
				$aURL['scheme'] = $_SERVER['HTTP_SCHEME'];
			} else {
				$aURL['scheme'] = (!empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) != 'off') ? 'https' : 'http';
			}
		}

		if (empty($aURL['host'])) {
			if (!empty($_SERVER['HTTP_X_FORWARDED_HOST'])) {
				if (strpos($_SERVER['HTTP_X_FORWARDED_HOST'], ':') > 0) {
					list($aURL['host'], $aURL['port']) = explode(':', $_SERVER['HTTP_X_FORWARDED_HOST']);
				} else {
					$aURL['host'] = $_SERVER['HTTP_X_FORWARDED_HOST'];
				}
			} else if (!empty($_SERVER['HTTP_HOST'])) {
				if (strpos($_SERVER['HTTP_HOST'], ':') > 0) {
					list($aURL['host'], $aURL['port']) = explode(':', $_SERVER['HTTP_HOST']);
				} else {
					$aURL['host'] = $_SERVER['HTTP_HOST'];
				}
			} else if (!empty($_SERVER['SERVER_NAME'])) {
				$aURL['host'] = $_SERVER['SERVER_NAME'];
			} else {
				print "xajax Error: xajax failed to automatically identify your Request URI.";
				print "Please set the Request URI explicitly when you instantiate the xajax object.";
				exit();
			}
		}

		if (empty($aURL['port']) && !empty($_SERVER['SERVER_PORT'])) {
			$aURL['port'] = $_SERVER['SERVER_PORT'];
		}

		if (empty($aURL['path'])) {
			if (!empty($_SERVER['PATH_INFO'])) {
				$sPath = parse_url($_SERVER['PATH_INFO']);
			} else {
				$sPath = parse_url($_SERVER['PHP_SELF']);
			}
			$aURL['path'] = $sPath['path'];
			unset($sPath);
		}

		if (!empty($aURL['query'])) {
			$aURL['query'] = '?'.$aURL['query'];
		}

		// Build the URL: Start with scheme, user and pass
		$sURL = $aURL['scheme'].'://';
		if (!empty($aURL['user'])) {
			$sURL.= $aURL['user'];
			if (!empty($aURL['pass'])) {
				$sURL.= ':'.$aURL['pass'];
			}
			$sURL.= '@';
		}

		// Add the host
		$sURL.= $aURL['host'];

		// Add the port if needed
		if (!empty($aURL['port']) && (($aURL['scheme'] == 'http' && $aURL['port'] != 80) || ($aURL['scheme'] == 'https' && $aURL['port'] != 443))) {
			$sURL.= ':'.$aURL['port'];
		}

		// Add the path and the query string
		$sURL.= $aURL['path'].@$aURL['query'];

		// Clean up
		unset($aURL);
		return $sURL;
	}
		
}// end class xajax 

/**
 * This function is registered with PHP's set_error_handler() function if
 * the xajax error handling system is turned on.
 */
function xajaxErrorHandler($errno, $errstr, $errfile, $errline)
{
	$errorReporting = error_reporting();
	if (($errno & $errorReporting) == 0) return;
	
	if ($errno == E_NOTICE) {
		$errTypeStr = "NOTICE";
	}
	else if ($errno == E_WARNING) {
		$errTypeStr = "WARNING";
	}
	else if ($errno == E_USER_NOTICE) {
		$errTypeStr = "USER NOTICE";
	}
	else if ($errno == E_USER_WARNING) {
		$errTypeStr = "USER WARNING";
	}
	else if ($errno == E_USER_ERROR) {
		$errTypeStr = "USER FATAL ERROR";
	}
	else if (defined('E_STRICT') && $errno == E_STRICT) {
		return;
	}
	else {
		$errTypeStr = "UNKNOWN: $errno";
	}
	$GLOBALS['xajaxErrorHandlerText'] .= "\n----\n[$errTypeStr] $errstr\nerror in line $errline of file $errfile";
}

?>

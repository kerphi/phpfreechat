<?php
/**
 * xajaxResponse.inc.php :: xajax XML response class
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
 * @version $Id: xajaxResponse.inc.php 259 2006-10-03 18:14:49Z gaeldesign $
 * @copyright Copyright (c) 2005-2006 by Jared White & J. Max Wilson
 * @license http://www.xajaxproject.org/bsd_license.txt BSD License
 */

/*
   ----------------------------------------------------------------------------
   | Online documentation for this class is available on the xajax wiki at:   |
   | http://wiki.xajaxproject.org/Documentation:xajaxResponse.inc.php         |
   ----------------------------------------------------------------------------
*/

/**
 * The xajaxResponse class is used to create responses to be sent back to your
 * Web page.  A response contains one or more command messages for updating
 * your page.
 * Currently xajax supports 23 kinds of command messages, including some common
 * ones such as:
 * <ul>
 * <li>Assign - sets the specified attribute of an element in your page</li>
 * <li>Append - appends data to the end of the specified attribute of an
 * element in your page</li>
 * <li>Prepend - prepends data to the beginning of the specified attribute of
 * an element in your page</li>
 * <li>Replace - searches for and replaces data in the specified attribute of
 * an element in your page</li>
 * <li>Script - runs the supplied JavaScript code</li>
 * <li>Alert - shows an alert box with the supplied message text</li>
 * </ul>
 *
 * <i>Note:</i> elements are identified by their HTML id, so if you don't see
 * your browser HTML display changing from the request, make sure you're using
 * the right id names in your response.
 * 
 * @package xajax
 */
class xajaxResponse
{
	/**#@+
	 * @access protected
	 */
	/**
	 * @var array internal command storage
	 */    
	var $aCommands;
	/**
	 * @var string the encoding type to use
	 */
	var $sEncoding;
	/**
	 * @var boolean if special characters in the XML should be converted to
	 *              entities
	 */
	var $bOutputEntities;

	/**#@-*/
	
	/**
	 * The constructor's main job is to set the character encoding for the
	 * response.
	 * 
	 * <i>Note:</i> to change the character encoding for all of the
	 * responses, set the XAJAX_DEFAULT_ENCODING constant before you
	 * instantiate xajax.
	 * 
	 * @param string  contains the character encoding string to use
	 * @param boolean lets you set if you want special characters in the output
	 *                converted to HTML entities
	 * 
	 */
	function xajaxResponse($sEncoding=XAJAX_DEFAULT_CHAR_ENCODING, $bOutputEntities=false)
	{
		$this->setCharEncoding($sEncoding);
		$this->bOutputEntities = $bOutputEntities;
		$this->aCommands = array();
	}
	
	/**
	 * Sets the character encoding for the response based on $sEncoding, which
	 * is a string containing the character encoding to use. You don't need to
	 * use this method normally, since the character encoding for the response
	 * gets set automatically based on the XAJAX_DEFAULT_CHAR_ENCODING
	 * constant.
	 * 
	 * @param string
	 */
	function setCharEncoding($sEncoding)
	{
		$this->sEncoding = $sEncoding;
		return $this;
	}
	
	/**
	 * If true, tells the response object to convert special characters to HTML
	 * entities automatically (only works if the mb_string extension is
	 * available).
	 */
	function setOutputEntities($bOption)
	{
		$this->bOutputEntities = (boolean)$bOption;
		return $this;
	}
	
	/**
	 * Provides access to the xajaxResponse plugin system. If you use PHP 4 or
	 * 5, pass the plugin name as the first argument, the plugin's method name
	 * as the second argument, and subsequent arguments (if any) after that.
	 * Optionally, if you use PHP 5, you can pass just the plugin name as the
	 * first argument and the plugin object will be returned which you can use
	 * to call the appropriate method.
	 * 
	 * @param string name of the plugin to call
	 */
	function &plugin($sName)
	{
		$objManager =& xajaxPluginManager::getInstance();
		$objPlugin =& $objManager->getResponsePlugin($sName);
		$objPlugin->setResponseObject($this);
		$aArgs = func_get_args();
		array_shift($aArgs);
		if (!empty($aArgs)) {
			$sMethodName = array_shift($aArgs);
			call_user_func_array(array(&$objPlugin, $sMethodName), $aArgs);
		}
		return $objPlugin;
	}
	
	/**
	 * Internal function for PHP5 only.  Used to permit plugins to be called as
	 * if they were native member variables of the xajaxResponse class.	 
	 * 
	 * <i>PHP5 Usage:</i> <kbd>$objResponse->myPlugin->myPluginMethod("param1", "param2");</kbd>
	 *
	 * @param string	The name of the callName of a responsePlugin that has been registered with the plugin manager 
	 */
	function __get($sPluginName)
	{
		return $this->plugin($sPluginName);
	}
	
	/**
	 * Adds a confirm commands command message to the response.
	 * 
	 * <i>Usage:</i> <kbd>$objResponse->confirmCommands(1, "Do you want to preview the new data?");</kbd>
	 *
	 * @param integer the number of commands to skip if the user presses
	 *                Cancel in the browsers's confirm dialog
	 * @param string  the message to show in the browser's confirm dialog
	 */
	function confirmCommands($iCmdNumber, $sMessage)
	{
	    $this->addCommand(array("n"=>"cc","t"=>$iCmdNumber),$sMessage);
	    return $this;
	}
	
	/**
	 * Adds an assign command message to the response.
	 * 
	 * <i>Usage:</i> <kbd>$objResponse->assign("contentDiv", "innerHTML", "Some Text");</kbd>
	 * 
	 * @param string contains the id of an HTML element
	 * @param string the part of the element you wish to modify ("innerHTML",
	 *               "value", etc.)
	 * @param string the data you want to set the attribute to
	 */
	function assign($sTarget,$sAttribute,$sData)
	{
		$this->addCommand(array("n"=>"as","t"=>$sTarget,"p"=>$sAttribute),$sData);
		return $this;
	}
	
	/**
	 * Adds an append command message to the response.
	 * 
	 * <i>Usage:</i> <kbd>$objResponse->append("contentDiv", "innerHTML", "Some New Text");</kbd>
	 * 
	 * @param string contains the id of an HTML element
	 * @param string the part of the element you wish to modify ("innerHTML",
	 *               "value", etc.)
	 * @param string the data you want to append to the end of the attribute
	 */
	function append($sTarget,$sAttribute,$sData)
	{	
		$this->addCommand(array("n"=>"ap","t"=>$sTarget,"p"=>$sAttribute),$sData);
		return $this;
	}
	
	/**
	 * Adds an prepend command message to the response.
	 * 
	 * <i>Usage:</i> <kbd>$objResponse->prepend("contentDiv", "innerHTML", "Some Starting Text");</kbd>
	 * 
	 * @param string contains the id of an HTML element
	 * @param string the part of the element you wish to modify ("innerHTML",
	 *               "value", etc.)
	 * @param string the data you want to prepend to the beginning of the
	 *               attribute
	 */
	 function prepend($sTarget,$sAttribute,$sData)
	{
		$this->addCommand(array("n"=>"pp","t"=>$sTarget,"p"=>$sAttribute),$sData);
		return $this;
	}
	
	/**
	 * Adds a replace command message to the response.
	 * 
	 * <i>Usage:</i> <kbd>$objResponse->replace("contentDiv", "innerHTML", "text", "<b>text</b>");</kbd>
	 * 
	 * @param string contains the id of an HTML element
	 * @param string the part of the element you wish to modify ("innerHTML",
	 *               "value", etc.)
	 * @param string the string to search for
	 * @param string the string to replace the search string when found in the
	 *               attribute
	 */
	function replace($sTarget,$sAttribute,$sSearch,$sData)
	{
		$aData = array('s'=>$sSearch, 'r'=>$sData);
		$this->addCommand(array("n"=>"rp","t"=>$sTarget,"p"=>$sAttribute),$aData);
		return $this;
	}
	
	/**
	 * Adds a clear command message to the response.
	 * 
	 * <i>Usage:</i> <kbd>$objResponse->clear("contentDiv", "innerHTML");</kbd>
	 * 
	 * @param string contains the id of an HTML element
	 * @param string the part of the element you wish to clear ("innerHTML",
	 *               "value", etc.)
	 */    
	function clear($sTarget,$sAttribute)
	{
		$this->assign($sTarget,$sAttribute,'');
		return $this;
	}
	
	/**
	 * Adds an alert command message to the response.
	 * 
	 * <i>Usage:</i> <kbd>$objResponse->alert("This is important information");</kbd>
	 * 
	 * @param string the text to be displayed in the Javascript alert box
	 */
	function alert($sMsg)
	{
		$this->addCommand(array("n"=>"al"),$sMsg);
		return $this;
	}
	
	/**
	 * Uses the addScript() method to add a Javascript redirect to another URL.
	 * 
	 * <i>Usage:</i> <kbd>$objResponse->redirect("http://www.xajaxproject.org");</kbd>
	 * 
	 * @param string the URL to redirect the client browser to
	 */   
	function redirect($sURL, $iDelay=0)
	{
		//we need to parse the query part so that the values are rawurlencode()'ed
		//can't just use parse_url() cos we could be dealing with a relative URL which
		//  parse_url() can't deal with.
		$queryStart = strpos($sURL, '?', strrpos($sURL, '/'));
		if ($queryStart !== FALSE)
		{
			$queryStart++;
			$queryEnd = strpos($sURL, '#', $queryStart);
			if ($queryEnd === FALSE)
				$queryEnd = strlen($sURL);
			$queryPart = substr($sURL, $queryStart, $queryEnd-$queryStart);
			parse_str($queryPart, $queryParts);
			$newQueryPart = "";
			if ($queryParts)
			{
				$first = true;
				foreach($queryParts as $key => $value)
				{
					if ($first)
						$first = false;
					else
						$newQueryPart .= ini_get('arg_separator.output');
					$newQueryPart .= rawurlencode($key).'='.rawurlencode($value);
				}
			} else if ($_SERVER['QUERY_STRING']) {
				//couldn't break up the query, but there's one there
				//possibly "http://url/page.html?query1234" type of query?
				//just encode it and hope it works
				$newQueryPart = rawurlencode($_SERVER['QUERY_STRING']);
			}
			$sURL = str_replace($queryPart, $newQueryPart, $sURL);
		}
		if ($iDelay)
			$this->script('window.setTimeout("window.location = \''.$sURL.'\';",'.($iDelay*1000).');');
		else
			$this->script('window.location = "'.$sURL.'";');
		return $this;
	}

	/**
	 * Adds a Javascript command message to the response.
	 * 
	 * <i>Usage:</i> <kbd>$objResponse->script("var x = prompt('get some text');");</kbd>
	 * 
	 * @param string contains Javascript code to be executed
	 */
	function script($sJS)
	{
		$this->addCommand(array("n"=>"js"),$sJS);
		return $this;
	}
	
	/**
	 * Adds a Javascript function call command message to the response.
	 * 
	 * <i>Usage:</i> <kbd>$objResponse->call("myJSFunction", "arg 1", "arg 2", 12345);</kbd>
	 * 
	 * @param string $sFunc the name of a Javascript function
	 * @param mixed $args,... optional arguments to pass to the Javascript function
	 */
	function call() {
	    $aArgs = func_get_args();
	    $sFunc = array_shift($aArgs);
	    $aData = $this->_buildObj($aArgs);
	    $this->addCommand(array("n"=>"jc","t"=>$sFunc),$aData);
	    return $this;
	}
	
	/**
	 * Adds a remove element command message to the response.
	 * 
	 * <i>Usage:</i> <kbd>$objResponse->remove("Div2");</kbd>
	 * 
	 * @param string contains the id of an HTML element to be removed
	 */
	function remove($sTarget)
	{
		$this->addCommand(array("n"=>"rm","t"=>$sTarget),'');
		return $this;
	}
	
	/**
	 * Adds a create element command message to the response.
	 * 
	 * <i>Usage:</i> <kbd>$objResponse->create("parentDiv", "h3", "myid");</kbd>
	 * 
	 * @param string contains the id of an HTML element to to which the new
	 *               element will be appended.
	 * @param string the tag to be added
	 * @param string the id to be assigned to the new element
	 * @param string deprecated, use the addCreateInput() method instead
	 */
	function create($sParent, $sTag, $sId, $sType="")
	{
		if ($sType)
		{
			trigger_error("The \$sType parameter of addCreate has been deprecated.  Use the addCreateInput() method instead.", E_USER_WARNING);
			return;
		}
		$this->addCommand(array("n"=>"ce","t"=>$sParent,"p"=>$sId),$sTag);
		return $this;
	}
	
	/**
	 * Adds a insert element command message to the response.
	 * 
	 * <i>Usage:</i> <kbd>$objResponse->insert("childDiv", "h3", "myid");</kbd>
	 * 
	 * @param string contains the id of the child before which the new element
	 *               will be inserted
	 * @param string the tag to be added
	 * @param string the id to be assigned to the new element
	 */
	function insert($sBefore, $sTag, $sId)
	{
		$this->addCommand(array("n"=>"ie","t"=>$sBefore,"p"=>$sId),$sTag);
		return $this;
	}

	/**
	 * Adds a insert element command message to the response.
	 * 
	 * <i>Usage:</i> <kbd>$objResponse->insertAfter("childDiv", "h3", "myid");</kbd>
	 * 
	 * @param string contains the id of the child after which the new element
	 *               will be inserted
	 * @param string the tag to be added
	 * @param string the id to be assigned to the new element
	 */
	function insertAfter($sAfter, $sTag, $sId)
	{
		$this->addCommand(array("n"=>"ia","t"=>$sAfter,"p"=>$sId),$sTag);
		return $this;
	}
	
	/**
	 * Adds a create input command message to the response.
	 * 
	 * <i>Usage:</i> <kbd>$objResponse->createInput("form1", "text", "username", "input1");</kbd>
	 * 
	 * @param string contains the id of an HTML element to which the new input
	 *               will be appended
	 * @param string the type of input to be created (text, radio, checkbox,
	 *               etc.)
	 * @param string the name to be assigned to the new input and the variable
	 *               name when it is submitted
	 * @param string the id to be assigned to the new input
	 */
	function createInput($sParent, $sType, $sName, $sId)
	{
		$this->addCommand(array("n"=>"ci","t"=>$sParent,"p"=>$sId,"c"=>$sType),$sName);
		return $this;
	}
	
	/**
	 * Adds an insert input command message to the response.
	 * 
	 * <i>Usage:</i> <kbd>$objResponse->insertInput("input5", "text", "username", "input1");</kbd>
	 * 
	 * @param string contains the id of the child before which the new element
	 *               will be inserted
	 * @param string the type of input to be created (text, radio, checkbox,
	 *               etc.)
	 * @param string the name to be assigned to the new input and the variable
	 *               name when it is submitted
	 * @param string the id to be assigned to the new input
	 */
	function insertInput($sBefore, $sType, $sName, $sId)
	{
		$this->addCommand(array("n"=>"ii","t"=>$sBefore,"p"=>$sId,"c"=>$sType),$sName);
		return $this;
	}
	
	/**
	 * Adds an insert input command message to the response.
	 * 
	 * <i>Usage:</i> <kbd>$objResponse->insertInputAfter("input7", "text", "email", "input2");</kbd>
	 * 
	 * @param string contains the id of the child after which the new element
	 *               will be inserted
	 * @param string the type of input to be created (text, radio, checkbox,
	 *               etc.)
	 * @param string the name to be assigned to the new input and the variable
	 *               name when it is submitted
	 * @param string the id to be assigned to the new input
	 */
	function insertInputAfter($sAfter, $sType, $sName, $sId)
	{
	    $this->addCommand(array("n"=>"iia","t"=>$sAfter,"p"=>$sId,"c"=>$sType),$sName);
	    return $this;
	}
	
	/**
	 * Adds an event command message to the response.
	 * 
	 * <i>Usage:</i> <kbd>$objResponse->addEvent("contentDiv", "onclick", "alert(\'Hello World\');");</kbd>
	 * 
	 * @param string contains the id of an HTML element
	 * @param string the event you wish to set ("onclick", "onmouseover", etc.)
	 * @param string the Javascript string you want the event to invoke
	 */
	function addEvent($sTarget,$sEvent,$sScript)
	{
		$this->addCommand(array("n"=>"ev","t"=>$sTarget,"p"=>$sEvent),$sScript);
		return $this;
	}
	
	/**
	 * Adds a handler command message to the response.
	 * 
	 * <i>Usage:</i> <kbd>$objResponse->addHandler("contentDiv", "onclick", "content_click");</kbd>
	 * 
	 * @param string contains the id of an HTML element
	 * @param string the event you wish to set ("onclick", "onmouseover", etc.)
	 * @param string the name of a Javascript function that will handle the
	 *               event. Multiple handlers can be added for the same event
	 */
	function addHandler($sTarget,$sEvent,$sHandler)
	{	
		$this->addCommand(array("n"=>"ah","t"=>$sTarget,"p"=>$sEvent),$sHandler);
		return $this;
	}
	
	/**
	 * Adds a remove handler command message to the response.
	 * 
	 * <i>Usage:</i> <kbd>$objResponse->removeHandler("contentDiv", "onclick", "content_click");</kbd>
	 * 
	 * @param string contains the id of an HTML element
	 * @param string the event you wish to remove ("onclick", "onmouseover",
	 *               etc.)
	 * @param string the name of a Javascript handler function that you want to
	 *               remove
	 */
	function removeHandler($sTarget,$sEvent,$sHandler)
	{	
		$this->addCommand(array("n"=>"rh","t"=>$sTarget,"p"=>$sEvent),$sHandler);
		return $this;
	}
	
	/**
	 * Adds an include script command message to the response.
	 * 
	 * <i>Usage:</i> <kbd>$objResponse->includeScript("functions.js");</kbd>
	 * 
	 * @param string URL of the Javascript file to include
	 */
	function includeScript($sFileName)
	{
		$this->addCommand(array("n"=>"in"),$sFileName);
		return $this;
	}
	
	/**
	 * Adds an include script once command message to the response.
	 * 
	 * <i>Usage:</i> <kbd>$objResponse->includeScriptOnce("functions2.js");</kbd>
	 * 
	 * @param string URL of the Javascript file to include
	 */
	function includeScriptOnce($sFileName)
	{
		$this->addCommand(array("n"=>"ino"),$sFileName);
		return $this;
	}
	
	/**
	 * Adds an include CSS command message to the response.
	 * 
	 * <i>Usage:</i> <kbd>$objResponse->includeCSS("stylesheet.css");</kbd>
	 * 
	 * @param string URL of the CSS file to include
	 */
	function includeCSS($sFileName)
	{
		$this->addCommand(array("n"=>"css"),$sFileName);
		return $this;
	}
	
	/**
	 * Returns the content type of the response (typically "text/xml").
	 * 
	 * @return string
	 */
	function getContentType()
	{
		return 'text/xml';
	}
	
	/**
	 * Returns the output of the response suitable for sending to a Web browser
	 * (i. e., XML or JSON)
	 * 
	 * @return string
	 */
	function getOutput()
	{
		$xml = "";
		if (is_array($this->aCommands))
		{
			foreach($this->aCommands as $aCommand)
			{
				$sData = $aCommand['data'];
				unset($aCommand['data']);
				$xml .= $this->_getXMLForCommand($aCommand, $sData);
			}
		}
		
		$sXML = "<?xml version=\"1.0\"";
		if ($this->sEncoding && strlen(trim($this->sEncoding)) > 0)
			$sXML .= " encoding=\"".$this->sEncoding."\"";
		$sXML .= " ?"."><xjx>" . $xml . "</xjx>";
		
		return $sXML;
	}
	
	/**
	 * Returns the number of commands current in the response
	 * 
	 * @return integer
	 */
	function getCommandCount()
	{
		return count($this->aCommands);
	}
	

	/**
	 * Adds the commands of the provided response to this response object
	 * 
	 * <i>Usage:</i>
	 * <code>$objResponse2->loadCommands($objResponse1);
	 * return $objResponse2;</code>
	 * 
	 * @param mixed the response object to add to the end of this response
	 *              object, or an array of response commands
	 */
	function loadCommands($mCommands, $bBefore=false)
	{
		if (is_a($mCommands, "xajaxResponse")) {
			if ($bBefore) {
				$this->aCommands = array_merge($mCommands->aCommands, $this->aCommands);
			}
			else {
				$this->aCommands = array_merge($this->aCommands, $mCommands->aCommands);
			}
		}
		else if (is_array($mCommands)) {
			if ($bBefore) {
				$this->aCommands = array_merge($mCommands, $this->aCommands);
			}
			else {
				$this->aCommands = array_merge($this->aCommands, $mCommands);
			}
		}
		else {
			if (!empty($mCommands))
				trigger_error("The xajax response output could not load other commands as data was not a valid array", E_USER_ERROR);
		}
	}
	
	/**
	 * Used internally by the response plugin system
	 * 
	 * @param xajaxResponsePlugin response plugin (subclass of xajaxResponsePlugin)
	 * @param array associative array of command attributes
	 * @param mixed command data
	 */
	function addPluginCommand($objPlugin, $aAttributes, $mData)
	{
		$aAttributes["plg"] = $objPlugin->sCallName;
		$this->addCommand($aAttributes, $mData);
	}

	/**
	 * Generates XML from command data
	 * 
	 * @access private
	 * @param array associative array of attributes
	 * @param mixed data
	 * @return string XML command
	 */
	function _getXMLForCommand($aAttributes, $mData)
	{
		$xml = "<cmd";
		foreach($aAttributes as $sAttribute => $sValue)
		{
			if ($sAttribute)
				$xml .= " $sAttribute=\"$sValue\"";
		}
		if (is_array($mData)) {
			$xml .= ">".$this->_arrayToXML($mData)."</cmd>";
		} else {
			$xml .= ">".$this->_escape($mData)."</cmd>";
		}
		
		return $xml;
	}
	
	/**
	 * Converts an array of data into XML
	 * 
	 * @access private
	 * @param mixed associative array of data or string of data
	 * @return string XML command
	 */
	function _arrayToXML($mArray)
	{
		if (!is_array($mArray))
			return $this->_escape($mArray);
		$xml = "";
		foreach($mArray as $sKey=>$sValue)
		{
			$xml .= '<'.htmlentities($sKey).'>';
			$xml .= $this->_arrayToXML($sValue);
			$xml .= '</'.htmlentities($sKey).'>';	
		}
		return $xml;
	}
	
	/**
	 * Adds a commmand to the array of all commands
	 * 
	 * @param array associative array of attributes
	 * @param mixed data
	 */
	function addCommand($aAttributes, $mData)
	{
		$aAttributes['data'] = $mData;
		$this->aCommands[] = $aAttributes;
	}
	
	/**
	 * Escapes the data.  Can be overridden to allow other transports to send
	 * data.
	 * 
	 * @access private
	 * @param string data
	 * @return string escaped data
	 */
	function _escape($sData)
	{
		if ($sData !== null && strpos($sData,'<![CDATA[')===FALSE) {
			if ($this->bOutputEntities) {
				if (function_exists('mb_convert_encoding')) {
					$sData = call_user_func_array('mb_convert_encoding', array(&$sData, 'HTML-ENTITIES', $this->sEncoding));
				}
				else {
					trigger_error("The xajax response output could not be converted to HTML entities because the mb_convert_encoding function is not available", E_USER_NOTICE);
				}
			}
		}
		else if ($sData === null) {
			$sData = "";
		}
		$sData = str_replace(']]>',']]]]><![CDATA[>', $sData);
		$sData = "<![CDATA[$sData]]>";
		return $sData;
	}
	
	/**
	 * Recursively serializes a data structure in an array so it can be sent to
	 * the client. It could be thought of as the opposite of
	 * {@link xajax::_parseObjXml()}.
	 * 
	 * @access private
	 * @param mixed data structure to serialize
	 * @return array data ready for insertion into command list array
	 */
	function _buildObj($mData) {
	    if (gettype($mData) == "object") $mData = get_object_vars($mData);
	    if (!is_array($mData)) {
	        return $this->_escape($mData);
	    }
	    else {
	    	$aData = array();
	        foreach ($mData as $key => $value) {
	            $aData['k'] = htmlentities($key);
	            $aData['v'] = $this->_buildObj($value);
	        }
	        return $aData;
	    }
	}
	
}// end class xajaxResponse
?>

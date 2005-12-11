<?php
///////////////////////////////////////////////////////////////////////////////
// xajax version 0.1 beta4
// copyright (c) 2005 by J. Max Wilson
// http://xajax.sourceforge.net
//
//
// xajax is an open source PHP class library for easily creating powerful
// PHP-driven, web-based AJAX Applications. Using xajax, you can asynchronously
// call PHP functions and update the content of your your webpage without
// reloading the page.
//
// xajax is released under the terms of the LGPL license
// http://www.gnu.org/copyleft/lesser.html#SEC3
//
// This library is free software; you can redistribute it and/or
// modify it under the terms of the GNU Lesser General Public
// License as published by the Free Software Foundation; either
// version 2.1 of the License, or (at your option) any later version.
//
// This library is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
// Lesser General Public License for more details.
// 
// You should have received a copy of the GNU Lesser General Public
// License along with this library; if not, write to the Free Software
// Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
///////////////////////////////////////////////////////////////////////////////

// The xajaxResponse class is used to created responses to be sent back to your
// webpage.  A response contains one or more command messages for updating your page.
// Currently xajax supports five kinds of command messages:
// * Assign - sets the specified attribute of an element in your page
// * Append - appends data to the end of the specified attribute of an element in your page
// * Prepend - prepends data to teh beginning of the specified attribute of an element in your page
// * Replace - searches for and replaces data in the specified attribute of an element in your page
// * Script - runs JavaScript
// * Alert - shows an alert box with the suplied message text
// elements are identified by their HTML id
class xajaxResponse
{
	var $xml;

	// Constructor
	function xajaxResponse()
	{
		$this->xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>";
		$this->xml .= "<xajax>";
	}
	
	// addAssign() adds an assign command message to your xml response
	// $sTarget is a string containing the id of an HTML element
	// $sAttribute is the part of the element you wish to modify ("innerHTML", "value", etc.)
	// $sData is the data you want to set the attribute to
	// usage: $objResponse->addAssign("contentDiv","innerHTML","Some Text");
	function addAssign($sTarget,$sAttribute,$sData)
	{
		$this->xml .= "<update action=\"assign\">";
		$this->xml .= "<target attribute=\"$sAttribute\">$sTarget</target>";
		$this->xml .= "<data><![CDATA[$sData]]></data>";
		$this->xml .= "</update>";
	}
	
	// addAppend() adds an append command message to your xml response
	// $sTarget is a string containing the id of an HTML element
	// $sAttribute is the part of the element you wish to modify ("innerHTML", "value", etc.)
	// $sData is the data you want to append to the end of the attribute
	// usage: $objResponse->addAppend("contentDiv","innerHTML","Some Text");
	function addAppend($sTarget,$sAttribute,$sData)
	{
		$this->xml .= "<update action=\"append\">";
		$this->xml .= "<target attribute=\"$sAttribute\">$sTarget</target>";
		$this->xml .= "<data><![CDATA[$sData]]></data>";
		$this->xml .= "</update>";
	}
	
	// addPrepend() adds an prepend command message to your xml response
	// $sTarget is a string containing the id of an HTML element
	// $sAttribute is the part of the element you wish to modify ("innerHTML", "value", etc.)
	// $sData is the data you want to prepend to the beginning of the attribute
	// usage: $objResponse->addPrepend("contentDiv","innerHTML","Some Text");
	function addPrepend($sTarget,$sAttribute,$sData)
	{
		$this->xml .= "<update action=\"prepend\">";
		$this->xml .= "<target attribute=\"$sAttribute\">$sTarget</target>";
		$this->xml .= "<data><![CDATA[$sData]]></data>";
		$this->xml .= "</update>";
	}
	
	// addReplace() adds an replace command message to your xml response
	// $sTarget is a string containing the id of an HTML element
	// $sAttribute is the part of the element you wish to modify ("innerHTML", "value", etc.)
	// $sSearch is a string to search for
	// $sData is a string to replace the search string when found in the attribute
	// usage: $objResponse->addReplace("contentDiv","innerHTML","text","<b>text</b>");
	function addReplace($sTarget,$sAttribute,$sSearch,$sData)
	{
		$this->xml .= "<update action=\"replace\">";
		$this->xml .= "<target attribute=\"$sAttribute\">$sTarget</target>";
		$this->xml .= "<search><![CDATA[$sSearch]]></search>";
		$this->xml .= "<data><![CDATA[$sData]]></data>";
		$this->xml .= "</update>";
	}
	
	// addClear() adds an clear command message to your xml response
	// $sTarget is a string containing the id of an HTML element
	// $sAttribute is the part of the element you wish to clear ("innerHTML", "value", etc.)
	// usage: $objResponse->addClear("contentDiv","innerHTML");
	function addClear($sTarget,$sAttribute)
	{
		$this->xml .= "<update action=\"clear\">";
		$this->xml .= "<target attribute=\"$sAttribute\">$sTarget</target>";
		$this->xml .= "</update>";
	}
	
	// addAlert() adds an alert command message to your xml response
	// $sMsg is a text to be displayed in the alert box
	// usage: $objResponse->addAlert("This is some text");
	function addAlert($sMsg)
	{
		$this->xml .= "<alert><![CDATA[$sMsg]]></alert>";
	}

	// addScript() adds a jscript command message to your xml response
	// $sJS is a string containing javascript code to be executed
	// usage: $objResponse->addAlert("var x = prompt('get some text');");
	function addScript($sJS)
	{
		$this->xml .= "<jscript><![CDATA[$sJS]]></jscript>";
	}
	
	// addRemove() adds a Remove Element command message to your xml response
	// $sTarget is a string containing the id of an HTML element to be removed
	// from your page
	// usage: $objResponse->addRemove("Div2");
	function addRemove($sTarget)
	{
		$this->xml .= "<update action=\"remove\">";
		$this->xml .= "<target>$sTarget</target>";
		$this->xml .= "</update>";
	}
	
	function addCreate($sParent, $sTag, $sId, $sType="")
	{
		$this->xml .= "<update action=\"create\">";
		$this->xml .= "<target attribute=\"$sTag\">$sParent</target>";
		$this->xml .= "<data><![CDATA[$sId]]></data>";
		if ($sType != "")
			$this->xml .= "<type><![CDATA[$sType]]></type>";
		$this->xml .= "</update>";
	}
	
	// getXML() returns the xml to be returned from your function to the xajax
	// processor on your page
	// usage: $objResponse->getXML();
	function getXML()
	{
		if (strstr($this->xml,"</xajax>") == false)
			$this->xml .= "</xajax>";
		
		return $this->xml; 
	}
}// end class xajaxResponse

// Communication Method Defines
if (!defined ('GET'))
{
	define ('GET', 0);
}
if (!defined ('POST'))
{
	define ('POST', 1);
}

// the xajax class generates the xajax javascript for your page including the 
// javascript wrappers for the PHP functions that you want to call from your page.
// It also handles processing and executing the command messages in the xml responses
// sent back to your page from your PHP functions.
class xajax
{
	var $aFunctions;			// Array of PHP functions that will be callable through javascript wrappers
	var $aFunctionRequestTypes;	// Array of RequestTypes to be used with each function (key=function name)
	var $sRequestURI;			// The URI for making requests to the xajax object
	var $bDebug;				// Show debug messages true/false
	var $sWrapperPrefix;		// The prefix to prepend to the javascript wraper function name
	var $bStatusMessages;		// Show debug messages true/false
	var $aObjArray;				// Array for parsing complex objects
	var $iPos;					// Position in $aObjArray
	
	// Contructor
	// $sRequestURI - defaults to the current page
	// $bDebug Mode - defaults to false
	// $sWrapperPrefix - defaults to "xajax_";
	// usage: $xajax = new xajax();
	function xajax($sRequestURI="",$sWrapperPrefix="xajax_",$bDebug=false)
	{
		$this->aFunctions = array();
		$this->sRequestURI = $sRequestURI;
		if ($this->sRequestURI == "")
			$this->sRequestURI = $this->detectURI();
		$this->sWrapperPrefix = $sWrapperPrefix;
		$this->bDebug = $bDebug;
	}
	
	// detectURL() returns the current URL based upon the SERVER vars
	// used internally
	function detectURI()
	{
		$aUri = array();

        if (!empty($_SERVER['REQUEST_URI']))
		{
            $aUri = parse_url($_SERVER['REQUEST_URI']);
        }
        
        if (empty($aUri['scheme']))
		{
			if (!empty($_SERVER['HTTP_SCHEME']))
			{
                $aUri['scheme'] = $_SERVER['HTTP_SCHEME'];
            }
			else
			{
                $aUri['scheme'] = (!empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) != 'off') ? 'https' : 'http';
            }

            if (!empty($_SERVER['HTTP_HOST']))
			{
                if (strpos($_SERVER['HTTP_HOST'], ':') > 0)
				{
                    list($aUri['host'], $aUri['port']) = explode(':', $_SERVER['HTTP_HOST']);
                }
				else
				{
					$aUri['host'] = $_SERVER['HTTP_HOST'];
                }
            }
			else if (!empty($_SERVER['SERVER_NAME']))
			{
            	$aUri['host'] = $_SERVER['SERVER_NAME'];
            }
			else
			{
				print "xajax Error: xajax failed to automatically identify your Request URI.";
				print "Please set the Request URI explicitly when you instantiate the xajax object.";
                exit();
            }

            if (empty($aUri['port']) && !empty($_SERVER['SERVER_PORT']))
			{
                $aUri['port'] = $_SERVER['SERVER_PORT'];
            }

            if (empty($aUri['path']))
			{
                if (!empty($_SERVER['PATH_INFO']))
				{
                    $path = parse_url($_SERVER['PATH_INFO']);
                }
				else
				{
                    $path = parse_url($_SERVER['PHP_SELF']);
                }
                $aUri['path'] = $path['path'];
                unset($path);
            }
        }

        $sUri = $aUri['scheme'] . '://';
        if (!empty($aUri['user']))
		{
            $sUri .= $aUri['user'];
            if (!empty($aUri['pass']))
			{
                $sUri .= ':' . $aUri['pass'];
            }
            $sUri .= '@';
        }
        
        $sUri .= $aUri['host'];
        
        if (!empty($aUri['port']) && (($aUri['scheme'] == 'http' && $aUri['port'] != 80) || ($aUri['scheme'] == 'https' && $aUri['port'] != 443)))
		{
            $sUri .= ':'.$aUri['port'];
        }
        // And finally path, without script name
        $sUri .= substr($aUri['path'], 0, strrpos($aUri['path'], '/') + 1);

        unset($aUri);

        return $sUri.basename($_SERVER['SCRIPT_NAME']);
	}
	
	// setRequestURI() sets the URI to which requests will be made
	// usage: $xajax->setRequestURI("http://xajax.sourceforge.net");
	function setRequestURI($sRequestURI)
	{
		$this->sRequestURI = $sRequestURI;
	}
	
	// debugOn() enables debug messages for xajax
	// usage: $xajax->debugOn();
	function debugOn()
	{
		$this->bDebug = true;
	}
	
	// debugOff() disables debug messages for xajax
	// usage: $xajax->debugOff();
	function debugOff()
	{
		$this->bDebug = false;
	}
	
	// statusMessagesOn() enables messages in the statusbar for xajax
	// usage: $xajax->statusMessagesOn();
	function statusMessagesOn()
	{
		$this->bStatusMessages = true;
	}
	
	// statusMessagesOff() disables messages in the statusbar for xajax
	// usage: $xajax->statusMessagesOff();
	function statusMessagesOff()
	{
		$this->bStatusMessages = false;
	}
	
	// setWrapperPrefix() sets the prefix that will be appended to the javascript
	// wraper functions.
	function setWrapperPrefix($sPrefix)
	{
		$this->sWrapperPrefix = $sPrefix;
	}
	
	//Dpericated.  Use registerFunction();
	function addFunction($sFunction,$sRequestType=POST)
	{
		trigger_error("xajax: the <b>addFunction()</b> method has been renamed <b>registerFunction()</b>. <br />Please use ->registerFunction('$sFunction'".($sRequestType==GET?",GET":"")."); instead.",E_USER_WARNING);
		$this->registerFunction($sFunction,$sRequestType);
	}
	
	// registerFunction() registers a PHP function to be callable through xajax
	// $sFunction is a string containing the function name
	// $sRequestType is the RequestType (GET/POST) that should be used 
	//		for this function.  Defaults to POST.
	// usage: $xajax->registerFunction("myfunction",POST);
	function registerFunction($sFunction,$sRequestType=POST)
	{
		$this->aFunctions[] = $sFunction;
		$this->aFunctionRequestTypes[$sFunction] = $sRequestType;
	}
	
	// generates the javascript wrapper for the specified PHP function
	// used internally
	function wrap($sFunction,$sRequestType=POST)
	{
		$js = "function ".$this->sWrapperPrefix."$sFunction(){xajax.call(\"$sFunction\", arguments, ".$sRequestType.");}\n";		
		return $js;
	}
	
	// processRequests() is the main communications engine of xajax
	// The engine handles all incoming xajax requests, calls the apporiate PHP functions
	// and passes the xml responses back to the javascript response handler
	// if your RequestURI is the same as your web page then this function should
	// be called before any headers or html has been sent.
	// usage: $xajax->processRequests()
	function processRequests()
	{	
		if (!empty($_GET['xajaxjs']))
		{
			header("Content-type: text/javascript");
			print $this->generateJavascript();
			exit();
			return;
		}
		
		$requestMode = -1;
		$sFunctionName = "";
		$aArgs = array();
		$sResponse = "";
		
		if (!empty($_GET["xajax"]))
			$requestMode = GET;
		
		if (!empty($_POST["xajax"]))
			$requestMode = POST;
			
		if ($requestMode == -1) 
			return;
	
		if ($requestMode == POST)
		{
			$sFunctionName = $_POST["xajax"];
			
			if (!empty($_POST["xajaxargs"])) 
				$aArgs = $_POST["xajaxargs"];
		}
		else
		{	
			header ("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
			header ("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
			header ("Cache-Control: no-cache, must-revalidate");
			header ("Pragma: no-cache");
			header("Content-type: text/xml");
			
			$sFunctionName = $_GET["xajax"];
			
			if (!empty($_GET["xajaxargs"])) 
				$aArgs = $_GET["xajaxargs"];
		}
		
		if (!in_array($sFunctionName, $this->aFunctions))
		{
			$objResponse = new xajaxResponse();
			$objResponse->addAlert("Unknown Function $sFunctionName.");
			$sResponse = $objResponse->getXML();
		}
		else if ($this->aFunctionRequestTypes[$sFunctionName] != $requestMode)
		{
			$objResponse = new xajaxResponse();
			$objResponse->addAlert("Incorrect Request Type.");
			$sResponse = $objResponse->getXML();
		}
		else
		{
			for ($i = 0; $i < sizeof($aArgs); $i++)
			{
				if (stristr($aArgs[$i],"<xjxobj>") != false)
				{
					$aArgs[$i] = $this->xmlToArray("xjxobj",$aArgs[$i]);	
				}
				else if (stristr($aArgs[$i],"<xjxquery>") != false)
				{
					$aArgs[$i] = $this->xmlToArray("xjxquery",$aArgs[$i]);	
				}
			}
			$sResponse = call_user_func_array($sFunctionName, $aArgs);
		}
		
		header("Content-type: text/xml; charset=utf-8");
		print $sResponse;
		
		exit();
	}
	
	// xmlToArray() takes a string containing xajax xjxobj xml or xjxquery xml
	// and builds an array representation of it to pass as an argument to
	// the php function being called. Returns an array.
	// used internally
	function xmlToArray($rootTag, $sXml)
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
		$aArray = $this->parseObjXml($rootTag);
		
		return $aArray;
	}
	
	// parseObjXml() is a recursive function that generates an array from the
	// contents of $this->aObjArray. Returns an array.
	// used internally
	function parseObjXml($rootTag)
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
									$value = $this->parseObjXml("xjxobj");
									$this->iPos++;
								}
								else
								{
									$value .= $this->aObjArray[$this->iPos];
								}
								$this->iPos++;
							}
						}
						$this->iPos++;
					}
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
		}
		
		return $aArray;
	}
	
	// Depricated.  Use printJavascript();
	function javascript($sJsURI="")
	{
		trigger_error("xajax: the <b>javascript()</b> method has been renamed <b>printJavascript()</b>. <br />Please use ->printJavascript(".($sJsURI==""?"":"'$sJsURI'")."); instead.",E_USER_WARNING);
		$this->printJavascript($sJsURI);
	}
	
	// printJavascript() prints the xajax javascript code into your page
	// it should only be called between the <head> </head> tags
	// usage:
	//	<head>
	//		...
	//		<?php $xajax->printJavascript(); 
	function printJavascript($sJsURI="")
	{
		print $this->getJavascript($sJsURI);
	}
	
	// getJavascript() returns the xajax javascript code that should be added to
	// your page between the <head> </head> tags
	// usage:
	//	<head>
	//		...
	//		<?php $xajax->getJavascript(); 
	function getJavascript($sJsURI="")
	{	
		if ($sJsURI == "")
			$sJsURI = $this->sRequestURI;
			
		$separator=strpos($sJsURI,'?')==false?'?':'&';
		
		$html  = "<script type=\"text/javascript\">var xajaxRequestUri=\"".$this->sRequestURI."\";</script>\n";
		$html .= "\t<script type=\"text/javascript\" src=\"".$sJsURI.$separator."xajaxjs=xajaxjs\"></script>\n";
		
		return $html;
	}
	
	// compressJavascript() compresses the javascript code for more efficient delivery
	// used internally 
	// $sJS is a string containing the javascript code to compress
	function compressJavascript($sJS)
	{
		//remove windows cariage returns
		$sJS = str_replace("\r","",$sJS);
		
		//array to store replaced literal strings
		$literal_strings = array();
		
		//explode the string into lines
		$lines = explode("\n",$sJS);
		//loop through all the lines, building a new string at the same time as removing literal strings
		$clean = "";
		$inComment = false;
		$literal = "";
		$inQuote = false;
		$escaped = false;
		$quoteChar = "";
		
		for($i=0;$i<count($lines);$i++)
		{
			$line = $lines[$i];
			$inNormalComment = false;
		
			//loop through line's characters and take out any literal strings, replace them with ___i___ where i is the index of this string
			for($j=0;$j<strlen($line);$j++)
			{
				$c = substr($line,$j,1);
				$d = substr($line,$j,2);
		
				//look for start of quote
				if(!$inQuote && !$inComment)
				{
					//is this character a quote or a comment
					if(($c=="\"" || $c=="'") && !$inComment && !$inNormalComment)
					{
						$inQuote = true;
						$inComment = false;
						$escaped = false;
						$quoteChar = $c;
						$literal = $c;
					}
					else if($d=="/*" && !$inNormalComment)
					{
						$inQuote = false;
						$inComment = true;
						$escaped = false;
						$quoteChar = $d;
						$literal = $d;	
						$j++;	
					}
					else if($d=="//") //ignore string markers that are found inside comments
					{
						$inNormalComment = true;
						$clean .= $c;
					}
					else
					{
						$clean .= $c;
					}
				}
				else //allready in a string so find end quote
				{
					if($c == $quoteChar && !$escaped && !$inComment)
					{
						$inQuote = false;
						$literal .= $c;
		
						//subsitute in a marker for the string
						$clean .= "___" . count($literal_strings) . "___";
		
						//push the string onto our array
						array_push($literal_strings,$literal);
		
					}
					else if($inComment && $d=="*/")
					{
						$inComment = false;
						$literal .= $d;
		
						//subsitute in a marker for the string
						$clean .= "___" . count($literal_strings) . "___";
		
						//push the string onto our array
						array_push($literal_strings,$literal);
		
						$j++;
					}
					else if($c == "\\" && !$escaped)
						$escaped = true;
					else
						$escaped = false;
		
					$literal .= $c;
				}
			}
			if($inComment) $literal .= "\n";
			$clean .= "\n";
		}
		//explode the clean string into lines again
		$lines = explode("\n",$clean);
		
		//now process each line at a time
		for($i=0;$i<count($lines);$i++)
		{
			$line = $lines[$i];
		
			//remove comments
			$line = preg_replace("/\/\/(.*)/","",$line);
		
			//strip leading and trailing whitespace
			$line = trim($line);
		
			//remove all whitespace with a single space
			$line = preg_replace("/\s+/"," ",$line);
		
			//remove any whitespace that occurs after/before an operator
			$line = preg_replace("/\s*([!\}\{;,&=\|\-\+\*\/\)\(:])\s*/","\\1",$line);
		
			$lines[$i] = $line;
		}
		
		//implode the lines
		$sJS = implode("\n",$lines);
		
		//make sure there is a max of 1 \n after each line
		$sJS = preg_replace("/[\n]+/","\n",$sJS);
		
		//strip out line breaks that immediately follow a semi-colon
		$sJS = preg_replace("/;\n/",";",$sJS);
		
		//curly brackets aren't on their own
		$sJS = preg_replace("/[\n]*\{[\n]*/","{",$sJS);
		
		//finally loop through and replace all the literal strings:
		for($i=0;$i<count($literal_strings);$i++)
			$sJS = str_replace("___".$i."___",$literal_strings[$i],$sJS);
		
		return $sJS;
	}

	// generateJavascript() generates all of the xajax javascript code including the javascript
	// wrappers for the PHP functions specified by the registerFunction() method and the response
	// xml parser
	// used internally
	function generateJavascript()
	{
		$js  = "";
		if ($this->bDebug){ $js .= "var xajaxDebug=".($this->bDebug?"true":"false").";\n"; }
		
		ob_start();
		?>
		function Xajax()
		{
			<?php if ($this->bDebug){ ?>this.DebugMessage = function(text){if (xajaxDebug) alert("Xajax Debug:\n " + text)}<?php	} ?>
			
			this.workId = 'xajaxWork'+ new Date().getTime();
			this.depth = 0;
			
			//Get the XMLHttpRequest Object
			this.getRequestObject = function()
			{
				<?php if ($this->bDebug){ ?>this.DebugMessage("Initializing Request Object..");<?php } ?>
				var req;
				try
				{
					req=new ActiveXObject("Msxml2.XMLHTTP");
				}
				catch (e)
				{
					try
					{
						req=new ActiveXObject("Microsoft.XMLHTTP");
					}
					catch (e2)
					{
						req=null;
					}
				}
				if(!req && typeof XMLHttpRequest != "undefined")
					req = new XMLHttpRequest();
				
					<?php if ($this->bDebug){ ?>if (!req) this.DebugMessage("Request Object Instantiation failed.");<?php } ?>
					
				return req;
			}

			// xajax.$() is shorthand for document.getElementById()
			this.$ = function(sId)
			{
				return document.getElementById(sId);
			}
			
			// xajax.getFormValues() builds a query string XML message from the elements of a form object
			this.getFormValues = function(frm)
			{
				var objForm;
				if (typeof(frm) == "string")
					objForm = this.$(frm);
				else
					objForm = frm;
				var sXml = "<xjxquery><q>";
				if (objForm && objForm.tagName == 'FORM')
				{
					var formElements = objForm.elements;
					for( var i=0; i < formElements.length; i++)
					{
						if ((formElements[i].type == 'radio' || formElements[i].type == 'checkbox') && formElements[i].checked == false)
							continue;
						var name = formElements[i].name;
						if (name)
						{
							if (sXml != '<xjxquery><q>')
								sXml += '&';
							sXml += name+"="+encodeURIComponent(formElements[i].value);
						} 
					}
				}
				
				sXml +="</q></xjxquery>";
				
				return sXml;
			}
			
			// Generates an XML message that xajax can understand from a javascript object
			this.objectToXML = function(obj)
			{
				var sXml = "<xjxobj>";
				for (i in obj)
				{
					try
					{
						if (i == 'constructor')
							continue;
						if (obj[i] && typeof(obj[i]) == 'function')
							continue;
							
						var key = i;
						var value = obj[i];
						if (value && typeof(value)=="object" && 
							(value.constructor == Array
							 ) && this.depth <= 50)
						{
							this.depth++;
							value = this.objectToXML(value);
							this.depth--;
						}
						
						sXml += "<e><k>"+key+"</k><v>"+value+"</v></e>";
						
					}
					catch(e)
					{
						<?php if ($this->bDebug){ ?>this.DebugMessage(e);<?php } ?>
					}
				}
				sXml += "</xjxobj>";
			
				return sXml;
			}

			// Sends a XMLHttpRequest to call the specified PHP function on the server
			this.call = function(sFunction, aArgs, sRequestType)
			{
				var i,r,postData;
				if (document.body)
					document.body.style.cursor = 'wait';
				<?php if ($this->bStatusMessages == true){?>window.status = 'Sending Request...';<?php } ?>
				<?php if ($this->bDebug){ ?>this.DebugMessage("Starting xajax...");<?php } ?>
				var xajaxRequestType = sRequestType;
				var uri = xajaxRequestUri;
				var value;
				switch(xajaxRequestType)
				{
					case <?php print GET; ?>:{
						var uriGet = uri.indexOf("?")==-1?"?xajax="+encodeURIComponent(sFunction):"&xajax="+encodeURIComponent(sFunction);
						for (i = 0; i<aArgs.length; i++)
						{
							value = aArgs[i];
							if (typeof(value)=="object")
								value = this.objectToXML(value);
							uriGet += "&xajaxargs[]="+encodeURIComponent(value);
						}
						uriGet += "&xajaxr=" + new Date().getTime();
						uri += uriGet;
						postData = null;
						} break;
					case <?php print POST; ?>:{
						postData = "xajax="+encodeURIComponent(sFunction);
						postData += "&xajaxr="+new Date().getTime();
						for (i = 0; i <aArgs.length; i++)
						{
							value = aArgs[i];
							if (typeof(value)=="object")
								value = this.objectToXML(value);
							postData = postData+"&xajaxargs[]="+encodeURIComponent(value);
						}
						} break;
					default:
						alert("Illegal request type: " + xajaxRequestType); return false; break;
				}
				r = this.getRequestObject();
				r.open(xajaxRequestType==<?php print GET; ?>?"GET":"POST", uri, true);
				if (xajaxRequestType == <?php print POST; ?>)
				{
					try
					{
						r.setRequestHeader("Method", "POST " + uri + " HTTP/1.1");
						r.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
					}
					catch(e)
					{
						alert("Your browser does not appear to  support asynchronous requests using POST.");
						return false;
					}
				}
				r.onreadystatechange = function()
				{
					if (r.readyState != 4)
						return;
					
					if (r.status==200)
					{
						<?php if ($this->bDebug){ ?>xajax.DebugMessage("Received:\n" + r.responseText);<?php } ?>
						var data = r.responseXML;
						if (data)
							xajax.processResponse(data);
					}
				}
				<?php if ($this->bDebug){ ?>this.DebugMessage("Calling "+sFunction +" uri="+uri+" (post:"+ postData +")");<?php } ?>
				r.send(postData);
				<?php if ($this->bStatusMessages == true){?>window.status = 'Waiting for data...';<?php } ?>
				<?php if ($this->bDebug){ ?>this.DebugMessage(sFunction + " waiting..");<?php } ?>
				delete r;
				return true;
			}
			
			// Tests if the new Data is the same as the extant data
			this.willChange = function(element, attribute, newData)
			{
				var oldData;
				if (attribute == "innerHTML")
				{
					tmpXajax = this.$(this.workId);
					if (tmpXajax == null)
					{
						tmpXajax = document.createElement("div");
						tmpXajax.setAttribute('id',this.workId);
						tmpXajax.style.display = "none";
						tmpXajax.style.visibility = "hidden";
						document.body.appendChild(tmpXajax);
					}
					tmpXajax.innerHTML = newData;
					newData = tmpXajax.innerHTML;
				}
				eval("oldData=document.getElementById('"+element+"')."+attribute);
				if (newData != oldData)
					return true;
					
				return false;
			}
			
			//Process XML xajaxResponses returned from the request
			this.processResponse = function(xml)
			{
				<?php if ($this->bStatusMessages == true){?> window.status = 'Recieving data...'; <?php } ?>
				var tmpXajax = null;
				xml = xml.documentElement;
				for (i=0; i<xml.childNodes.length; i++)
				{
					if (xml.childNodes[i].nodeName == "alert")
					{
						if (xml.childNodes[i].firstChild)
							alert(xml.childNodes[i].firstChild.nodeValue);
					}
					if (xml.childNodes[i].nodeName == "jscript")
					{
						if (xml.childNodes[i].firstChild)
							eval(xml.childNodes[i].firstChild.nodeValue);
					}
					if (xml.childNodes[i].nodeName == "update")
					{
						var action;
						var element;
						var attribute;
						var search;
						var data;
						var type;
						var objElement;
						
						for (j=0; j<xml.childNodes[i].attributes.length; j++)
						{
							if (xml.childNodes[i].attributes[j].name == "action")
							{
								action = xml.childNodes[i].attributes[j].value;
							}
						}
						
						var node = xml.childNodes[i];
						for (j=0;j<node.childNodes.length;j++)
						{
							if (node.childNodes[j].nodeName == "target")
							{
								for (k=0; k<node.childNodes[j].attributes.length; k++)
								{
									if (node.childNodes[j].attributes[k].name == "attribute")
									{
										attribute = node.childNodes[j].attributes[k].value;
									}
								}
								element = node.childNodes[j].firstChild.nodeValue;
							}
							if (node.childNodes[j].nodeName == "search")
							{
								if (node.childNodes[j].firstChild)
									search = node.childNodes[j].firstChild.nodeValue;
								else
									search = "";
							}
							if (node.childNodes[j].nodeName == "data")
							{
								if (node.childNodes[j].firstChild)
									data = node.childNodes[j].firstChild.nodeValue;
								else
									data = "";
							}
							
							if (node.childNodes[j].nodeName == "type")
							{
								if (node.childNodes[j].firstChild)
									type = node.childNodes[j].firstChild.nodeValue;
								else
									type = "";
							}
						}
						if (action=="assign")
						{
							if (this.willChange(element,attribute,data))
							{
								eval("document.getElementById('"+element+"')."+attribute+"=data;");
							}
						}
						if (action=="append")
							eval("document.getElementById('"+element+"')."+attribute+"+=data;");
						if (action=="prepend")
							eval("document.getElementById('"+element+"')."+attribute+"=data+document.getElementById('"+element+"')."+attribute);
						if (action=="replace")
						{
							eval("var v=document.getElementById('"+element+"')."+attribute);
							var v2 = v.indexOf(search)==-1?v:"";
							while (v.indexOf(search) > -1)
							{
								x = v.indexOf(search)+search.length+1;
								v2 += v.substr(0,x).replace(search,data);
								v = v.substr(x,v.length-x);
							}
							if (this.willChange(element,attribute,v2))
								eval('document.getElementById("'+element+'").'+attribute+'=v2;');
						}
						if (action=="clear")
							eval("document.getElementById('"+element+"')."+attribute+"='';");
						if (action=="remove")
						{
							objElement = this.$(element);
							if (objElement.parentNode && objElement.parentNode.removeChild)
							{
								objElement.parentNode.removeChild(objElement);
							}
						}
						if (action=="create")
						{
							var objParent = this.$(element);
							objElement = document.createElement(attribute);
							objElement.setAttribute('id',data);
							if (type && type != '')
								objElement.setAttribute('type',type);
							objParent.appendChild(objElement);
							if (objParent.tagName == "FORM")
							{

							}
						}
					}	
				}
				document.body.style.cursor = 'default';
				<?php if ($this->bStatusMessages == true){?> window.status = 'Done'; <?php } ?>
			}
		}
		
		var xajax = new Xajax();
		<?php
		$js .= ob_get_contents()."\n";
		ob_end_clean();
		foreach($this->aFunctions as $sFunction)
			$js .= $this->wrap($sFunction,$this->aFunctionRequestTypes[$sFunction]);
	
		if ($this->bDebug == false)
			$js = $this->compressJavascript($js);
		
		print $js;
	}
}// end class xajax 
?>

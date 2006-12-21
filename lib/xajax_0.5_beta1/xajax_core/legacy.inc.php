<?php
class legacyXajaxResponse extends xajaxResponse {
	function outputEntitiesOn()	{ $this->setOutputEntities(true); }
	function outputEntitiesOff()	{ $this->setOutputEntities(false); }
	function addConfirmCommands()	{ $temp=func_get_args(); return call_user_func_array(array(&$this, 'confirmCommands'), $temp); }
	function addAssign()			{ $temp=func_get_args(); return call_user_func_array(array(&$this, 'assign'), $temp); }
	function addAppend()			{ $temp=func_get_args(); return call_user_func_array(array(&$this, 'append'), $temp); }
	function addPrepend()			{ $temp=func_get_args(); return call_user_func_array(array(&$this, 'prepend'), $temp); }
	function addReplace()			{ $temp=func_get_args(); return call_user_func_array(array(&$this, 'replace'), $temp); }
	function addClear()				{ $temp=func_get_args(); return call_user_func_array(array(&$this, 'clear'), $temp); }
	function addAlert()				{ $temp=func_get_args(); return call_user_func_array(array(&$this, 'alert'), $temp); }
	function addRedirect()			{ $temp=func_get_args(); return call_user_func_array(array(&$this, 'redirect'), $temp); }
	function addScript()			{ $temp=func_get_args(); return call_user_func_array(array(&$this, 'script'), $temp); }
	function addScriptCall()		{ $temp=func_get_args(); return call_user_func_array(array(&$this, 'call'), $temp); }
	function addRemove()			{ $temp=func_get_args(); return call_user_func_array(array(&$this, 'remove'), $temp); }
	function addCreate()			{ $temp=func_get_args(); return call_user_func_array(array(&$this, 'create'), $temp); }
	function addInsert()			{ $temp=func_get_args(); return call_user_func_array(array(&$this, 'insert'), $temp); }
	function addInsertAfter()		{ $temp=func_get_args(); return call_user_func_array(array(&$this, 'insertAfter'), $temp); }
	function addCreateInput()		{ $temp=func_get_args(); return call_user_func_array(array(&$this, 'createInput'), $temp); }
	function addInsertInput()		{ $temp=func_get_args(); return call_user_func_array(array(&$this, 'insertInput'), $temp); }
	function addInsertInputAfter()	{ $temp=func_get_args(); return call_user_func_array(array(&$this, 'insertInputAfter'), $temp); }
	function addRemoveHandler()	{ $temp=func_get_args(); return call_user_func_array(array(&$this, 'removeHandler'), $temp); }
	function addIncludeScript()	{ $temp=func_get_args(); return call_user_func_array(array(&$this, 'includeScript'), $temp); }
	function addIncludeCSS()		{ $temp=func_get_args(); return call_user_func_array(array(&$this, 'includeCSS'), $temp); }
	function &getXML()				{ return $this; }
	
}

class legacyXajax extends xajax {
	function legacyXajax($sRequestURI="",$sWrapperPrefix="xajax_",$sEncoding=XAJAX_DEFAULT_CHAR_ENCODING,$bDebug=false)
	{
		parent::xajax($sRequestURI);
		$this->setWrapperPrefix($sWrapperPrefix);
		$this->setCharEncoding($sEncoding);
		$this->setFlag('debug', $bDebug);
	}
	function registerExternalFunction($mFunction,$sFunctionName)
	{
		$this->registerFunction($mFunction,$sFunctionName);
	}
	function registerCatchAllFunction($mFunction)
	{
		if (is_array($mFunction)) array_shift($mFunction);
		$this->registerEvent($mFunction, "onMissingFunction");
	}
	function registerPreFunction($mFunction)
	{
		if (is_array($mFunction)) array_shift($mFunction);
		$this->registerEvent($mFunction, "beforeProcessing");
	}
	function canProcessRequests()			{ return $this->canProcessRequest(); }
	function processRequests()				{ return $this->processRequest(); }
	function setCallableObject(&$oObject)	{ return $this->registerCallableObject($oObject); }
	function debugOn()						{ return $this->setFlag('debug',true); }
	function debugOff()						{ return $this->setFlag('debug',false); }
	function statusMessagesOn()			{ return $this->setFlag('statusMessages',true); }
	function statusMessagesOff()			{ return $this->setFlag('statusMessages',false); }
	function waitCursorOn()				{ return $this->setFlag('waitCursor',true); }
	function waitCursorOff()				{ return $this->setFlag('waitCursor',false); }
	function exitAllowedOn()				{ return $this->setFlag('exitAllowed',true); }
	function exitAllowedOff()				{ return $this->setFlag('exitAllowed',false); }
	function errorHandlerOn()				{ return $this->setFlag('errorHandler',true); }
	function errorHandlerOff()				{ return $this->setFlag('errorHandler',false); }
	function cleanBufferOn()				{ return $this->setFlag('cleanBuffer',true); }
	function cleanBufferOff()				{ return $this->setFlag('cleanBuffer',false); }
	function decodeUTF8InputOn()			{ return $this->setFlag('decodeUTF8Input',true); }
	function decodeUTF8InputOff()			{ return $this->setFlag('decodeUTF8Input',false); }
	function outputEntitiesOn()			{ return $this->setFlag('outputEntities',true); }
	function outputEntitiesOff()			{ return $this->setFlag('outputEntities',false); }
	function allowBlankResponseOn()		{ return $this->setFlag('allowBlankResponse',true); }
	function allowBlankResponseOff()		{ return $this->setFlag('allowBlankResponse',false); }
}

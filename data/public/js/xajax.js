/* xajax Javascript library :: version 0.5 (Beta 1) */
var xajax={callOptions:{method:'post'},
workId:'xajaxWork'+new Date().getTime(),
depth:0,
eventFunctions:{globalRequestDelay:null,
globalRequestComplete:null
},
delayEventTime:400,
delayTimer:null,
responseErrorsForAlert:["400","401","402","403","404","500","501","502","503"],
DebugMessage:function(text){if(text.length > 1000)text=text.substr(0,1000)+"...\n[long response]\n...";try{if(this.debugWindow==undefined||this.debugWindow.closed==true){this.debugWindow=window.open('about:blank','xajax_debug_'+this.workId,'width=800,height=600,scrollbars=yes,resizable=yes,status=yes');this.debugWindow.document.write('<html><head><title>xajax debug output</title></head><body><h2>xajax debug output</h2><div id="debugTag"></div></body></html>');}
text=text.replace(/&/g,"&amp;")
text=text.replace(/</g,"&lt;")
text=text.replace(/>/g,"&gt;")
debugTag=this.debugWindow.document.getElementById('debugTag');debugTag.innerHTML=('<b>'+(new Date()).toString()+'</b>: '+text+'<hr/>')+debugTag.innerHTML;}catch(e){alert("xajax Debug:\n "+text);}
},
isEventFunction:function(mFunction){if(mFunction&&typeof mFunction=="function")return true;return false;},
getRequestObject:function(){if(xajaxConfig.debug)this.DebugMessage("Initializing Request Object..");var req=null;if(typeof XMLHttpRequest!="undefined")
req=new XMLHttpRequest();if(!req&&typeof ActiveXObject!="undefined"){try{req=new ActiveXObject("Msxml2.XMLHTTP");}
catch(e){try{req=new ActiveXObject("Microsoft.XMLHTTP");}
catch(e2){try{req=new ActiveXObject("Msxml2.XMLHTTP.4.0");}
catch(e3){req=null;}
}
}
}
if(!req&&window.createRequest)
req=window.createRequest();if(!req)this.DebugMessage("Request Object Instantiation failed.");return req;},
$:function(sId){if(!sId){return null;}
var returnObj=document.getElementById(sId);if(!returnObj&&document.all){returnObj=document.all[sId];}
if(xajaxConfig.debug&&!returnObj){this.DebugMessage("Element with the id \""+sId+"\" not found.");}
return returnObj;},
include:function(sFileName){var objHead=document.getElementsByTagName('head');var objScript=document.createElement('script');objScript.type='text/javascript';objScript.src=sFileName;objHead[0].appendChild(objScript);},
includeOnce:function(sFileName){var loadedScripts=document.getElementsByTagName('script');for(var i=0;i < loadedScripts.length;i++){if(loadedScripts[i].src&&loadedScripts[i].src.indexOf(sFileName)==0)
return;}
return this.include(sFileName);},
addCSS:function(sFileName){var objHead=document.getElementsByTagName('head');var objCSS=document.createElement('link');objCSS.rel='stylesheet';objCSS.type='text/css';objCSS.href=sFileName;objHead[0].appendChild(objCSS);},
stripOnPrefix:function(sEventName){sEventName=sEventName.toLowerCase();if(sEventName.indexOf('on')==0){sEventName=sEventName.replace(/on/,'');}
return sEventName;},
addOnPrefix:function(sEventName){sEventName=sEventName.toLowerCase();if(sEventName.indexOf('on')!=0){sEventName='on'+sEventName;}
return sEventName;},
addHandler:function(sElementId,sEvent,sFunctionName){if(window.addEventListener){sEvent=this.stripOnPrefix(sEvent);eval("this.$('"+sElementId+"').addEventListener('"+sEvent+"',"+sFunctionName+",false);");}
else{sAltEvent=this.addOnPrefix(sEvent);eval("this.$('"+sElementId+"').attachEvent('"+sAltEvent+"',"+sFunctionName+",false);");}
},
removeHandler:function(sElementId,sEvent,sFunctionName){if(window.addEventListener){sEvent=this.stripOnPrefix(sEvent);eval("this.$('"+sElementId+"').removeEventListener('"+sEvent+"',"+sFunctionName+",false);");}
else{sAltEvent=this.addOnPrefix(sEvent);eval("this.$('"+sElementId+"').detachEvent('"+sAltEvent+"',"+sFunctionName+",false);");}
},
create:function(sParentId,sTag,sId){var objParent=this.$(sParentId);objElement=document.createElement(sTag);objElement.setAttribute('id',sId);if(objParent)
objParent.appendChild(objElement);},
insert:function(sBeforeId,sTag,sId){var objSibling=this.$(sBeforeId);objElement=document.createElement(sTag);objElement.setAttribute('id',sId);objSibling.parentNode.insertBefore(objElement,objSibling);},
insertAfter:function(sAfterId,sTag,sId){var objSibling=this.$(sAfterId);objElement=document.createElement(sTag);objElement.setAttribute('id',sId);objSibling.parentNode.insertBefore(objElement,objSibling.nextSibling);},
getInput:function(sType,sName,sId){var Obj;if(!window.addEventListener){Obj=document.createElement('<input type="'+sType+'" id="'+sId+'" name="'+sName+'">');}
else{Obj=document.createElement('input');Obj.setAttribute('type',sType);Obj.setAttribute('name',sName);Obj.setAttribute('id',sId);}
return Obj;},
createInput:function(sParentId,sType,sName,sId){var objParent=this.$(sParentId);var objElement=this.getInput(sType,sName,sId);if(objParent&&objElement)
objParent.appendChild(objElement);},
insertInput:function(sBeforeId,sType,sName,sId){var objSibling=this.$(sBeforeId);var objElement=this.getInput(sType,sName,sId);if(objElement&&objSibling&&objSibling.parentNode)
objSibling.parentNode.insertBefore(objElement,objSibling);},
insertInputAfter:function(sAfterId,sType,sName,sId){var objSibling=this.$(sAfterId);var objElement=this.getInput(sType,sName,sId);if(objElement&&objSibling&&objSibling.parentNode){objSibling.parentNode.insertBefore(objElement,objSibling.nextSibling);}
},
remove:function(sId){objElement=this.$(sId);if(objElement&&objElement.parentNode&&objElement.parentNode.removeChild){objElement.parentNode.removeChild(objElement);}
},
replace:function(sId,sAttribute,sSearch,sReplace){var bFunction=false;if(sAttribute=="innerHTML")
sSearch=this.getBrowserHTML(sSearch);eval("var txt=this.$('"+sId+"')."+sAttribute);if(typeof txt=="function"){txt=txt.toString();bFunction=true;}
if(txt.indexOf(sSearch)>-1){var newTxt='';while(txt.indexOf(sSearch)>-1){x=txt.indexOf(sSearch)+sSearch.length+1;newTxt+=txt.substr(0,x).replace(sSearch,sReplace);txt=txt.substr(x,txt.length-x);}
newTxt+=txt;if(bFunction){eval('this.$("'+sId+'").'+sAttribute+'=newTxt;');}
else if(this.willChange(sId,sAttribute,newTxt)){eval('this.$("'+sId+'").'+sAttribute+'=newTxt;');}
}
},
getFormValues:function(frm){var objForm;var submitDisabledElements=false;if(arguments.length > 1&&arguments[1]==true)
submitDisabledElements=true;var prefix="";if(arguments.length > 2)
prefix=arguments[2];if(typeof(frm)=="string")
objForm=this.$(frm);else
objForm=frm;var sXml="<xjxquery><q>";if(objForm&&objForm.tagName&&objForm.tagName.toUpperCase()=='FORM'){var formElements=objForm.elements;for(var i=0;i < formElements.length;i++){if(!formElements[i].name)
continue;if(formElements[i].name.substring(0,prefix.length)!=prefix)
continue;if(formElements[i].type&&(formElements[i].type=='radio'||formElements[i].type=='checkbox')&&formElements[i].checked==false)
continue;if(formElements[i].disabled&&formElements[i].disabled==true&&submitDisabledElements==false)
continue;var name=formElements[i].name;if(name){if(sXml!='<xjxquery><q>')
sXml+='&';if(formElements[i].type=='select-multiple'){if(name.substr(name.length-2,2)!='[]')
name+='[]';for(var j=0;j < formElements[i].length;j++){if(formElements[i].options[j].selected==true)
sXml+=name+"="+encodeURIComponent(formElements[i].options[j].value)+"&";}
}
else{sXml+=name+"="+encodeURIComponent(formElements[i].value);}
}
}
}
sXml+="</q></xjxquery>";return sXml;},
objectToXML:function(obj){var sXml="<xjxobj>";for(i in obj){try{if(i=='constructor')
continue;if(obj[i]&&typeof(obj[i])=='function')
continue;var key=i;var value=obj[i];if(value&&typeof(value)=="object"&&this.depth <=50){this.depth++;value=this.objectToXML(value);this.depth--;}
key=key.replace(/]]>/g,']]]]><![CDATA[>');value=value.replace(/]]>/g,']]]]><![CDATA[>');sXml+="<e><k><![CDATA["+key+"]]></k><v><![CDATA["+value+"]]></v></e>";}
catch(e){if(xajaxConfig.debug)this.DebugMessage(e.name+": "+e.message);}
}
sXml+="</xjxobj>";return sXml;},
_nodeToObject:function(node){if(node.nodeName=='#cdata-section'){var data=node.data;while(node=node.nextSibling){data+=node.data;}
return data;}
else if(node.nodeName=='xjxobj'){var data=new Array();for(var j=0;j<node.childNodes.length;j++){var child=node.childNodes[j];var key;var value;if(child.nodeName=='e'){for(var k=0;k<child.childNodes.length;k++){if(child.childNodes[k].nodeName=='k'){key=child.childNodes[k].firstChild.data;}
else if(child.childNodes[k].nodeName=='v'){if(child.childNodes[k].firstChild==null)
value='';else
value=this._nodeToObject(child.childNodes[k].firstChild);}
}
if(key!=null&&value!=null){data[key]=value;key=value=null;}
}
}
return data;}
},
runDelayEvents:function(){if(this.isEventFunction(this.eventFunctions.globalRequestDelay)){this.eventFunctions.globalRequestDelay();}
if(this.isEventFunction(this.callOptions.onRequestDelay)){this.callOptions.onRequestDelay();}
},
setCallOptions:function(aOptions){this.callOptions={URI:"",
parameters:null,
onRequestDelay:null,
beforeResponse:null,
onResponse:null
};for(optionKey in aOptions){this.callOptions[optionKey]=aOptions[optionKey];}
},
call:function(sFunction,aOptions){var i,r,postData;this.setCallOptions(aOptions);if(document.body&&xajaxConfig.waitCursor)
document.body.style.cursor='wait';if(xajaxConfig.statusMessages==true)window.status='Sending Request...';if(xajax.loadingFunction!=undefined){xajax.eventFunctions.globalRequestDelay=xajax.loadingFunction;}
if(xajax.doneLoadingFunction!=undefined){xajax.eventFunctions.globalRequestComplete=xajax.doneLoadingFunction;}
clearTimeout(this.delayTimer);this.delayTimer=setTimeout("xajax.runDelayEvents()",this.delayEventTime);if(xajaxConfig.debug)this.DebugMessage("Starting xajax...");if(!this.callOptions.method){var requestType="post";}
else{var requestType=this.callOptions.method;if(requestType!=("get"||"post")){requestType="post";}
}
if(this.callOptions.URI){var uri=this.callOptions.URI;}
else{var uri=xajaxConfig.requestURI;}
var value;var aArgs=this.callOptions.parameters;postData="xajax="+encodeURIComponent(sFunction);postData+="&xajaxr="+new Date().getTime();if(aArgs){for(i=0;i <aArgs.length;i++){value=aArgs[i];if(typeof(value)=="object")
value=this.objectToXML(value);postData+="&xajaxargs[]="+encodeURIComponent(value);}
}
switch(requestType){case "get":{var uriGet=uri.indexOf("?")==-1?"?":"&";uriGet+=postData;uri+=uriGet;postData=null;}break;case "post":{}break;default:
alert("Illegal request type: "+requestType);return false;break;}
r=this.getRequestObject();if(!r)return false;r.open(requestType,uri,true);if(requestType=="post"){try{r.setRequestHeader("Method","POST "+uri+" HTTP/1.1");r.setRequestHeader("Content-Type","application/x-www-form-urlencoded");}
catch(e){delete r;r=null;aOptions.method='get';return this.call(sFunction,aOptions);}
}else{r.setRequestHeader("If-Modified-Since","Sat, 1 Jan 2000 00:00:00 GMT");}
r.onreadystatechange=function(){if(r.readyState!=4)
return;xajax.readyStateChange(r);delete r;r=null;}
if(xajaxConfig.debug)this.DebugMessage("Calling "+sFunction+" uri="+uri+" (post:"+postData+")");r.send(postData);if(xajaxConfig.statusMessages==true)window.status='Waiting for data...';delete r;return true;},
readyStateChange:function(r){try{if(r.status==0||r.status==200){if(xajaxConfig.debug)xajax.DebugMessage("Received:\n"+r.responseText);if(r.responseXML&&r.responseXML.documentElement)
this.processResponse(r.responseXML);else if(r.responseText=="")
this.completeResponse();else{var errorString="Error: the XML response that was returned from the server is invalid.";errorString+="\nReceived:\n"+r.responseText;trimmedResponseText=r.responseText.replace(/^\s+/g,"");if(trimmedResponseText!=r.responseText)
errorString+="\nYou have whitespace at the beginning of your response.";trimmedResponseText=r.responseText.replace(/\s+$/g,"");if(trimmedResponseText!=r.responseText)
errorString+="\nYou have whitespace at the end of your response.";alert(errorString);this.completeResponse();if(xajaxConfig.statusMessages==true)window.status='Invalid XML response error';}
}
else{if(this.arrayContainsValue(this.responseErrorsForAlert,r.status)){var errorString="Error: the server returned the following HTTP status: "+r.status;errorString+="\nReceived:\n"+r.responseText;alert(errorString);}
this.completeResponse();if(this.statusMessages==true)window.status='Invalid XML response error';}
}catch(e){}
},
processResponse:function(xml){clearTimeout(this.delayTimer);if(this.isEventFunction(this.eventFunctions.globalRequestComplete)){this.eventFunctions.globalRequestComplete();}
if(this.isEventFunction(this.callOptions.beforeResponse)){var eventReturn=this.callOptions.beforeResponse(xml);if(eventReturn==false){this.completeResponse();return;}
}
if(xajaxConfig.statusMessages==true)window.status='Processing...';var tmpXajax=null;xml=xml.documentElement;if(xml==null){this.completeResponse();return;}
var skipCommands=0;for(var i=0;i<xml.childNodes.length;i++){if(skipCommands > 0){skipCommands--;continue;}
if(xml.childNodes[i].nodeName=="cmd"){var cmd;var id;var property;var data;var search;var type;var before;var objElement=null;for(var j=0;j<xml.childNodes[i].attributes.length;j++){if(xml.childNodes[i].attributes[j].name=="n"){cmd=xml.childNodes[i].attributes[j].value;}
else if(xml.childNodes[i].attributes[j].name=="t"){id=xml.childNodes[i].attributes[j].value;}
else if(xml.childNodes[i].attributes[j].name=="p"){property=xml.childNodes[i].attributes[j].value;}
else if(xml.childNodes[i].attributes[j].name=="c"){type=xml.childNodes[i].attributes[j].value;}
}
if(xml.childNodes[i].childNodes.length > 1&&xml.childNodes[i].firstChild.nodeName=="#cdata-section"){data="";for(var j=0;j<xml.childNodes[i].childNodes.length;j++){data+=xml.childNodes[i].childNodes[j].data;}
}
else if(xml.childNodes[i].firstChild&&xml.childNodes[i].firstChild.nodeName=='xjxobj'){data=this._nodeToObject(xml.childNodes[i].firstChild);}
else if(xml.childNodes[i].childNodes.length > 1){for(var j=0;j<xml.childNodes[i].childNodes.length;j++){if(xml.childNodes[i].childNodes[j].childNodes.length > 1&&xml.childNodes[i].childNodes[j].firstChild.nodeName=="#cdata-section"){var internalData="";for(var k=0;k<xml.childNodes[i].childNodes[j].childNodes.length;k++){internalData+=xml.childNodes[i].childNodes[j].childNodes[k].nodeValue;}
}else{var internalData=xml.childNodes[i].childNodes[j].firstChild.nodeValue;}
if(xml.childNodes[i].childNodes[j].nodeName=="s"){search=internalData;}
if(xml.childNodes[i].childNodes[j].nodeName=="r"){data=internalData;}
}
}
else if(xml.childNodes[i].firstChild)
data=xml.childNodes[i].firstChild.nodeValue;else
data="";if(cmd!="jc")objElement=this.$(id);var cmdFullname;try{if(cmd=="cc"){cmdFullname="confirmCommands";var confirmResult=confirm(data);if(!confirmResult){skipCommands=id;}
}
if(cmd=="al"){cmdFullname="alert";alert(data);}
else if(cmd=="jc"){cmdFullname="call";var scr=id+'(';if(data[0]!=null){scr+='data[0]';for(var l=1;l<data.length;l++){scr+=',data['+l+']';}
}
scr+=');';eval(scr);}
else if(cmd=="js"){cmdFullname="script/redirect";eval(data);}
else if(cmd=="in"){cmdFullname="includeScript";this.include(data);}
else if(cmd=="ino"){cmdFullname="includeScriptOnce";this.includeOnce(data);}
else if(cmd=="css"){cmdFullname="includeCSS";this.addCSS(data);}
else if(cmd=="as"){cmdFullname="assign/clear";if(this.willChange(id,property,data)){eval("objElement."+property+"=data;");}
}
else if(cmd=="ap"){cmdFullname="append";eval("objElement."+property+"+=data;");}
else if(cmd=="pp"){cmdFullname="prepend";eval("objElement."+property+"=data+objElement."+property);}
else if(cmd=="rp"){cmdFullname="replace";this.replace(id,property,search,data)
}
else if(cmd=="rm"){cmdFullname="remove";this.remove(id);}
else if(cmd=="ce"){cmdFullname="create";this.create(id,data,property);}
else if(cmd=="ie"){cmdFullname="insert";this.insert(id,data,property);}
else if(cmd=="ia"){cmdFullname="insertAfter";this.insertAfter(id,data,property);}
else if(cmd=="ci"){cmdFullname="createInput";this.createInput(id,type,data,property);}
else if(cmd=="ii"){cmdFullname="insertInput";this.insertInput(id,type,data,property);}
else if(cmd=="iia"){cmdFullname="insertInputAfter";this.insertInputAfter(id,type,data,property);}
else if(cmd=="ev"){cmdFullname="addEvent";property=this.addOnPrefix(property);eval("this.$('"+id+"')."+property+"= function(){"+data+";}");}
else if(cmd=="ah"){cmdFullname="addHandler";this.addHandler(id,property,data);}
else if(cmd=="rh"){cmdFullname="removeHandler";this.removeHandler(id,property,data);}
}
catch(e){if(xajaxConfig.debug)
alert("While trying to '"+cmdFullname+"' (command number "+i+"), the following error occured:\n"
+e.name+": "+e.message+"\n"
+(id&&!objElement?"Object with id='"+id+"' wasn't found.\n":""));}
delete objElement;delete cmd;delete cmdFullname;delete id;delete property;delete search;delete data;delete type;delete before;delete internalData;delete j;delete k;delete l;}
}
delete xml;delete i;delete skipCommands;this.completeResponse();if(this.isEventFunction(this.callOptions.onResponse)){this.callOptions.onResponse();}
},
completeResponse:function(){document.body.style.cursor='default';if(xajaxConfig.statusMessages==true)window.status='Done';},
getBrowserHTML:function(html){tmpXajax=document.getElementById(this.workId);if(!tmpXajax){tmpXajax=document.createElement("div");tmpXajax.setAttribute('id',this.workId);tmpXajax.style.display="none";tmpXajax.style.visibility="hidden";document.body.appendChild(tmpXajax);}
tmpXajax.innerHTML=html;var browserHTML=tmpXajax.innerHTML;tmpXajax.innerHTML='';return browserHTML;},
willChange:function(element,attribute,newData){if(!document.body){return true;}
if(attribute=="innerHTML"){newData=this.getBrowserHTML(newData);}
elementObject=this.$(element);if(elementObject){var oldData;eval("oldData=this.$('"+element+"')."+attribute);if(newData!==oldData)
return true;}
return false;},
viewSource:function(){return "<html>"+document.getElementsByTagName("HTML")[0].innerHTML+"</html>";},
arrayContainsValue:function(array,valueToCheck){for(i=0;i<array.length;i++){if(array[i]==valueToCheck)return true;}
return false;}
}
if(xajaxConfig.legacy){xajax.advancedCall=xajax.call;xajax.call=function(sFunction,aArgs,sRequestType){var options={};if(aArgs!=null){options['parameters']=aArgs;}
if(sRequestType!=null){options['method']=sRequestType;}
xajax.advancedCall(sFunction,options);}
}
xajaxLoaded=true;
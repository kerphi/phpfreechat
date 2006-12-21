// from http://www.bobbyvandersluis.com/articles/dynamicCSS.php
function createStyleRule(selector, declaration) {
    if (!document.getElementsByTagName ||
      !(document.createElement || document.createElementNS)) return;
    var agt = navigator.userAgent.toLowerCase();
    var is_ie = ((agt.indexOf("msie") != -1) &&  (agt.indexOf("opera") == -1));
    var is_iewin = (is_ie &&  (agt.indexOf("win") != -1));
    var is_iemac = (is_ie &&  (agt.indexOf("mac") != -1));
    if (is_iemac) return; // script doesn't work properly in IE/Mac
    var head = document.getElementsByTagName("head")[0]; 
    var style = (typeof document.createElementNS != "undefined") ?
      document.createElementNS("http://www.w3.org/1999/xhtml", "style") :
      document.createElement("style");
    if (!is_iewin) {
        var styleRule = document.createTextNode(selector + " {" + declaration + "}");
	    style.appendChild(styleRule); // bugs in IE/Win
    }
	style.setAttribute("type", "text/css");
    style.setAttribute("media", "screen"); 
    head.appendChild(style);
    if (is_iewin &&  document.styleSheets &&  document.styleSheets.length > 0) {
        var lastStyle = document.styleSheets[document.styleSheets.length - 1];
        if (typeof lastStyle.addRule == "object") {
            lastStyle.addRule(selector, declaration);
        }
    }
}
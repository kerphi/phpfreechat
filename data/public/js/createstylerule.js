// from http://www.bobbyvandersluis.com/articles/dynamicCSS.php
var pfcCSS = Class.create();
pfcCSS.prototype = { 
  initialize: function()
  {
    if (!document.getElementsByTagName ||
      !(document.createElement || document.createElementNS)) return;
    var agt = navigator.userAgent.toLowerCase();
    this.is_ie = ((agt.indexOf("msie") != -1) &&  (agt.indexOf("opera") == -1));
    this.is_iewin = (is_ie &&  (agt.indexOf("win") != -1));
    this.is_iemac = (is_ie &&  (agt.indexOf("mac") != -1));
    if (this.is_iemac) return; // script doesn't work properly in IE/Mac

    var head = document.getElementsByTagName("head")[0]; 
    this.style = (typeof document.createElementNS != "undefined") ?
      document.createElementNS("http://www.w3.org/1999/xhtml", "style") :
      document.createElement("style");
    this.style.setAttribute("type", "text/css");
    this.style.setAttribute("media", "screen"); 
    head.appendChild(this.style);

    this.lastStyle = document.styleSheets[document.styleSheets.length - 1];
  },

  applyRule: function(selector, declaration)
  {
    selector = selector.split(',');
    for ( var i = 0; i < selector.length; i++)
    {
      if (!this.is_iewin) {
        var styleRule = document.createTextNode(selector[i] + " {" + declaration + "}");
        this.style.appendChild(styleRule); // bugs in IE/Win
      }
      if (this.is_iewin &&  document.styleSheets &&  document.styleSheets.length > 0) {
        this.lastStyle.addRule(selector[i], declaration);
      }
    }
  }
}
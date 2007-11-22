/**
 * This class centralize the pfc resources (translated messages, images, themes ...)
 * (depends on prototype library)
 * @author Stephane Gully
 */
var pfcResource = Class.create();
pfcResource.prototype = {
  
  initialize: function()
  {
    this.labels  = $H();
    this.fileurl = $H();
    this.smileys = $H();
    this.smileysreverse = $H();
    this.smileyskeys = new Array();
  },

  setLabel: function(key, value)
  {
    this.labels.set(key,value);
  },

  getLabel: function()
  {
    var key = this.getLabel.arguments[0];
    if (this.labels.get(key))
    {
      this.getLabel.arguments[0] = this.labels.get(key);
      return String.sprintf2(this.getLabel.arguments);
    }
    else
      return '_'+key+'_';
  },

  setFileUrl: function(key, value)
  {
    this.fileurl.set(key,value);
  },
  
  getFileUrl: function(key)
  {
    if (this.fileurl.get(key))
      return this.fileurl.get(key);
    else
      return "";
  },

  setSmiley: function(key, value)
  {
    this.smileys.set(key, value);
    this.smileysreverse.set(value,key);
    this.smileyskeys.push(key);
  },
  getSmiley: function(key)
  {
    if (this.smileys.get(key))
      return this.smileys.get(key);
    else
      return "";
  },
  getSmileyHash: function()
  {
    return this.smileys;
  },
  getSmileyReverseHash: function()
  {
    return this.smileysreverse;
  },
  getSmileyKeys: function()
  {
    return this.smileyskeys;
  },
  sortSmileyKeys: function()
  {
    // Sort keys by longest to shortest. This prevents a smiley like :) from being used on >:)
    return this.smileyskeys.sort(
        function (a,b)
        {
          var x = a.unescapeHTML();
          var y = b.unescapeHTML();

          // Replace &quot; with " for IE and Webkit browsers.
          // The prototype.js version 1.5.1.1 unescapeHTML() function does not do this.
          if (is_ie || is_webkit)
          {
            x = x.replace(/&quot;/g,'"');
            y = y.replace(/&quot;/g,'"');
          }    
    
          return (y.length - x.length);
        }
    );
  }
};



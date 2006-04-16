/**
 * This class centralize the pfc' translated messages
 * (depends on prototype library)
 * @author Stephane Gully
 */
var pfcI18N = Class.create();
pfcI18N.prototype = {
  
  initialize: function()
  {
    this.labels = $H();
    this.elts   = $H();
  },

  setLabel: function(key, value)
  {
    this.labels[key] = value;
  },
  
  _: function(key, param)
  {   
    if (this.labels[key])
      return this.labels[key];
    else
      return '_'+key+'_';
  },

  /**
   * Register element can be used to change dynamicaly the client language
   */
  registerElement: function(key, element, attribute)
  {
    // store the assigned element
    element = $(element);
    if (!this.elts[key])
      this.elts[key] = [ [element, attribute] ];
    else
      this.elts[key].push( [element, attribute] );
  }
};

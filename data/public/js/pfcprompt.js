// DHTML prompt() function replacement inspirated by :
// http://www.hunlock.com/blogs/Working_around_IE7s_prompt_bug,_er_feature

var pfcPrompt = Class.create();
pfcPrompt.prototype = { 
  initialize: function(container)
  {
    if (container == undefined || (is_ie && !is_ie7))
      container = document.getElementsByTagName('body')[0];
    this.container    = container;
    this.box          = $('pfc_promptbox');
    this.bgbox        = $('pfc_promptbgbox');
    this.prompt_field = $('pfc_promptbox_field');
    this.prompt_title = $('pfc_promptbox_title');

    this.buildBox();
    this.buildBgBox();
  },

  buildBox: function()
  {
    if (!this.box)
    {
      this.box = document.createElement('div');
      this.box.id = 'pfc_promptbox';
      this.box.style.position = 'absolute';
      this.box.style.zIndex   = 100;
      this.box.style.display  = 'none';

      if (is_gecko) {
        this.box.style.overflow = 'auto';
      }

      var div = document.createElement('h2');
      div.appendChild(document.createTextNode(pfc.res.getLabel('Input Required')));
      this.box.appendChild(div);

      this.prompt_title = document.createElement('p');
      this.prompt_title.id = 'pfc_promptbox_title';
      this.box.appendChild(this.prompt_title);

      var form = document.createElement('form');
      form.pfc_prompt = this;
      form.onsubmit = function(evt) { return this.pfc_prompt._doSubmit(); };
      this.box.appendChild(form);

      this.prompt_field = document.createElement('input');
      this.prompt_field.id = 'pfc_promptbox_field';
      this.prompt_field.type  = 'text';
      this.prompt_field.value = '';
      form.appendChild(this.prompt_field);

      var br = document.createElement('br');
      form.appendChild(br);

      var cancel = document.createElement('input');
      cancel.id = 'pfc_promptbox_cancel';
      cancel.type = 'button';
      cancel.value = pfc.res.getLabel('Cancel');
      cancel.pfc_prompt = this;
      cancel.onclick = function(evt) { return this.pfc_prompt._doSubmit(true); };
      form.appendChild(cancel);

      var submit = document.createElement('input');
      submit.id = 'pfc_promptbox_submit';
      submit.type = 'submit';
      submit.value = pfc.res.getLabel('OK');
      form.appendChild(submit);

      var ct = document.getElementsByTagName('body')[0];
      ct.appendChild(this.box);
    }
  },

  buildBgBox: function()
  {
    if (!this.bgbox)
    {
      this.bgbox = document.createElement('div');
      this.bgbox.id = 'pfc_promptbgbox';
      // assign the styles to the blackout division.
      this.bgbox.style.opacity = '.7';
      this.bgbox.style.position = 'absolute';
      this.bgbox.style.backgroundColor = '#555';
      this.bgbox.style.filter = 'alpha(opacity=70)';
      this.bgbox.style.display = 'none';
      this.bgbox.style.zIndex = 50;

      var ct = document.getElementsByTagName('body')[0];
      ct.appendChild(this.bgbox);
    }
  },

  prompt: function(text,def)
  {
    // if def wasn't actually passed, initialize it to null
    if (def==undefined) { def=''; }

    // Stretch the blackout division to fill the entire document
    // and make it visible.  Because it has a high z-index it should
    // make all other elements on the page unclickable.
    var pos = this._findPos(this.container);
    this.bgbox.style.top     = pos[1]+'px';
    this.bgbox.style.left    = pos[0]+'px';
    /* Some older IE browsers (e.g., IE 5.5) need scrollHeight/scrollWidth.
       See: http://www.quirksmode.org/viewport/compatibility.html */
    if (this.container.scrollHeight > this.container.offsetHeight
          || this.container.scrollWidth > this.container.offsetWidth)
    {
      this.bgbox.style.height  = this.container.scrollHeight+'px';
      this.bgbox.style.width   = this.container.scrollWidth+'px';
    }
    else
    {
      this.bgbox.style.height  = this.container.offsetHeight+'px';
      this.bgbox.style.width   = this.container.offsetWidth+'px';
    }
    this.bgbox.style.display = 'block';

    // Position the dialog box on the screen and make it visible.
    this.box.style.display  = 'block';
    this.box.style.top      = parseInt(pos[1]+(this.bgbox.offsetHeight-this.box.offsetHeight)/2)+'px';
    this.box.style.left     = parseInt(pos[0]+(this.bgbox.offsetWidth-this.box.offsetWidth)/2)+'px';
    this.prompt_field.value = def;
    this.prompt_field.focus(); // Give the dialog box's input field the focus.
    this.prompt_title.innerHTML = text;
  },

  _doSubmit: function(canceled)
  {
    // _doSubmit is called when the user enters or cancels the box.
    var val = this.prompt_field.value;
    if (is_gecko) this.box.focus(); // test is_gecko because it doesn't work on KHTML browser, the popup shows infinitly
    this.box.style.display   = 'none'; // clear out the dialog box
    this.bgbox.style.display = 'none'; // clear out the screen
    this.prompt_field.value  = ''; // clear out the text field
    // if the cancel button was pushed, force value to null.
    if (canceled) { val = '' }
    // call the user's function
    this.callback(val,this);
    return false;
  },

  _findPos: function(obj)
  {
    var curleft = curtop = 0;
    if (obj.offsetParent) {
      curleft = obj.offsetLeft;
      curtop = obj.offsetTop;
      while (obj = obj.offsetParent) {
        curleft += obj.offsetLeft;
        curtop += obj.offsetTop;
      }
    }
    return [curleft,curtop];
  },

  focus: function()
  {
    this.prompt_field.focus();
  },

  callback: function(v,pfcp)
  {
  }


}


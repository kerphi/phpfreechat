;(function ($, window, undefined) {

  var pluginName = 'phpfreechat',
      document = window.document,
      defaults = {
        loaded: function (pfc) {}
      };

  function Plugin(element, options) {
    this.element = element;
    this.options = $.extend({}, defaults, options) ;
    this._defaults = defaults;
    this._name = pluginName;

    /**
     * Appends a username in the user list 
     * returns the id of the user's dom element
     */
    this.appendUser = function(user) {

      // user.role = admin or user
      // user.name = nickname
      // user.email = user email used to calculate gravatar
      // user.active = true if active
      
      // default values
      user.id     = (user.id != undefined) ? user.id : 0;
      user.role   = (user.role != undefined) ? user.role : 'user';
      user.name   = (user.name != undefined) ? user.name : 'Guest '+Math.round(Math.random()*100);
      user.email  = (user.email != undefined) ? user.email : '';
      user.active = (user.active != undefined) ? user.active : true;
      
      // user list DOM element
      var users_ul = $(this.element).find(user.role == 'admin' ? 'div.pfc-role-admin ul' :
                                                                 'div.pfc-role-user ul');

      // create a blank DOM element for the user
      var html = $('              <li class="user">'
                  +'                <div class="status"></div>'
                  +'                <div class="name"></div>'
                  +'                <div class="avatar"></div>'
                  +'              </li>');

      // fill the DOM element
      if (user.name) {
        html.find('div.name').text(user.name);
      }
      if (users_ul.find('li').length == 0) {
        html.addClass('first');
      }
      html.find('div.status').addClass(user.active ? 'st-active' : 'st-inactive'); 
      html.find('div.avatar').append('<img src="http://www.gravatar.com/avatar/' + this.md5(user.email) + '?d=wavatar&amp;s=20" alt="" />');

      // get all userids from the list (could be cached)
      var userids = [];
      $(this.element).find('div.pfc-users li.user').each(function (i, dom_user) {
        userids.push(parseInt($(dom_user).attr('id').split('_')[1]));
      });
      // if no user id is indicated, generate a new one
      if (user.id == 0) {
        do {
          user.id = Math.round(Math.random()*10000);
        } while (userids.indexOf(user.id) != -1);
      }
      // add the id in the user's dom element
      if (user.id != 0 && userids.indexOf(user.id) == -1) {
        html.attr('id', 'user_'+user.id);
      } else {
        delete html;
        return 0;
      }

      // append the HTML element to the interface
      users_ul.append(html);

      return user.id;
    }
    
    /**
     * Remove a user from the user list
     * return true if user has been found, else returns false
     */
    this.removeUser = function(userid) {
      return ($(this.element).find('#user_'+userid).remove().length > 0);
    }

    /**
     * Check if the backlink is in the page
     */
    this.hasBacklink = function() {
      var backlink = $('a[href="http://www.phpfreechat.net"]').length;
      if (!backlink) {
        $(this.element).html(
          '<div class="pfc-backlink">'
          +'<p>Please insert the phpfreechat backlink somewhere in your HTML in order to load the chat. The attended backlink is:</p>'
          +'<pre>'
          +$('<div/>').text('<a href="http://www.phpfreechat.net">phpFreeChat: simple Web chat</a>').html()
          +'</pre>'
          +'</div>'
        );
        return false;
      }
      return true;
    }
    
    this.md5 = function(s){function L(k,d){return(k<<d)|(k>>>(32-d))}function K(G,k){var I,d,F,H,x;F=(G&2147483648);H=(k&2147483648);I=(G&1073741824);d=(k&1073741824);x=(G&1073741823)+(k&1073741823);if(I&d){return(x^2147483648^F^H)}if(I|d){if(x&1073741824){return(x^3221225472^F^H)}else{return(x^1073741824^F^H)}}else{return(x^F^H)}}function r(d,F,k){return(d&F)|((~d)&k)}function q(d,F,k){return(d&k)|(F&(~k))}function p(d,F,k){return(d^F^k)}function n(d,F,k){return(F^(d|(~k)))}function u(G,F,aa,Z,k,H,I){G=K(G,K(K(r(F,aa,Z),k),I));return K(L(G,H),F)}function f(G,F,aa,Z,k,H,I){G=K(G,K(K(q(F,aa,Z),k),I));return K(L(G,H),F)}function D(G,F,aa,Z,k,H,I){G=K(G,K(K(p(F,aa,Z),k),I));return K(L(G,H),F)}function t(G,F,aa,Z,k,H,I){G=K(G,K(K(n(F,aa,Z),k),I));return K(L(G,H),F)}function e(G){var Z;var F=G.length;var x=F+8;var k=(x-(x%64))/64;var I=(k+1)*16;var aa=Array(I-1);var d=0;var H=0;while(H<F){Z=(H-(H%4))/4;d=(H%4)*8;aa[Z]=(aa[Z]|(G.charCodeAt(H)<<d));H++}Z=(H-(H%4))/4;d=(H%4)*8;aa[Z]=aa[Z]|(128<<d);aa[I-2]=F<<3;aa[I-1]=F>>>29;return aa}function B(x){var k="",F="",G,d;for(d=0;d<=3;d++){G=(x>>>(d*8))&255;F="0"+G.toString(16);k=k+F.substr(F.length-2,2)}return k}function J(k){k=k.replace(/rn/g,"n");var d="";for(var F=0;F<k.length;F++){var x=k.charCodeAt(F);if(x<128){d+=String.fromCharCode(x)}else{if((x>127)&&(x<2048)){d+=String.fromCharCode((x>>6)|192);d+=String.fromCharCode((x&63)|128)}else{d+=String.fromCharCode((x>>12)|224);d+=String.fromCharCode(((x>>6)&63)|128);d+=String.fromCharCode((x&63)|128)}}}return d}var C=Array();var P,h,E,v,g,Y,X,W,V;var S=7,Q=12,N=17,M=22;var A=5,z=9,y=14,w=20;var o=4,m=11,l=16,j=23;var U=6,T=10,R=15,O=21;s=J(s);C=e(s);Y=1732584193;X=4023233417;W=2562383102;V=271733878;for(P=0;P<C.length;P+=16){h=Y;E=X;v=W;g=V;Y=u(Y,X,W,V,C[P+0],S,3614090360);V=u(V,Y,X,W,C[P+1],Q,3905402710);W=u(W,V,Y,X,C[P+2],N,606105819);X=u(X,W,V,Y,C[P+3],M,3250441966);Y=u(Y,X,W,V,C[P+4],S,4118548399);V=u(V,Y,X,W,C[P+5],Q,1200080426);W=u(W,V,Y,X,C[P+6],N,2821735955);X=u(X,W,V,Y,C[P+7],M,4249261313);Y=u(Y,X,W,V,C[P+8],S,1770035416);V=u(V,Y,X,W,C[P+9],Q,2336552879);W=u(W,V,Y,X,C[P+10],N,4294925233);X=u(X,W,V,Y,C[P+11],M,2304563134);Y=u(Y,X,W,V,C[P+12],S,1804603682);V=u(V,Y,X,W,C[P+13],Q,4254626195);W=u(W,V,Y,X,C[P+14],N,2792965006);X=u(X,W,V,Y,C[P+15],M,1236535329);Y=f(Y,X,W,V,C[P+1],A,4129170786);V=f(V,Y,X,W,C[P+6],z,3225465664);W=f(W,V,Y,X,C[P+11],y,643717713);X=f(X,W,V,Y,C[P+0],w,3921069994);Y=f(Y,X,W,V,C[P+5],A,3593408605);V=f(V,Y,X,W,C[P+10],z,38016083);W=f(W,V,Y,X,C[P+15],y,3634488961);X=f(X,W,V,Y,C[P+4],w,3889429448);Y=f(Y,X,W,V,C[P+9],A,568446438);V=f(V,Y,X,W,C[P+14],z,3275163606);W=f(W,V,Y,X,C[P+3],y,4107603335);X=f(X,W,V,Y,C[P+8],w,1163531501);Y=f(Y,X,W,V,C[P+13],A,2850285829);V=f(V,Y,X,W,C[P+2],z,4243563512);W=f(W,V,Y,X,C[P+7],y,1735328473);X=f(X,W,V,Y,C[P+12],w,2368359562);Y=D(Y,X,W,V,C[P+5],o,4294588738);V=D(V,Y,X,W,C[P+8],m,2272392833);W=D(W,V,Y,X,C[P+11],l,1839030562);X=D(X,W,V,Y,C[P+14],j,4259657740);Y=D(Y,X,W,V,C[P+1],o,2763975236);V=D(V,Y,X,W,C[P+4],m,1272893353);W=D(W,V,Y,X,C[P+7],l,4139469664);X=D(X,W,V,Y,C[P+10],j,3200236656);Y=D(Y,X,W,V,C[P+13],o,681279174);V=D(V,Y,X,W,C[P+0],m,3936430074);W=D(W,V,Y,X,C[P+3],l,3572445317);X=D(X,W,V,Y,C[P+6],j,76029189);Y=D(Y,X,W,V,C[P+9],o,3654602809);V=D(V,Y,X,W,C[P+12],m,3873151461);W=D(W,V,Y,X,C[P+15],l,530742520);X=D(X,W,V,Y,C[P+2],j,3299628645);Y=t(Y,X,W,V,C[P+0],U,4096336452);V=t(V,Y,X,W,C[P+7],T,1126891415);W=t(W,V,Y,X,C[P+14],R,2878612391);X=t(X,W,V,Y,C[P+5],O,4237533241);Y=t(Y,X,W,V,C[P+12],U,1700485571);V=t(V,Y,X,W,C[P+3],T,2399980690);W=t(W,V,Y,X,C[P+10],R,4293915773);X=t(X,W,V,Y,C[P+1],O,2240044497);Y=t(Y,X,W,V,C[P+8],U,1873313359);V=t(V,Y,X,W,C[P+15],T,4264355552);W=t(W,V,Y,X,C[P+6],R,2734768916);X=t(X,W,V,Y,C[P+13],O,1309151649);Y=t(Y,X,W,V,C[P+4],U,4149444226);V=t(V,Y,X,W,C[P+11],T,3174756917);W=t(W,V,Y,X,C[P+2],R,718787259);X=t(X,W,V,Y,C[P+9],O,3951481745);Y=K(Y,h);X=K(X,E);W=K(W,v);V=K(V,g)}var i=B(Y)+B(X)+B(W)+B(V);return i.toLowerCase()};

    
    this.init();
  }


  Plugin.prototype.init = function () {

    // are available here:
    // this.element 
    // this.options
    
    // check backlink presence
    if (!this.hasBacklink()) {
      return;
    }
    
    // load chat HTML
    $(this.element).html(
       '      <div class="pfc-content">'
      +'        <div class="pfc-tabs">'
      +'          <ul>'
      +'            <li class="channel active">'
      +'              <div class="icon"></div>'
      +'              <div class="name">Channel 1</div>'
      +'              <div class="close"></div>'
      +'            </li>'
      +'            <li class="channel">'
      +'              <div class="icon"></div>'
      +'              <div class="name">Channel 2</div>'
      +'              <div class="close"></div>'
      +'            </li>'
      +'            <li class="pm">'
      +'              <div class="icon"></div>'
      +'              <div class="name">admin</div>'
      +'              <div class="close"></div>'
      +'            </li>'
      +'            <li class="new-tab">'
      +'              <div class="icon"></div>'
      +'            </li>'
      +'          </ul>'
      +'        </div>'
      +''
      +'        <div class="pfc-topic">'
      +'          <p><span class="pfc-topic-label">Topic:</span> <span class="pfc-topic-value">no topic for this channel</span></p>'
      +'        </div>'
      +''
      +'        <div class="pfc-messages">'
      +'          <div class="messages-group" data-stamp="1336815502" data-from="kerphi">'
      +'            <div class="avatar"><img src="http://www.gravatar.com/avatar/ae5979732c49cae7b741294a1d3a8682?d=wavatar&s=30" alt="" /></div>'
      +'            <div class="date">11:38:21</div>'
      +'            <div class="name">kerphi</div>'
      +'            <div class="message">123</div>'
      +'            <div class="message">456</div>'
      +'          </div>'
      +'          <div class="messages-group" data-stamp="1336815503" data-from="admin">'
      +'            <div class="avatar"><img src="http://www.gravatar.com/avatar/00000000000000000000000000000001?d=wavatar&s=30" alt="" /></div>'
      +'            <div class="date">11:38:22</div>'
      +'            <div class="name">admin</div>'
      +'            <div class="message">Hello</div>'
      +'            <div class="message">World</div>'
      +'            <div class="message">!</div>'
      +'          </div>'
      +'        </div>'
      +''
      +'        <div class="pfc-users">'
      +'          <div class="pfc-role-admin">'
      +'            <p class="role-title">Administrators</p>'
      +'            <ul>'
      +'              <li class="first">'
      +'                <div class="status st-active"></div>'
      +'                <div class="name">admin</div>'
      +'                <div class="avatar"><img src="http://www.gravatar.com/avatar/00000000000000000000000000000001?d=wavatar&s=20" alt="" /></div>'
      +'              </li>'
      +'            </ul>'
      +'          </div>'
      +'          <div class="pfc-role-user">'
      +'            <p class="role-title">Users</p>'
      +'            <ul>'
      +'              <li class="first">'
      +'                <div class="status st-active"></div>'
      +'                <div class="name myself">kerphi</div>'
      +'                <div class="avatar"><img src="http://www.gravatar.com/avatar/ae5979732c49cae7b741294a1d3a8682?d=wavatar&s=20" alt="" /></div>'
      +'              </li>'
      +'              <li>'
      +'                <div class="status st-inactive"></div>'
      +'                <div class="name">Stéphane Gully</div>'
      +'                <div class="avatar"><img src="http://www.gravatar.com/avatar/00000000000000000000000000000002?d=wavatar&s=20" alt="" /></div>'
      +'              </li>'
      +'            </ul>'
      +'          </div>'
      +'        </div>'
      +''
      +'        <div class="pfc-footer">'
      +'          <p class="logo"><a href="http://www.phpfreechat.net">Powered by phpFreeChat</a></p>'
      +'          <p class="ping">150ms</p>'
      +'          <ul>'
      //+'            <li><div class="logout-btn"></div></li>'
      +'            <li><div class="smiley-btn" title="Not implemented"></div></li>'
      +'            <li><div class="sound-btn" title="Not implemented"></div></li>'
      //+'            <li><div class="online-btn"></div></li>'
      +'          </ul>'
      +'        </div>'
      +''
      +'        <div class="pfc-compose">'
      +'          <textarea data-to="channel1"></textarea>'
      +'        </div>'
      +'      </div>'
    );

/*    $('.pfc-tabs ul').append(
       '            <li class="channel">'
      +'              <div class="icon"></div>'
      +'              <div class="name">Channel 2</div>'
      +'              <div class="close"></div>'
      +'            </li>'    
    );*/

    // call the loaded callback when finished 
    if (this.options.loaded) {
      this.options.loaded(this);
    }
  };
  

  // multiple instantiations are forbidden
  $.fn[pluginName] = function ( options ) {
      return this.each(function () {
          if (!$.data(this, 'plugin_' + pluginName)) {
              $.data(this, 'plugin_' + pluginName, new Plugin( this, options ));
          }
      });
  }

}(jQuery, window));
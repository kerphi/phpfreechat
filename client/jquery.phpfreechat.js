;(function ($, window, undefined) {

  var pluginName = 'phpFreeChat',
      document = window.document,
      defaults = {
        propertyName: "value"
      };

  function Plugin(element, options) {
    this.element = element;
    this.options = $.extend({}, defaults, options) ;
    this._defaults = defaults;
    this._name = pluginName;
    this.init();
  }

  Plugin.prototype.init = function () {
    
    // this.element 
    // this.options
    
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
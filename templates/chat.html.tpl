<div class="~[$prefix]~container">
  <p class="~[$prefix]~today_date">Le ~[php]~echo date("d/m/Y")~[/php]~</p>
  <h2 class="~[$prefix]~title">~[$title|htmlspecialchars]~</h2>
  <div id="~[$prefix]~content">
    <div id="~[$prefix]~online"></div>
    <div id="~[$prefix]~chat"></div>
    <div class="~[$prefix]~smileys">
      ~[foreach from=$smileys key=s_file item=s_str]~
      <img src="~[$s_file]~" alt="~[$s_str[0]]~" onclick="~[$prefix]~insertSmiley('~[$s_str[0]]~');" />
      ~[/foreach]~
    </div>
    <div id="~[$prefix]~misc1"></div>
    <div id="~[$prefix]~misc2"></div>
    <div id="~[$prefix]~misc3"></div>
  </div>

  <div class="~[$prefix]~input_container">
    <input id="~[$prefix]~words" type="text" title="enter your text here" maxlength="~[$max_text_len]~" />
    <div id="~[$prefix]~cmd_container">
      <a href="http://www.phpfreechat.net" id="~[$prefix]~logo"><img src="http://www.phpfreechat.net/pub/logo_80x15.gif" alt="PHP FREE CHAT [powered by phpFreeChat-~[$version]~]" title="PHP FREE CHAT [powered by phpFreeChat-~[$version]~]" /></a>
      <input id="~[$prefix]~handle" type="button" title="enter your nickname here" maxlength="~[$max_nick_len]~" value="~[$nick]~" onclick="if (!~[$prefix]~login_status) return false; ~[$prefix]~handleRequest('/asknick ' + ~[$prefix]~clientid);" />
      <img src="~[$rootpath]~/misc/logout.png" alt="Logout" title="Logout" id="~[$prefix]~loginlogout"
           onclick="~[$prefix]~connect_disconnect()"/>
    </div>
  </div>

  <p id="~[$prefix]~errors"></p>

  <div id="~[$prefix]~misc4"></div>
  <div id="~[$prefix]~misc5"></div>
  <div id="~[$prefix]~misc6"></div>
  		
  <script type="text/javascript">
  <!--
  
  ~[include file="javascript2.js.tpl"]~
  
  -->
  </script>
</div>
~[if $debug]~
<p>Debug is on, you can <a href="~[$rootpath]~/debug/console.php?chatid=~[$id]~">open the debugging console</a>.</p>
~[/if]~

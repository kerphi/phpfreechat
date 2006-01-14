<div class="~[$prefix]~container">
  <p class="~[$prefix]~today_date">Le ~[php]~echo date("d/m/Y")~[/php]~</p>
  <h2 class="~[$prefix]~title">~[$title|htmlspecialchars]~</h2>
  <div id="~[$prefix]~content">
    <div id="~[$prefix]~online"></div>
    <div id="~[$prefix]~chat"></div>
    <div class="~[$prefix]~smileys">
      ~[foreach from=$smileys key=s_file item=s_str]~
      <img src="~[$s_file]~" alt="~[$s_str[0]]~" onclick="~[$prefix]~insertSmiley('~[$s_str[0]|addslashes]~');" />
      ~[/foreach]~
    </div>
    <div id="~[$prefix]~misc1"></div>
    <div id="~[$prefix]~misc2"></div>
    <div id="~[$prefix]~misc3"></div>
  </div>

  <div class="~[$prefix]~input_container">
  <table>
    <tr>
      <td width="1%"><input id="~[$prefix]~handle" type="button" title="enter your nickname here" maxlength="~[$max_nick_len]~" ~[if $frozen_nick!=""]~readonly="readonly" value="~[$init_nick]~"~[/if]~ onclick="~[$prefix]~handleRequest('/asknick');" /></td>
      <td><input id="~[$prefix]~words" type="text" title="enter your text here" maxlength="~[$max_text_len]~" /></td>
      <td width="1%"><a href="http://www.phpfreechat.net" id="~[$prefix]~logo"><img src="http://www.phpfreechat.net/pub/logo_80x15.gif" alt="Powered by phpFreeChat-~[$version]~" title="Powered by phpFreeChat-~[$version]~" /></a></td>
    </tr>
  </table>
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
<p>Debug is on, you can <a href="~[$debugpath]~/console.php?chatid=~[$id]~">open the debugging console</a>.</p>
~[/if]~

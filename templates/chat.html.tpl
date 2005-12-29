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
  <p class="~[$prefix]~input_container">
    <input id="~[$prefix]~words" type="text" title="enter your text here" maxlength="~[$max_text_len]~" />
    <input id="~[$prefix]~handle" type="text" title="enter your nickname here" maxlength="~[$max_nick_len]~" ~[if $frozen_nick!=""]~readonly="readonly" value="~[$init_nick]~"~[/if]~ />
  </p>
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
<p>Debug is on, you can <a href="~[$smarty.server.PHP_SELF|dirname]~/debug/console.php?chatid=~[$id]~">open the debugging console</a>.</p>
~[/if]~

<div class="~[$prefix]~container">
  <p class="~[$prefix]~today_date">Le ~[php]~echo date("d/m/Y")~[/php]~</p>
  <h2 class="~[$prefix]~title">~[$title|htmlspecialchars]~</h2>
  <div id="~[$prefix]~content">
    <div id="~[$prefix]~online"></div>
    <div id="~[$prefix]~chat"></div>
  </div>
  <p class="~[$prefix]~input_container">
    <input id="~[$prefix]~words" type="text" title="enter your text here" maxlength="~[$max_text_len]~" />
    <input id="~[$prefix]~handle" type="text" title="enter your nickname here" maxlength="~[$max_nick_len]~" ~[if $frozen_nick!=""]~readonly="readonly" value="~[$init_nick]~"~[/if]~ />
  </p>
  <p id="~[$prefix]~errors"></p>
  		
  <script type="text/javascript">
  <!--
  
  ~[include file="javascript2.js.tpl"]~
  
  -->
  </script>
</div>

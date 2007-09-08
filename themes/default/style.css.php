/* used to enable png transparency for IE6 */
div#pfc_container img, div#pfc_container div {
  behavior: url("<?php echo $c->getFileUrlFromTheme('iepngfix.htc'); ?>");
}

div#pfc_container {
  margin: 0; padding: 0;
  border: 1px solid #555;
  color: #000;
  padding: 10px;
  min-height: 20px;
  background-color: #FFF;
  background-image: url("<?php echo $c->getFileUrlFromTheme('images/background.gif'); ?>");
  background-position: right;
/*  background-repeat: repeat-xy;*/
  font-family: Verdana, Sans-Serif; /* without this rule, the tabs are not correctly display on FF */
}

div#pfc_container a img { border: 0px; }
  
#pfc_minmax {
  margin: 0; padding: 0;
  cursor: pointer;
}
div#pfc_content_expandable {
  margin: 0; padding: 0;
  margin-top: 0.2em;
}

div#pfc_channels_content {
  margin: 0; padding: 0;
  z-index: 20;
  position: relative;
  width: 100%;
  border-right: 1px solid #555;
  border-left: 1px solid #555;
  border-bottom: 1px solid #555;
  background-color: #FFF;
  height: <?php echo ($c->height!=''?$c->height:'300px'); ?>;
}
div.pfc_content {
  margin: 0; padding: 0;
}

/* channels tabpanes */
ul#pfc_channels_list {
  margin: 0; padding: 0;
  list-style-type: none;
  display: block;
  z-index: 50;
  border-bottom: 1px solid #555;
  /*  margin-bottom: -5px;*/
  line-height: 100%;
}
ul#pfc_channels_list li {
  margin: 0; padding: 0;
  display: inline;
  margin-left: 5px;
}
ul#pfc_channels_list li img {
  margin: 0; padding: 0;
  vertical-align: bottom;
}
ul#pfc_channels_list li div {
  margin: 0; padding: 0;
  display: inline;
  padding: 0 4px 0 4px;
  border-top: 1px solid #555;
  border-right: 1px solid #555;
  border-left: 1px solid #555;
  border-bottom: 1px solid #555;
  background-color: #DDD;
  vertical-align: bottom;  
}
ul#pfc_channels_list li.selected div {
  background-color: #FFF;
  border-bottom: 1px solid #FFF;
  color: #000;
  font-weight: bold;  
}
/* this rule does not work on ie6 ( :hover ) */
ul#pfc_channels_list li div:hover {
  background-color: #FFF;
}
ul#pfc_channels_list li a {
  margin: 0; padding: 0;
  color: #000;
  text-decoration: none;  
}
ul#pfc_channels_list li a.pfc_tabtitle {
  cursor: pointer;
}
ul#pfc_channels_list li a.pfc_tabtitle img {
  padding-right: 4px;
}
ul#pfc_channels_list li a.pfc_tabclose {
  margin-left: 4px;
  cursor: pointer;
}
/* blinking stuff (tab notifications) */
ul#pfc_channels_list li div.pfc_tabblink2 {
  background-color: #FFF;
}


div.pfc_chat {
  margin: 0; padding: 0;
  z-index: 100;
  position: absolute;
  top: 0;
  left: 0;
  width: 80%;
/* WARNING: do not fix height in % because it will display blank screens on IE6 */
/*  height: 100%;*/
  overflow: auto;
  word-wrap: break-word;
}
div.pfc_chat div {
  margin: 0; padding: 0; border: none;
}

div.pfc_online {
  margin: 0; padding: 0;
  position: absolute;
  right: 0;
  top: 0;
  overflow: auto;
  width: 20%;
/* WARNING: do not fix height in % because it will display blank screens on IE6 */
/*  height: 100%;*/
  color: #000; /* colors can be overriden by js nickname colorization */
  background-color: #FFF;

  /* borders are drawn by this image background */
  background-image: url("<?php echo $c->getFileUrlFromTheme('images/online-separator.gif'); ?>");
  background-position: left;
  background-repeat: repeat-y;
}
div.pfc_online ul {
  margin: 4px; padding: 0;
  list-style-type: none;
  font-size: 90%;
  font-weight: bold;  
}
ul.pfc_nicklist li {
  margin: 0 0 5px 0; padding: 0;
  border-bottom: 1px solid #AAA;
  background-image: none;
}
ul.pfc_nicklist img {
  vertical-align: middle; /* fix icon position problem in IE6 */
}
ul.pfc_nicklist a {
  text-decoration: none;
}
ul.pfc_nicklist nobr span {
  margin: 0; padding: 0;
  display: inline;
  text-decoration: none;
}




h2#pfc_title {
  margin:0; padding:0; border: none;
  font-size: 110%;
}

img#pfc_minmax {
  float: right;
}

.pfc_invisible {
  display: none;
}

div.pfc_message {
  margin: 0; padding: 0;
  background-image: url("<?php echo $c->getFileUrlFromTheme('images/newmsg.gif'); ?>");
  background-position: right;
  background-repeat: no-repeat; 
}
div.pfc_message img {
  margin: 0; padding: 0;
  vertical-align: middle;
}
div.pfc_oldmsg {
  background-image: url("<?php echo $c->getFileUrlFromTheme('images/oldmsg.gif'); ?>");
  background-position: right;
  background-repeat: no-repeat; 
}

span.pfc_date, span.pfc_heure {
  color: #bebebe;
  font-size: 70%;
}
span.pfc_nick {
  color: #fbac17;
  font-weight: bold;
}

div#pfc_input_container {
  margin: 5px 0 0 0; padding: 0;
}
div#pfc_input_container input {
  margin: 0; padding: 0;
}

div#pfc_input_container table  { border: none; margin: 0; padding: 0; }
div#pfc_input_container tbody  { border: none; margin: 0; padding: 0; }
div#pfc_input_container td     { border: none; margin: 0; padding: 0; }

div#pfc_input_container td.pfc_td2 {
  padding-right: 5px;
  width: 100%;
}

input#pfc_words {
  margin: 0; padding: 0;
  border: #555 solid 1px;
  background-color: #FAFAFA;
  width: 100%;
  font-size: 12px;
  height: 20px;
  vertical-align: bottom;
  font-size: 1em;
  height: 1.2em;
}

input#pfc_send {
  margin: 0; padding: 0;
  display: block;
  padding: 2px;
  border: 1px solid #555;
  background-color: #CCC;
  font-size: 10px;
  vertical-align: bottom;
  font-size: 0.7em;
  height: 1.9em;
  cursor: pointer;
}

div#pfc_cmd_container {
  position: relative;
  margin: 4px 0 0 0; padding: 0;
}

p#pfc_handle {
  margin: 0; padding: 0;
  display: inline;
  margin-right: 5px;
  color: black;
  font-weight: bold;
  /*background-color: #EEE;*/
  font-size: 70%;          /* these two line fix a display problem in IE6 : */
  vertical-align: top;
  white-space: pre;
}

a#pfc_logo {
  margin: 0; padding: 0;
  float: right;
}
#pfc_ping { 
  margin: 0 5px 0 0; padding: 0;
  float:right;
  font-size: 80%;
}
a#pfc_logo img {
  margin: 0; padding: 0;
}
div.pfc_btn {
  margin: 0; padding: 0;
  display: inline;  
  cursor: pointer;
}
div.pfc_btn img {
  margin: 0; padding: 0; border: none;
  vertical-align: middle;
}

div#pfc_bbcode_container {
  margin: 4px 0 4px 0; padding: 0;
}

div#pfc_errors {
  margin: 0 0 4px 0; padding: 5px;
  display: none;
  border: 1px solid #555;
  color: #EC4B0F;
  background-color: #FFBB77;
  font-style: italic;
  font-family: monospace;
  font-size: 90%;
}

/* commands */
.pfc_cmd_msg {
  color: black;
}
.pfc_cmd_me {
  font-style: italic;
  color: black;
}
.pfc_cmd_notice {
  font-style: italic;
  color: #888;
}

/* commands info */
.pfc_info {
  color: #888;

  /* to fix IE6 display bug */
  /* http://sourceforge.net/tracker/index.php?func=detail&aid=1545403&group_id=158880&atid=809601 */
  font-family: sans-serif; /* do NOT setup monospace font or it will not work in IE6 */

  font-style: italic;
  background-color: #EEE;
  font-size: 80%;
}

div#pfc_colorlist {
  margin:0; padding:0;
  display: none;
}
img.pfc_color {
  margin: 1px;padding: 1px;
  cursor: pointer;
  vertical-align: middle;
}

.pfc_nickmarker {
  white-space: pre;
}

div#pfc_smileys {
  margin: 0; padding: 0;
  display: none; /* will be shown by javascript routines */
  background-color: #FFF;
  border: 1px solid #555;
  padding: 4px;
}
div#pfc_smileys img {
  margin: 0; padding: 0;
  margin-right: 2px;
  cursor: pointer;
  vertical-align: middle;
}

div.pfc_nickwhois { padding: 0; margin: 0; }
div.pfc_nickwhois a img { border: none; }
div.pfc_nickwhois {
  border: 1px solid #444;
  background-color: #FFF;
  font-size: 75%;
}
.pfc_nickwhois_header {
  margin: 0; padding: 0;
  background-color: #EEE;
  border-bottom: 1px solid #444;
  text-align: center;
  font-weight: bold;
  vertical-align: middle;
}
.pfc_nickwhois_header img {
  float: left;
  cursor: pointer;
  vertical-align: middle;
  margin: 3px 0 3px 2px;
}
div.pfc_nickwhois table {
  width: 120px;
}

div.pfc_nickwhois table  { border: none; margin: 0; padding: 0; }
div.pfc_nickwhois tbody  { border: none; margin: 0; padding: 0; }
div.pfc_nickwhois td     { border: none; margin: 0; padding: 0 0 0 2px; }

td.pfc_nickwhois_c1 {
  font-weight: bold;
}
td.pfc_nickwhois_c2 {
}
.pfc_nickwhois_pv {
  margin:0; padding: 0 0 0 2px;
  text-align: left;
}
.pfc_nickwhois_pv a {
  text-decoration: none;
}


img.pfc_nickbutton {
  cursor: pointer;
}

div#pfc_debug {
  font-size: 11px;
}
div#pfc_sound_container {
  position: absolute;
  top: 0;
  left: 0;
  visibility:hidden; /* this box is hidden because it contains a flash sound media (sound.swf)*/
  width: 0;
  height: 0;
}


/* The DHTML prompt */
div#pfc_promptbox {
  border: 2px solid #000;
  background-color: #DDD;
  width: 350px;
}
div#pfc_promptbox h2 {
  margin: 0;
  width: 100%;
  background-color: #888;
  color: white;
  font-family: verdana;
  font-size: 10pt;
  font-weight: bold;
  height: 20px;
}
div#pfc_promptbox p {
  margin: 10px 0 0 10px;
}
div#pfc_promptbox form {
  margin: 0 10px 10px 10px;
  text-align: right;
}
div#pfc_promptbox input {
  border: 1px solid #000;
}
input#pfc_promptbox_field {
  width: 100%;
}
input#pfc_promptbox_submit {
  margin: 0;
}
input#pfc_promptbox_cancel {
  margin: 5px 10px 0 0;
}

div#<?php echo $prefix; ?>container * {
  border: 0;
  margin: 0;
  padding: 0;
}

div#<?php echo $prefix; ?>container {
  <?php if ($width!="") { ?>width: <?php echo $width; ?>;<?php } ?>
  border: black solid 1px;
  color: #338822;
  background-color: #d9edd8;
  background-image: url(<?php echo $c->getFileUrlFromTheme('images/shade.gif'); ?>);
  background-position: right;
  background-repeat: repeat-y;
  padding: 10px;
  min-height: 20px;
}

#<?php echo $prefix; ?>minmax {
cursor: pointer;
}

div#<?php echo $prefix; ?>content {
  <?php if ($height!="") { ?>height: <?php echo $height; ?>;<?php } ?>
  position: relative;
  margin-top: 0.5em;
  width: 100%;
}

div#<?php echo $prefix; ?>chat {
  position: absolute;
  left: 0;
  top: 0;
  border: 1px solid black;
  overflow: auto;
  width: 79%;
  height: 100%;
  background-color: #e0edde;
}

div#<?php echo $prefix; ?>smileys {
  position: absolute;
  bottom: 0;
  right: 0;
  padding: 0;
  width: 20%;
  height: 58%;
  overflow: auto;
  text-align: center;
  border: 1px solid black;
  background-color: #FFF;
}

div#<?php echo $prefix; ?>online {
  position: absolute;
  right: 0;
  top: 0;
  padding: 0;
  overflow: auto;
  border: black solid 1px;
  color: #000;
  background-color: #FFF;
  width: 20%;
  height: 39%;
}
div#<?php echo $prefix; ?>online ul {
  list-style-type: none;
  margin: 0;
  padding: 0;
  margin-left: 8px;
  margin-right: 8px;
}
div#<?php echo $prefix; ?>online li {
  border-bottom: 1px solid #DDD;
  font-weight: bold;
  font-size: 90%;
}

h2#<?php echo $prefix; ?>title {
  font-size: 110%;
}

img#<?php echo $prefix; ?>minmax {
  float: right;
}

.<?php echo $prefix; ?>invisible {
  display: none;
}

div.<?php echo $prefix; ?>message {
  margin: 0;
}
.<?php echo $prefix; ?>words {
  font-size: 90%;
}

div.<?php echo $prefix; ?>oldmsg {
  background-color: #dde4dc;
}

span.<?php echo $prefix; ?>heure, span.<?php echo $prefix; ?>date {
  color: #bebebe;
  font-size: 70%;
}

span.<?php echo $prefix; ?>nick {
  color: #fbac17;
  font-weight: bold;
}

div#<?php echo $prefix; ?>input_container {
  margin-top: 5px;
}
div<?php echo $prefix; ?>input_container table {
  width: 100%;
}

input#<?php echo $prefix; ?>words {
  border: black solid 1px;
  width: 100%;
  height: 1.3em;
}

div#<?php echo $prefix; ?>cmd_container {
  position: relative;
  margin-top: 5px;
  width: 100%;
}

input#<?php echo $prefix; ?>handle {
  border: black solid 1px;
  padding: 0 4px 0 4px;
  color: black;
  <?php if ($nick!="") { ?>background-color: #CCC;<?php } ?>
  text-align: center;
  margin-bottom: 5px;
}

a#<?php echo $prefix; ?>logo {
  position: absolute;
  right: 0;
  top: 0;
}

div.<?php echo $prefix; ?>btn {
  display: inline;
  cursor: pointer;
}
div.<?php echo $prefix; ?>btn img {
  border: 1px solid #393; /* same as container color */
}
div.<?php echo $prefix; ?>btn img:hover {
  border: 1px solid #000;
}

p#<?php echo $prefix; ?>errors {
  display: none;
  margin-top: 5px;
  padding: 2px;
  height: 18px;

  border: black solid 1px;
  color: #EC4A1F;
  background-color: #FFBA76;
  text-align: center;
  font-style: italic;
  font-weight: bold;
}

/* commands */
.<?php echo $prefix; ?>cmd_msg {
  color: black;
}
.<?php echo $prefix; ?>cmd_me {
  font-style: italic;
  color: black;
}
.<?php echo $prefix; ?>cmd_notice {
  font-style: italic;
  color: #888;
}

div#<?php echo $prefix; ?>colorlist {
  display: none;
}
img.<?php echo $prefix; ?>color {
  padding: 1px;
  cursor: pointer;
}

.<?php echo $prefix; ?>nickmarker {
  white-space: pre;
}

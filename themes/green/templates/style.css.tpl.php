div#<?php echo $prefix; ?>container {
  border: 1px solid #555;
  color: #338822;
  background-color: #d9edd8;
  background-image: url(<?php echo $c->getFileUrlFromTheme('images/shade.gif'); ?>);
  background-position: right;
  background-repeat: repeat-y;
}

div#<?php echo $prefix; ?>channels_content {
  border-right: 1px solid #555;
  border-left: 1px solid #555;
  border-bottom: 1px solid #555;
  background-color: #e0edde;
}

/* channels tabpanes */
ul#<?php echo $prefix; ?>channels_list {
  border-bottom: 1px solid #555;
}
ul#<?php echo $prefix; ?>channels_list li div {
  border-top: 1px solid #555;
  border-right: 1px solid #555;
  border-left: 1px solid #555;
  border-bottom: 1px solid #555;
  background-color: #7dc073;
}
ul#<?php echo $prefix; ?>channels_list li.selected div {
  background-color: #e0edde;
  border-bottom: 1px solid #e0edde;
  color: #000;
}
ul#<?php echo $prefix; ?>channels_list li > div:hover {
  background-color: #e0edde;
}
ul#<?php echo $prefix; ?>channels_list li a {
  color: #000;
}

div.<?php echo $prefix; ?>smileys {
  border: 1px solid #000;
  background-color: #EEE;
}
div.<?php echo $prefix; ?>online {
  border: black solid 1px;
  color: #000;
  background-color: #DDD;
}
div.<?php echo $prefix; ?>online li {
  border-bottom: 1px solid #DDD;
}

h2#<?php echo $prefix; ?>title {
  font-size: 110%;
}

div.<?php echo $prefix; ?>oldmsg {
  background-color: #dde4dc;
}

span.<?php echo $prefix; ?>heure, span.<?php echo $prefix; ?>date {
  color: #bebebe;
}

span.<?php echo $prefix; ?>nick {
  color: #fbac17;
}

input#<?php echo $prefix; ?>words {
  border: black solid 1px;
}

input#<?php echo $prefix; ?>handle {
  border: black solid 1px;
  color: black;
  <?php if ($nick!="") { ?>background-color: #CCC;<?php } ?>
}

div.<?php echo $prefix; ?>btn img {
  border: 1px solid #393; /* same as container color */
}
div.<?php echo $prefix; ?>btn img:hover {
  border: 1px solid #000;
}

p#<?php echo $prefix; ?>errors {
  border: black solid 1px;
  color: #EC4A1F;
  background-color: #FFBA76;
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
pre.<?php echo $prefix; ?>cmd_rehash,
pre.<?php echo $prefix; ?>cmd_help
{
  color: #888;
  font-style: italic;
}

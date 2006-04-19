
div#<?php echo $prefix; ?>container {
  border: black double 5px;
  background-image: url(<?php echo $c->getFileUrlFromTheme('images/brick.jpg'); ?>);
  background-repeat: repeat;
  padding: 20px;
  color: black;
  margin: auto;
}
div#<?php echo $prefix; ?>chat {
  background-color: #FFF;
}

div#<?php echo $prefix; ?>content {
  border: none;
}

div.<?php echo $prefix; ?>message {
  background-color: transparent;
  background-image: url(<?php echo $c->getFileUrlFromTheme('images/newmsg.gif'); ?>);
  background-repeat: no-repeat;
  background-position: right center;
}

div.<?php echo $prefix; ?>oldmsg {
  background-image: url(<?php echo $c->getFileUrlFromTheme('images/oldmsg.gif'); ?>);
  background-color: #EEE;
}

span.<?php echo $prefix; ?>heure {
  margin-left: 25px;
  color: #888;
}

span.<?php echo $prefix; ?>date {
  display: none;
}

span.<?php echo $prefix; ?>pseudo {
  color: black;
  font-weight: bold;
}

input#<?php echo $prefix; ?>handle {
  color: black;
  font-weight: bold;
}

div#<?php echo $prefix; ?>online {
}

div.<?php echo $prefix; ?>btn img {
  border: 1px solid #FFF; /* same as container color */
}
div.<?php echo $prefix; ?>btn img:hover {
  border: 1px solid #000;
  background-color: #CCC;
}


/* commands */
.<?php echo $prefix; ?>cmd_notice {
  color: red;
}
.<?php echo $prefix; ?>cmd_msg {
  color: #555;
}

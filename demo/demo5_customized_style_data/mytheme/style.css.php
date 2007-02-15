
div#pfc_container {
  border: black double 5px;
  background-image: url("<?php echo $c->getFileUrlFromTheme('images/brick.jpg'); ?>");
  background-repeat: repeat;
  padding: 20px;
  color: black;
  margin: auto;
}
div#pfc_chat {
  background-color: #FFF;
}

div#pfc_content {
  border: none;
}

div.pfc_message {
  background-color: transparent;
  background-image: url("<?php echo $c->getFileUrlFromTheme('images/newmsg.gif'); ?>");
  background-repeat: no-repeat;
  background-position: right center;
}

div.pfc_oldmsg {
  background-image: url("<?php echo $c->getFileUrlFromTheme('images/oldmsg.gif'); ?>");
}

span.pfc_heure {
  margin-left: 25px;
  color: #888;
}

span.pfc_date {
  display: none;
}

span.pfc_pseudo {
  color: black;
  font-weight: bold;
}

input#pfc_handle {
  color: black;
  font-weight: bold;
}

div#pfc_online {
}

div.pfc_btn img {
  border: 1px solid #FFF; /* same as container color */
}
div.pfc_btn img:hover {
  border: 1px solid #000;
  background-color: #CCC;
}


/* commands */
.pfc_cmd_notice {
  color: red;
}
.pfc_cmd_msg {
  color: #555;
}
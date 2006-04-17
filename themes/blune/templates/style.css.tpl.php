
div#<?php echo $prefix; ?>container {
  color: #2A4064;
  background-color: #BEC5D0;
  background-image: url(<?php echo $c->getFileUrlFromTheme('images/shade.gif'); ?>);
}

div#<?php echo $prefix; ?>chat {
  background-color:#CED4DF;
}

div.<?php echo $prefix; ?>message {
  background-color:#CED4DF;
}

div.<?php echo $prefix; ?>oldmsg {
  background-color:#DCDEE4;
}

span.<?php echo $prefix; ?>nick {
  color:#2A4064;
}

div.<?php echo $prefix; ?>btn img:hover {
  border: 1px solid #000;
}

div#<?php echo $prefix; ?>online {
  height: 48%;
}

div#<?php echo $prefix; ?>smileys {
  height: 48%;
}

p#<?php echo $prefix; ?>errors {
  display: none;
  margin-top: 5px;
  padding: 2px;
  height: 18px;

  border: black solid 1px;
  color: #EC4A1F;
  background-color: #BEC5D0;
  text-align: center;
  font-style: italic;
  font-weight: bold;
}
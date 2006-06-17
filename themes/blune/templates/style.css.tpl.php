
div#<?php echo $prefix; ?>container {
  color: #2A4064;
  background-color: #BEC5D0;
  background-image: url(<?php echo $c->getFileUrlFromTheme('images/shade.gif'); ?>);
}

div.<?php echo $prefix; ?>chat {
  background-color:#CED4DF;
}

div.<?php echo $prefix; ?>message {
  background-color:#CED4DF;
}

div.<?php echo $prefix; ?>oldmsg {
  background-image: none;
  background-color:#DCDEE4;
}

span.<?php echo $prefix; ?>nick {
  color:#2A4064;
}

div.<?php echo $prefix; ?>btn img:hover {
  border: 1px solid #000;
}

p#<?php echo $prefix; ?>errors {
  display: none;
  margin-top: 5px;
  padding: 2px;
  height: 18px;

  border: #555 solid 1px;
  color: #EC4A1F;
  background-color: #BEC5D0;
  text-align: center;
  font-style: italic;
  font-weight: bold;
}

ul#<?php echo $prefix; ?>channels_list li div {
  background-color: #bec5d0;
  border-bottom: 1px solid #bec5d0;
}
ul#<?php echo $prefix; ?>channels_list li.selected div {
  background-color: #CED4DF;
  border-bottom: 1px solid #CED4DF;
  color: #000;
  font-weight: bold;
}
ul#<?php echo $prefix; ?>channels_list li > div:hover {
  background-color: #CED4DF;
  border-bottom: 1px solid #CED4DF;
}
ul#<?php echo $prefix; ?>channels_list li.selected > div:hover {
  background-color: #CED4DF;
}

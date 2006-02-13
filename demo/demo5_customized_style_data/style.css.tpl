
div#<?php echo $prefix; ?>container {
  border: black double 5px;
  background-image: url(demo5_customized_style_data/brick.jpg);
  background-repeat: repeat;
  padding: 20px;
  color: black;
  margin: auto;
}

div#<?php echo $prefix; ?>content {
  border: none;
}

div.<?php echo $prefix; ?>message {
  background-color: transparent;
  background-image: url(demo5_customized_style_data/newmsg.gif);
  background-repeat: no-repeat;
  background-position: right center;
}

div.<?php echo $prefix; ?>oldmsg {
  background-image: url(demo5_customized_style_data/oldmsg.gif);
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


#<?php echo $prefix; ?>words {
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

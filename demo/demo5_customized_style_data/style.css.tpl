
div#~[$prefix]~container {
  border: black double 5px;
  background-image: url(demo5_customized_style_data/brick.jpg);
  background-repeat: repeat;
  padding: 20px;
  color: black;
  margin: auto;
}

div#~[$prefix]~content {
  border: none;
}

div.~[$prefix]~message {
  background-color: transparent;
  background-image: url(demo5_customized_style_data/newmsg.gif);
  background-repeat: no-repeat;
  background-position: right center;
}

div.~[$prefix]~oldmsg {
  background-image: url(demo5_customized_style_data/oldmsg.gif);
}

span.~[$prefix]~heure {
  margin-left: 25px;
  color: #888;
}

span.~[$prefix]~date {
  display: none;
}

span.~[$prefix]~pseudo {
  color: black;
  font-weight: bold;
}


#~[$prefix]~words {
}

input#~[$prefix]~handle {
  color: black;
  font-weight: bold;
}

div#~[$prefix]~online {
}

div.~[$prefix]~btn img {
  border: 1px solid #FFF; /* same as container color */
}
div.~[$prefix]~btn img:hover {
  border: 1px solid #000;
  background-color: #CCC;
}

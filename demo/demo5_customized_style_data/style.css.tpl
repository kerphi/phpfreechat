div.~[$prefix]~container {
  margin: auto;
  border: black double 5px;
  background-image: url(demo5_customized_style_data/brick.jpg);
  padding: 20px;
}

div#~[$prefix]~content {
  ~[if $height!=""]~height: ~[$height]~;~[/if]~
  clear: both;
  overflow: auto;
  margin: 0px;
  border-left: black solid 1px;
  border-right: black solid 1px;
  border-top: black solid 1px;
}

div.~[$prefix]~message {
  background-color: transparent;
  background-image: url(demo5_customized_style_data/newmsg.gif);
  background-repeat: no-repeat;
  background-position: left center;
}

div.~[$prefix]~oldmsg {
  background-image: url(demo5_customized_style_data/oldmsg.gif);
}

span.~[$prefix]~heure {
  margin-left: 25px;
  color: #888;
}

span.~[$prefix]~pseudo {
  color: black;
  font-weight: bold;
}


#~[$prefix]~words {
  width: 84%;
}

#~[$prefix]~handle {
  width: 15%;
  color: black;
  font-weight: bold;
}

div#~[$prefix]~chat {
  width: 84%;
}

div#~[$prefix]~online {
  position: absolute;
  right: 0;
  top: 0;
  overflow: auto;
  border: none;
  background-color: transparent;
  width: 15%;
  border-left: black solid 1px;
}
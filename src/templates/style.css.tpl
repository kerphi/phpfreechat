div.~[$prefix]~container * {
  border: 0;
  margin: 0;
  padding: 0;
}

div.~[$prefix]~container {
  ~[if $width!=""]~width: ~[$width]~;~[/if]~
  border: black solid 1px;
  background-color: #9A9;
  padding: 10px;
}

div#~[$prefix]~content {
  ~[if $height!=""]~height: ~[$height]~;~[/if]~
  position: relative;
  clear: both;
  width: 100%;
}

div#~[$prefix]~chat {
  position: absolute;
  left: 0;
  top: 0;
  overflow: auto;
  width: 89%;
  ~[if $height!=""]~height: ~[$height]~;~[/if]~
}

div#~[$prefix]~online {
  position: absolute;
  right: 0;
  top: 0;
  overflow: auto;
  border: black dotted 1px;
  background-color: #FFF;
  width: 10%;
  height: 100%;
}
div#~[$prefix]~online ul {
  list-style-type: none;
  margin: 10px 0px 10px 2px;
  white-space: nowrap;
}

h2.~[$prefix]~title {
  float: left;
  font-size: 110%;
}

p.~[$prefix]~today_date {
  float: right;
  font-size: 80%;
}

.~[$prefix]~invisible {
  display: none;
}

div.~[$prefix]~message {
  background-color: #DDD;
  margin: 2px 0 2px 0;
}

div.~[$prefix]~oldmsg {
  background-color: #CCC;
}

span.~[$prefix]~heure {
  color: red;
}

span.~[$prefix]~pseudo {
  color: blue;
}

p.~[$prefix]~input_container {
  height: 1.3em;
  margin-top: 5px;
  clear: both;
}

#~[$prefix]~words {
  border: black solid 1px;
  float: left;
  width: 89%;
  height: 1.3em;
}

#~[$prefix]~handle {
  border: black solid 1px;
  float: right;
  width: 10%;
  height: 1.3em;
  color: blue;
  ~[if $pseudo!=""]~background-color: #CCC;~[/if]~
  text-align: center;
}

p#~[$prefix]~errors {
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

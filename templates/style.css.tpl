div.~[$prefix]~container * {
  border: 0;
  margin: 0;
  padding: 0;
}

div.~[$prefix]~container {
  ~[if $width!=""]~width: ~[$width]~;~[/if]~
  border: black solid 1px;
  background-color: #339933;
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
  border: 1px solid black;
  overflow: auto;
  width: 79%;
  ~[if $height!=""]~height: ~[$height]~;~[/if]~
  background-color: #FFF;
}

div.~[$prefix]~smileys {
  position: absolute;
  bottom: 0;
  right: 0;
  padding: 0;
  width: 20%;
  height: 58%;
  overflow: auto;
  text-align: center;
  border: 1px dotted black;
  background-color: #FFF;
}

div#~[$prefix]~online {
  position: absolute;
  right: 0;
  top: 0;
  padding: 0;
  overflow: auto;
  border: black dotted 1px;
  background-color: #FFF;
  width: 20%;
  height: 39%;
}
div#~[$prefix]~online ul {
  list-style-type: none;
  margin: 0;
  padding: 0;
  margin-left: 8px;
  margin-right: 8px;
  white-space: nowrap;
}
div#~[$prefix]~online li {
  border-bottom: 1px solid #DDD;
}

h2.~[$prefix]~title {
  float: left;
  font-size: 110%;
  color: #FFF;
}

p.~[$prefix]~today_date {
  float: right;
  font-size: 80%;
  color: #FFF;
}

.~[$prefix]~invisible {
  display: none;
}

div.~[$prefix]~message {
  background-color: #e0edde;
  margin: 0;
}

div.~[$prefix]~oldmsg {
  background-color: #dde4dc;
}

span.~[$prefix]~heure {
  color: #bebebe;
}

span.~[$prefix]~pseudo {
  color: orange;
}

div.~[$prefix]~input_container {
  margin-top: 10px;
}
div.~[$prefix]~input_container table {
  width: 100%;
}

#~[$prefix]~words {
  border: black solid 1px;
  width: 99.8%;
  height: 1.3em;
}

#~[$prefix]~handle {
  border: black solid 1px;
  padding: 0 4px 0 4px;
  height: 1.4em;
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

#~[$prefix]~logo {
  margin: auto;
}

/* commands */
.~[$prefix]~cmd_msg {
  color: black;
}
.~[$prefix]~cmd_me {
  font-style: italic;
  color: black;
}
.~[$prefix]~cmd_notice {
  font-style: italic;
  color: grey;
}

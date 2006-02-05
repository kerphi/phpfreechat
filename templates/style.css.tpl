div#~[$prefix]~container * {
  border: 0;
  margin: 0;
  padding: 0;
}

div#~[$prefix]~container {
  ~[if $width!=""]~width: ~[$width]~;~[/if]~
  border: black solid 1px;
  color: #338822;
  background-image: url(~[$rootpath]~/data/public/images/shade.gif);
  background-position: right;
  background-repeat: repeat-y;
  padding: 10px;
  min-height: 20px;
}

#~[$prefix]~minmax {
cursor: pointer;
}

div#~[$prefix]~content {
  ~[if $height!=""]~height: ~[$height]~;~[/if]~
  position: relative;
  margin-top: 1.5em;
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

div#~[$prefix]~smileys {
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
  color: #000;
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
  font-weight: bold;
  font-size: 90%;
}

h2#~[$prefix]~title {
  float: left;
  font-size: 110%;
}

img#~[$prefix]~minmax {
  float: right;
}

.~[$prefix]~invisible {
  display: none;
}

div.~[$prefix]~message {
  background-color: #e0edde;
  margin: 0;
}
.~[$prefix]~words {
  font-size: 90%;
}

div.~[$prefix]~oldmsg {
  background-color: #dde4dc;
}

span.~[$prefix]~heure, span.~[$prefix]~date {
  color: #bebebe;
  font-size: 70%;
}

span.~[$prefix]~nick {
  color: #fbac17;
  font-weight: bold;
}

div#~[$prefix]~input_container {
  margin-top: 5px;
}
div~[$prefix]~input_container table {
  width: 100%;
}

input#~[$prefix]~words {
  border: black solid 1px;
  width: 100%;
  height: 1.3em;
}

div#~[$prefix]~cmd_container {
  position: relative;
  margin-top: 5px;
  width: 100%;
}

input#~[$prefix]~handle {
  border: black solid 1px;
  padding: 0 4px 0 4px;
  color: black;
  ~[if $nick!=""]~background-color: #CCC;~[/if]~
  text-align: center;
  margin-bottom: 5px;
}

a#~[$prefix]~logo {
  position: absolute;
  right: 0;
  top: 0;
}

div.~[$prefix]~btn {
  display: inline;
  cursor: pointer;
}
div.~[$prefix]~btn img {
  border: 1px solid #393; /* same as container color */
}
div.~[$prefix]~btn img:hover {
  border: 1px solid #000;
  background-color: #4A4;
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
  color: #888;
}

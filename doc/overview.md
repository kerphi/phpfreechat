# Overview

phpFreeChat (pfc) is a Web based chat written in JQuery and PHP. Perfect if you need an easy to integrate chat for your Web site.

## Features

* themable web interface
* responsive web interface (mobile, tablet, desktop)
* multi-user management
* polling refresh system (with ajax)
* modular authentication system (phpbb3 integration available)
* hook system to enable features extension
* file system used for storage (no database)
* coming soon:
  * be able to rename the username (/nick command)
  * be able to create private messages
  * multi-channel management
  * long polling refresh system (to improve reactvity)
  * user's avatars management
  * user's role/rights management (admin, users)
  * user's presence management (away, online)
  * messages with smiley
  * messages with url detection (open in a new window)
  * messages with color, bold, or underline
  * news message notification
  * log message system


## Architecture

pfc architecture is splited in two distinct parts:

- client: a themable jquery plugin in charge of displaying the chat interface and to communicate with the server side using for example AJAX
- server: a [RESTful architecture](http://en.wikipedia.org/wiki/Representational_state_transfer) coded in PHP using the [Slim framework](http://www.slimframework.com/) in charge of the chat logic. It stores messages and send messages updates to the clients using classic HTTP methods (GET, POST, PUT, DELETE).

Here is an example of a basic communication between client and server:

* Client asks server to authenticate the user, server stores the user and returns a session id to the client.
* Client joins a channel, server stores that this user joined the channel and sends a "join" message to every connected users in this channel.
* Client sends a message into this channel, server publish this message into a queue for each connected users in this channel.
* Client read its pending messages, server read the user's queue and returns the messages list, client displays the messages on the interface.

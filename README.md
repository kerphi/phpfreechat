# phpFreeChat

phpFreeChat (pfc) is a Web based chat written in JQuery and PHP. Perfect if you need an easy to integrate chat for your Web site.

pfc is splited in two distinct parts:

- client: a themable jquery plugin in charge of displaying the chat interface and to communicate with the server side using for example AJAX
- server: a [RESTful architecture](http://en.wikipedia.org/wiki/Representational_state_transfer) coded in PHP using the [Slim framework](http://www.slimframework.com/) in charge of the chat logic. It stores messages and send messages updates to the clients using classic HTTP methods (GET, POST, PUT, DELETE).

Here is an example of a basic communication between client and server:

* Client asks server to authenticate the user, server stores the user and returns a session id to the client.
* Client joins a channel, server stores that this user joined the channel and sends a "join" message to every connected users in this channel.
* Client sends a message into this channel, server publish this message into a queue for each connected users in this channel.
* Client read its pending messages, server read the user's queue and returns the messages list, client displays the messages on the interface.

## Quick start installation

Include phpfreechat plugin in your html `<head>`:
```html
  <link type="text/css" href="phpfreechat-2.0.0/client/themes/default/jquery.phpfreechat.min.css" />
  <script src="phpfreechat-2.0.0/client/jquery.phpfreechat.min.js" type="text/javascript"></script>
```

Add a piece of HTML in your `<body>` where you want the chat to be displayed:
```html
...
<div id="mychat"><a href="http://www.phpfreechat.net">phpFreeChat: simple Web chat</a></div>
...
```

Hook the phpfreechat plugin to this element:
```html
<script>
  $('#mychat').phpfreechat();
</script>
```

## Themes

phpfreechat is released with few themes. You can choose which one you want to use:

* `default`
* `carbon`

To select the theme, you only have to change one line in your html `<head>`. To use the `default` theme:
```html
  <link type="text/css" href="phpfreechat-2.0.0/client/themes/default/jquery.phpfreechat.min.css" />
```
or this code for `carbon` theme:
```html
  <link type="text/css" href="phpfreechat-2.0.0/client/themes/carbon/jquery.phpfreechat.min.css" />
```

## Parameters (client side)

* `refresh_delay` [Integer:5000]: miliseconds to wait before next pending messages are checked
* `focus_on_connect` [Bool:true]: setting this to true will give the focus to the input text box when connecting to the chat. It can be useful not to touch the focus when integrating the chat into an existing website because when the focus is changed, the viewport follows the focus location.
* `serverUrl` [String:'../server']: where is located the pfc's server folder
* `loaded` [Function:null]: a callback executed when pfc's interface is totaly loaded
* `loadTestData` [Bool:false]: used for unit tests. It load test data into the interface.

Client side parameters can be given to phpfreechat client side jquery plugin as a javascript object.

Example:
```javascript
$('#mychat').phpfreechat({
  refresh_delay: 2000
});
```

## Parameters (server side)

Server side parameters are located in `server/config.php` or `server/config.local.php` files. By default only `server/config.php` exists and contains default parameters. Parameters can be modified directly in this file but for easier upgrade, you can also overload just parameters you want to change in the file `server/config.local.php` (to create).

Parameters list:

* `pfc_timeout` [Integer:35]: time (in second) of inactivity to wait before considering a user is disconnected. A user is inactive only if s/he closed his/her chat window. A user with an open chat window is not inactive because s/he sends each refresh_delay an HTTP request.

Example in `server/config.php` or `server/config.local.php`:
```php
<?php

$GLOBALS['pfc_timeout'] = 50;
```

## Hooks (server side)

Hooks can be used to plug piece of code into the official phpfreechat code. Thanks to hooks, you can customize or extend phpfreechat's features.

### pfc.before.auth

This hook can be used to connect the chat authentication system to you own one. It is activated just before asking a login to the user. It can check for an user in a cookie, an external database or through a sso. The hook has to return the login as an attribut of the object in the first parameter (`$hr` in the following example). Here is a basic example:
```php
$GLOBALS['pfc_hooks']['pfc.before.auth'][5] = function ($app, $req, $res) {
  return function ($hr) use ($app, $req, $res) {
    $hr->login = 'guest'.rand(1,1000);
  };
};
```
This hook will randomly assign a nickname to each users (`[5]` is the hook priority cause it can have several hooks with one type)

A hook to connect phpbb3 authentication system to the chat (used on the [phpfreechat web site](http://www.phpfreechat.net)) can be found [at github here](https://github.com/kerphi/phpfreechat/tree/master/server/contrib/phpbb3-auth). 

## Developments

### Installation

```bash
git clone ...
make setup
```

### Makefile

* `make setup`: download and install Slim library for server side
* `make test`: run all tests client and server side
* `make test-client`: run tests client side
* `make test-server`: run tests server side
* `make bench`: simulate 50 users chatting (telling 1 sentence per 2 seconds) on 2 channels during 60 seconds
* `make jshint`: check client side syntaxe
* `make phpcs`: check client side syntaxe
* `make minify`: compile and minify css and js
* `make clean`: ...

* `make release`: create a tar.gz ready to download
* `make clean-release-for-dev`: used by `make release`
* `make clean-release-for-prod`: used by `make release`

* `make setup-client-test`: install dependencies needed for running client tests
* `make setup-server-test`: install dependencies needed for running server tests
* `make setup-client-test`: install dependencies needed for running client tests
* `make setup-jshint`: install dependencies needed for `make jshint`
* `make setup-minify`: install dependencies needed for `make minify`
* `make setup-bench`: install dependencies needed for `make bench`

### Playing with unit tests

Tests (client and server side) need phpfreechat's base absolute url. By default `http://127.0.0.1:32773` is used but it can be customized. For that, you have to create a `serverurl` file that contains the absolute url to your phpfreechat installation without the trailing slash  (ex: ``http://localhost/phpfreechat``)

Client side tests are written with [qunit](http://qunitjs.com/) and are located here: `client/tests/`

Server side tests are written with [vows](http://vowsjs.org/) and are located here: `server/tests/`


### Modules (client side)

* `jquery.phpfreechat.js`: just jquery plugin stuff
* `jquery.phpfreechat.init.js`: phpfreechat's initialization related function
* `jquery.phpfreechat.core.js`: phpfreechat's core related function
* `jquery.phpfreechat.auth.js`: phpfreechat's authentication related function
* `jquery.phpfreechat.utils.js`: phpfreechat's helpers

### Events (client side)

* `pfc-loaded` : triggered when pfc's interface is totaly loaded
* `pfc-login` : triggered when the user is logged
* `pfc-logout` : triggered when the user is loggouted

Example: TODO

### Routes design (server side)

* `/auth`                          (authentication)
* `/channels/`                     (list available channels)
* `/channels/:cid/name`            (channel little name)
* `/channels/:cid/users/`          (list users in the channel)
* `/channels/:cid/users/:uid`      (show a subscribed user to this channel)
* `/channels/:cid/msg/`            (used to post a new message on this channel)
* `/users/`                        (global users list)
* `/users/:uid/`                   (user info)
* `/users/:uid/msg/`               (messages received by the user: from a channel or a private message)
* `/users/:uid/closed`             (flag used to indicate when the user has closed his window)

### File container structure (server side)

Server stores data into the `server/data/` folder as following:

* `server/data/indexes/users/name/:name`      (index on user's nicknames: name -> uid)
* `server/data/users/:uid/index.json`         (full user data without messages and channels)
* `server/data/users/:uid/name`               (user name)
* `server/data/users/:uid/messages/:mid`      (pending messages for the user)
* `server/data/users/:uid/channels/:cid`      (channels joinded by the user)
* TODO

# phpFreeChat

phpFreeChat (pfc) is a Web based chat written in JQuery and PHP. Perfect if you need an easy to integrate chat for your Web site.

Features list:

* themable web interface (2 themes are available)
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


pfc architecture is splited in two distinct parts:

- client: a themable jquery plugin in charge of displaying the chat interface and to communicate with the server side using for example AJAX
- server: a [RESTful architecture](http://en.wikipedia.org/wiki/Representational_state_transfer) coded in PHP using the [Slim framework](http://www.slimframework.com/) in charge of the chat logic. It stores messages and send messages updates to the clients using classic HTTP methods (GET, POST, PUT, DELETE).

Here is an example of a basic communication between client and server:

* Client asks server to authenticate the user, server stores the user and returns a session id to the client.
* Client joins a channel, server stores that this user joined the channel and sends a "join" message to every connected users in this channel.
* Client sends a message into this channel, server publish this message into a queue for each connected users in this channel.
* Client read its pending messages, server read the user's queue and returns the messages list, client displays the messages on the interface.

## Prerequisites

  * Web browser compatible with JQuery (almost all !)
  * A server with:
    * php >= 5.3.0 ([Slim framework](https://github.com/codeguy/Slim/blob/master/README.markdown#system-requirements) dependency)
    * apache server with mod_rewrite and .htaccess enabled
    * write access to the phpfreechat-2.0.5/server/data/ folder (777 or write permission for the web server)
  * No database !

## Quick start installation

Download [phpfreechat-2.0.5.zip](http://www.phpfreechat.net/download) and unzip it in the root folder of your Web server.

Include the phpfreechat plugin in your html `<head>`:
```html
  <link rel="stylesheet" type="text/css" href="/phpfreechat-2.0.5/client/themes/default/jquery.phpfreechat.min.css" />
  <script src="/phpfreechat-2.0.5/client/jquery.phpfreechat.min.js" type="text/javascript"></script>
```

Add this piece of HTML in your `<body>` where you want the chat to be displayed:
```html
...
<div id="mychat"><a href="http://www.phpfreechat.net">phpFreeChat • Creating chat rooms everywhere</a></div>
...
```

The add this piece of code just after (it will hook the chat on the page):
```html
<script type="text/javascript">
  $('#mychat').phpfreechat({ serverUrl: '/phpfreechat-2.0.5/server' });
</script>
```

## Themes

phpfreechat is released with few themes. You can choose which one you want to use:

* `default`
* `carbon`
* `gamer`

To select the theme, you only have to change one line in your html `<head>`. To use the `default` theme:
```html
  <link rel="stylesheet" type="text/css" href="/phpfreechat-2.0.5/client/themes/default/jquery.phpfreechat.min.css" />
```
or this code for `carbon` theme:
```html
  <link rel="stylesheet" type="text/css" href="/phpfreechat-2.0.5/client/themes/carbon/jquery.phpfreechat.min.css" />
```

## Parameters client side

* `refresh_delay` [Integer:5000]: miliseconds to wait before next pending messages are checked
* `focus_on_connect` [Bool:true]: setting this to true will give the focus to the input text box when connecting to the chat. It can be useful not to touch the focus when integrating the chat into an existing website because when the focus is changed, the viewport follows the focus location.
* `serverUrl` [String:'../server']: where is located the pfc's server folder
* `loaded` [Function:null]: a callback executed when pfc's interface is totaly loaded
* `loadTestData` [Bool:false]: used for unit tests. It load test data into the interface.
* `use_post_wrapper` [Bool:true]: used to wrap PUT and DELETE http methods by special POST http methods (useful with a badly configured server)
* `check_server_config` [Bool:true]: when true, the first AJAX request is used to verify that server config is ok and display errors in a popup
* `tolerated_network_errors` [Integer:5]: number of tolerated network error before stoping chat refresh

Client side parameters can be given to phpfreechat client side jquery plugin as a javascript object.

Example:
```javascript
$('#mychat').phpfreechat({
  refresh_delay: 2000,
  focus_on_connect: false
});
```

## Parameters server side

Server side parameters are located in `server/config.php` or `server/config.local.php` files. By default only `server/config.php` exists and it contains default parameters. Parameters can be modified directly in this file but for easier upgrade, you can also overload just parameters you want to change in the file `server/config.local.php` (you have to create this file).

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

This hook can be used to connect the chat authentication system to you own one. It is activated just before asking a login to the user. It can check for an user in a cookie, an external database or through a sso. The hook has to return the login and it will be used by phpfreechat (if not already used by another user). Here is a basic example:
```php
$GLOBALS['pfc_hooks']['pfc.before.auth'][5] = function ($app, $req, $res) {
  return function ($hr) use ($app, $req, $res) {
    return 'guest'.rand(1,1000);
  };
};
```
This hook will randomly assign a nickname to each users (`[5]` is the hook priority cause it can have several hooks with one type)

A hook to connect phpbb3 authentication system to the chat (used on the [phpfreechat web site](http://www.phpfreechat.net)) can be found [at github here](https://github.com/kerphi/phpfreechat/tree/master/server/contrib/phpbb3-auth). 

### pfc.filter.login

This hook can be used to filter characters on the user's login in the auth route. First parameter is the login and the hook must return the filtered login. 

Example which filter none ascii characters from the login: 
```php
$GLOBALS['pfc_hooks']['pfc.filter.login'][0] = function ($app, $req, $res) {
  return function ($login) use ($app, $req, $res) {
    $ascii_pattern = '/^[a-z0-9()\/\'"|&,. -]{2,55}$/i';
    return preg_replace($ascii_pattern, '', $login);
  };
};
```
This hook will remove all none ascii chars from the given login. The code source can be found [at github here](https://github.com/kerphi/phpfreechat/tree/master/server/contrib/ascii-login). 

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

* `make version v=2.0.2`: patch files in source code and replace all version number by with 2.0.2
* `make untag`: remove the current (which is in package.json) version tag from github
* `make tag`: tag the source code with the version number found into package.json
* `make release`: create a tar.gz ready to download
* `make upload`: upload latest generated release on sourceforge
* `make clean-release-for-dev`: used by `make release`
* `make clean-release-for-prod`: used by `make release`

* `make setup-client-test`: install dependencies needed for running client tests
* `make setup-server-test`: install dependencies needed for running server tests
* `make setup-client-test`: install dependencies needed for running client tests
* `make setup-jshint`: install dependencies needed for `make jshint`
* `make setup-minify`: install dependencies needed for `make minify`
* `make setup-bench`: install dependencies needed for `make bench`

### How to release a version

* `make version v=x.x.x`
* `git commit -a -m "prepare version x.x.x"`
* `git push`
* `make tag`
* `make release`
* `make upload`

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

### Bench results archives

```
[Sat, 20 Oct 2012 21:05:31 GMT] [phpfreechat-2.0.0] Bench result: 20.13 (cpu=65% mem=55Mo dread=1k dwrite=17660k)
```
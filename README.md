Work in progress...

## Installation

Include phpfreechat plugin in your html `<head>`:

```html
  <link type="text/css" href="phpfreechat/client/themes/default/jquery.phpfreechat.min.css" />
  <script src="phpfreechat/client/jquery.phpfreechat.min.js" type="text/javascript"></script>
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

## Options (client side)

* `refresh_delay` [Integer:5000]: miliseconds to wait before next pending messages are checked
* `focus_on_connect` [Bool:true]: setting this to true will give the focus to the input text box when connecting to the chat. It can be useful not to touch the focus when integrating the chat into an existing website because when the focus is changed, the viewport follows the focus location.
* `serverUrl` [String:'../server']: where is located the pfc's server folder
* `loaded` [Function:null]: a callback executed when pfc's interface is totaly loaded
* `loadTestData` [Bool:false]]: used for interface unit tests

Example:
```javascript
$('#mychat').phpfreechat({
  refresh_delay: 2000
});
```

## Developments

### Installation

```bash
git clone ...
make setup
```

### Makefile

* `make setup`: download and install Slim library for server side
* `make test`: run all tests client and server side
* `make setup-client-test`: install dependencies needed for running client tests
* `make test-client`: run tests client side
* `make setup-server-test`: install dependencies needed for running server tests
* `make test-server`: run tests server side
* `make clean`: ...


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

### File container structure (server side)

* `/indexes/users/name/:name`      (index on user's nicknames: name -> uid)
* `/users/:uid/index.json`         (full user data without messages and channels)
* `/users/:uid/name`               (user name)
* `/users/:uid/messages/:mid`      (pending messages for the user)
* `/users/:uid/channels/:cid`      (channels joinded by the user)
* TODO

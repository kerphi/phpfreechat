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

```javascript
<script>
  $('#mychat').phpfreechat();
</script>
```

## Options (client side)

* `refresh_delay` [Integer:5000]: miliseconds to wait before next pending messages are checked
* `serverUrl` [String:'../server']: where is located the pfc's server folder
* `loaded` [Function:null]: a callback executed when pfc's interface is totaly loaded
* `loadTestData` [Bool:false]]: used for interface unit tests

Example:
```javascript
<script>
  $('#mychat').phpfreechat({
    refresh_delay: 2000
  });
</script>
```

## Modules (client side)

* `jquery.phpfreechat.js`: just jquery plugin stuff
* `jquery.phpfreechat.init.js`: phpfreechat's initialization related function
* `jquery.phpfreechat.core.js`: phpfreechat's core related function
* `jquery.phpfreechat.auth.js`: phpfreechat's authentication related function
* `jquery.phpfreechat.utils.js`: phpfreechat's helpers

## Events (client side)

* `pfc-loaded` : triggered when pfc's interface is totaly loaded
* `pfc-login` : triggered when the user is logged
* `pfc-logout` : triggered when the user is loggouted

Example: TODO

## Routes design (server side)

* `/auth`
* `/channels/`                     (list available channels)
* `/channels/:cid/name`            (channel little name)
* `/channels/:cid/users/`          (list users in the channel)
* `/channels/:cid/users/:uid`      (show a subscribed user to this channel)
* `/channels/:cid/msg/`            (used to post a new message on this channel)
* `/users/`                        (global users list)
* `/users/:uid/`                   (user info)
* `/users/:uid/msg/`               (messages received by the user: from a channel or a private message)

## File container structure (server side)

* TODO
* `/indexes/users/name/`           (index on user's nicknames: name -> uid)
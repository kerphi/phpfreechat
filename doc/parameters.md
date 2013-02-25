# Parameters

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
* `first_is_op` [Bool:true]: When this parameter is true, server gives channel operator rights to the first connected user.

Example in `server/config.php` or `server/config.local.php`:
```php
<?php

$GLOBALS['pfc_timeout']  = 50;
$GLOBALS['first_is_op']  = false;

```

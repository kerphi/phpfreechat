# Server side hooks

Hooks can be used to plug piece of code into the official phpfreechat code. Thanks to hooks, you can customize or extend phpfreechat's features. Hooks can be configured in your `server/config.php` or `server/config.local.php` files.

## pfc.before.auth

This hook can be used to connect the chat authentication system to you own one. It is activated just before asking a login to the user. It can check for an user in a cookie, an external database or through a sso. The hook has to return the login and it will be used by phpfreechat (if not already used by another user). Here is a basic example:
```php
$GLOBALS['pfc_hooks']['pfc.before.auth'][5] = function ($app, $req, $res) {
  return function () use ($app, $req, $res) {
    return 'guest'.rand(1,1000);
  };
};
```
This hook will randomly assign a nickname to each users (`[5]` is the hook priority cause it can have several hooks with one type)

A hook to connect phpbb3 authentication system to the chat (used on the [phpfreechat web site](http://www.phpfreechat.net)) can be found [at github here](https://github.com/kerphi/phpfreechat/tree/master/server/contrib/phpbb3-auth). 

## pfc.filter.login

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

## pfc.isop

This hook can be used to give operator right to a specific login on a specific channel.

* First parameter is the login 
* Second parameter is the channel name

The hook must return true if operator, false if not an operator on this channel. 

Example which give op rights to "kerphi" login when he joins "Kerphi's room" channel: 
```php
$GLOBALS['pfc_hooks']['pfc.isop'][5] = function ($app, $req, $res) {
  return function ($login, $channel, $uid, $cid) use ($app, $req, $res) {
    if ($login == 'kerphi' and $channel == "Kerphi's room") {
      return true;
    } else {
      return false;
    }
  };
};
```

This hook will systematically give channel operator rights to "kerphi" login when he joins "Kerphi's room" channel.

Available in phpFreeChat ≥ 2.1.0

## pfc.isban

This hook can be used to banish a specific user on a specific channel.

* First parameter is the login 
* Second parameter is the channel name

The hook must return an array with baninfo (see bellow example) if the user is banned, false if the user is not banned on this channel. 

Example which ban "baduser" login on the "Kerphi's room" channel: 
```php
$GLOBALS['pfc_hooks']['pfc.isban'][5] = function ($app, $req, $res) {
  return function ($login, $channel, $uid, $cid) use ($app, $req, $res) {
    if ($login == 'baduser' and $channel == "Kerphi's room") {
      return array('opname' => 'Chat Master', 'reason' => 'Because you are a spammer', 'timestamp' => time());
    } else {
      return false;
    }
  };
};
```

Available in phpFreeChat ≥ 2.1.0
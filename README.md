# Installation quickstart

[![Build Status](https://travis-ci.org/kerphi/phpfreechat.png?branch=master)](https://travis-ci.org/kerphi/phpfreechat)

## Prerequisites

  * Web browser compatible with JQuery (almost all !)
  * A server with:
    * php >= 5.3.0 ([Slim framework](https://github.com/codeguy/Slim/blob/master/README.markdown#system-requirements) dependency)
    * apache server with mod_rewrite and .htaccess enabled (AllowOverride All)
    * write access to the phpfreechat-2.1.1/server/data/ and phpfreechat-2.1.1/server/log/ folder (777 or write permission for the web server)
  * No database needed !

## Quick start

Download [phpfreechat-2.1.1.zip](http://www.phpfreechat.net/download) and unzip it in the root folder of your Web server.

JQuery should be included in your html `<head>` before the phpfreechat code:
```html
  <script src="/phpfreechat-2.1.1/client/lib/jquery-1.8.2.min.js" type="text/javascript"></script>
```

Include the phpfreechat plugin in your html `<head>`:
```html
  <link rel="stylesheet" type="text/css" href="/phpfreechat-2.1.1/client/themes/default/pfc.min.css" />
  <script src="/phpfreechat-2.1.1/client/pfc.min.js" type="text/javascript"></script>
```

Add this piece of HTML in your `<body>` where you want the chat to be displayed:
```html
...
<div id="mychat"><a href="http://www.phpfreechat.net">Creating chat rooms everywhere - phpFreeChat</a></div>
...
```

Then add this piece of code just after (it will hook the chat on the page):
```html
<script type="text/javascript">
  $('#mychat').phpfreechat({ serverUrl: '/phpfreechat-2.1.1/server' });
</script>
```

## Themes customization

phpfreechat is released with few themes. You can choose which one you want to use:

* `default`
* `carbon`
* `gamer`
* `phpfreechat`
* `phpfreechat-mini`

To select the theme, you only have to change one line in your html `<head>`. To use the `default` theme:
```html
  <link rel="stylesheet" type="text/css" href="/phpfreechat-2.1.1/client/themes/default/pfc.min.css" />
```
or this code for `carbon` theme:
```html
  <link rel="stylesheet" type="text/css" href="/phpfreechat-2.1.1/client/themes/carbon/pfc.min.css" />
```

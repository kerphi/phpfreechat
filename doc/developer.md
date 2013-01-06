# Developer guide

## Installation

```bash
git clone ...
make setup
```

## Makefile

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

## How to release a version

* `make version v=x.x.x`
* `git commit -a -m "prepare version x.x.x"`
* `git push`
* `make tag`
* `make release`
* `make upload`

## Playing with unit tests

Tests (client and server side) need phpfreechat's base absolute url. By default `http://127.0.0.1:32773` is used but it can be customized. For that, you have to create a `serverurl` file that contains the absolute url to your phpfreechat installation without the trailing slash  (ex: ``http://localhost/phpfreechat``)

Client side tests are written with [qunit](http://qunitjs.com/) and are located here: `client/tests/`

Server side tests are written with [vows](http://vowsjs.org/) and are located here: `server/tests/`


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

* `server/auth`                          (authentication)
* `server/channels/`                     (list available channels)
* `server/channels/:cid/name`            (channel little name)
* `server/channels/:cid/users/`          (list users in the channel)
* `server/channels/:cid/users/:uid`      (show a subscribed user to this channel)
* `server/channels/:cid/msg/`            (used to post a new message on this channel)
* `server/users/`                        (global users list)
* `server/users/:uid/`                   (user info)
* `server/users/:uid/msg/`               (messages received by the user: from a channel or a private message)
* `server/users/:uid/closed`             (flag used to indicate when the user has closed his window)
* `server/skipintro`                     (flag used to indicate if the intro message about donation should be displayed or not)
* `server/status`                        (returns json structure which indicates server status)
* `/check.php`                           (check php version and other needed server dependancies)

Warning: work in progress, routes structure can change.

## File container structure (server side)

Server stores data into the `server/data/` folder as following:

* `server/data/users/:uid/index.json`         (full user data without messages and channels)
* `server/data/users/:uid/name`               (user name)
* `server/data/users/:uid/messages/:mid`      (pending messages for the user)
* `server/data/users/:uid/channels/:cid`      (channels joinded by the user)
* `server/data/channels/:cid/users/:uid       (:uid is online on :cid)
* `server/data/channels/:cid/index.json`      (full channel attributes)
* `server/data/channels/:cid/op               (operators list)
* `server/data/indexes/users/name/:name`      (index on user's nicknames: name -> uid)
* `server/data/skipintro`                     (contains 0 or 1)
* `server/data/gc`                            (timestamp used for garbage collector)

Warning: work in progress, folder structure can change.

## Bench results archives

```
[Sat, 20 Oct 2012 21:05:31 GMT] [phpfreechat-2.0.0] Bench result: 20.13 (cpu=65% mem=55Mo dread=1k dwrite=17660k)
```
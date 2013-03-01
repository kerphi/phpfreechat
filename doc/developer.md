# Developer guide

## Installation

```bash
git clone https://github.com/kerphi/phpfreechat.git
make setup
```

If you are using Windows, you should download manualy [https://github.com/codeguy/Slim/archive/2.1.0.zip](Slim Framework)
and unzip it into server/lib/Slim/ folder (rename Slim-x.x.x to Slim).

If you plan to contribute, it's better to fork the git repository then to commit your code in your fork and to submit
pull request to the phpfreechat github repository.

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

* `pfc.js`: just jquery plugin stuff
* `pfc-init.js`: phpfreechat's initialization related function
* `pfc-core.js`: phpfreechat's core related function
* `pfc-auth.js`: phpfreechat's authentication related function
* `pfc-utils.js`: phpfreechat's helpers
* ...

## Events (client side)

* `pfc-loaded` : triggered when pfc's interface is totaly loaded
* `pfc-login` : triggered when the user is logged
* `pfc-logout` : triggered when the user is loggouted

Example: TODO

## Routes design (server side)

* `server/auth`                          - GET    - returns authentication informations
* `server/auth`                          - DELETE - logout
* `server/channels/`                     - GET    - list available channels [not implemented]
* `server/channels/:cid/`                - GET    - returns :cid channel merged infos [not implemented]
* `server/channels/:cid/name`            - GET    - channel little name [not implemented]
* `server/channels/:cid/users/`          - GET    - list users in the :cid channel
* `server/channels/:cid/users/:uid`      - GET    - tells if :uid user is subscribed to this :cid channel
* `server/channels/:cid/users/:uid`      - PUT    - :uid user joins :cid channel (try to)
* `server/channels/:cid/users/:uid`      - DELETE - :uid user leave himself or with a kick :cid channel (try to)
* `server/channels/:cid/msg/`            - POST   - current user post a new message on :cid channel (if joined)
* `server/channels/:cid/op/`             - GET    - returns the :cid channel operators list (list of :uid)
* `server/channels/:cid/op/:uid`         - GET    - tells if :uid is operator on :cid
* `server/channels/:cid/op/:uid`         - PUT    - adds :uid to the operator list on :cid channel (try to)
* `server/channels/:cid/op/:uid`         - DELETE - removes operator rights to :uid on :cid channel (try to)
* `server/channels/:cid/ban/`            - GET    - returns the :cid channel banished list (list of :name)
* `server/channels/:cid/ban/:name`       - PUT    - adds :name to the banished list on :cid channel (:name is base64 encoded)
* `server/channels/:cid/ban/:name`       - DELETE - :name is no more banished on :cid channel (:name is base64 encoded)
* `server/users/`                        - GET    - returns the online users :uid currently online on the server [not implemented]
* `server/users/:uid/`                   - GET    - returns :uid user info
* `server/users/:uid/pending/`           - GET    - pending messages for :uid user (from channels or a private messages)
* `server/users/:uid/closed`             - PUT    - set the :uid user window closed flag to true (used to disconnect the user earlier)
* `server/skipintro`                     - GET    - flag used to indicate if the intro message about donation should be displayed or not
* `server/skipintro`                     - PUT    - tells to not display again the intro message (set the flag to true)
* `server/status`                        - GET    - returns json structure indicating server status
* `check.php`                            - GET    - check php version and other needed server dependencies

Warning: work in progress, routes structure can change in future.

## File container structure (server side)

Server stores data into the `server/data/` folder as following:

* `server/data/users/:uid/index.json`         - full user data without messages and channels
* `server/data/users/:uid/name`               - user name
* `server/data/users/:uid/messages/:mid`      - pending messages for the user
* `server/data/users/:uid/channels/:cid`      - channels joinded by the user
* `server/data/channels/:cid/users/           - users online on :cid channel
* `server/data/channels/:cid/users/:uid       - tells that :uid is online on :cid
* `server/data/channels/:cid/index.json`      - full channel attributes
* `server/data/channels/:cid/op/              - operators list
* `server/data/channels/:cid/op/:uid          - tells that :uid is operator on :cid
* `server/data/channels/:cid/ban/             - banished list
* `server/data/channels/:cid/ban/:name        - tells that :name is banished on :cid
* `server/data/indexes/users/name/:name`      - index on user's nicknames: name -> uid
* `server/data/skipintro`                     - contains 0 or 1
* `server/data/gc`                            - timestamp used for garbage collector

Warning: work in progress, folder structure can change.

## Bench results archives

```
[Sat, 20 Oct 2012 21:05:31 GMT] [phpfreechat-2.0.0] Bench result: 20.13 (cpu=65% mem=55Mo dread=1k dwrite=17660k)
```
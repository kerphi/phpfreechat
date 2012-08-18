path=$(shell pwd)
SERVERURL=`cat serverurl 2>/dev/null | echo "http://127.0.0.1:32773"`
TESTS=$(wildcard $(path)/server/tests/*.js)

dummy:

# run all tests
test: test-server test-client

# run server tests
test-server: dummy
	@touch $(path)/server/config.local.php
	@mv -f $(path)/server/config.local.php $(path)/server/config.local.php.tmp
	@cp -f $(path)/server/tests/config.local.php $(path)/server/config.local.php
	@rm -rf server/data/*
	@vows $(TESTS) --spec
	@mv -f $(path)/server/config.local.php.tmp $(path)/server/config.local.php

# run client tests
test-client: dummy
	@./phantomjs/bin/phantomjs ./phantomjs/examples/run-qunit.js $(SERVERURL)/client/tests/test1.html

setup: dummy
	@cd $(path)/server/lib/ && curl -L https://nodeload.github.com/codeguy/Slim/tarball/1.6.5 > slim.tar.gz && pwd && tar -ztf slim.tar.gz 2>/dev/null | head -1 > /tmp/slimname
	@cd $(path)/server/lib/ && tar xzf slim.tar.gz
	@rm -rf $(path)/server/lib/Slim && mv $(path)/server/lib/`cat /tmp/slimname` $(path)/server/lib/Slim
	@rm -f /tmp/slimname && rm -f $(path)/server/lib/slim.tar.gz

# install needed packages for tests run
setup-server-test:
	@cd $(path)/server/tests && npm install vows request async && npm install -g vows

setup-client-test:
	@cd $(path) && wget http://phantomjs.googlecode.com/files/phantomjs-1.6.1-linux-x86_64-dynamic.tar.bz2
	@tar xzf phantomjs-1.6.1-linux-x86_64-dynamic.tar.bz2

setup-minify:
	@npm install -g less clean-css pack

# compress javascript and css
minify: $(path)/client/jquery.phpfreechat.js $(path)/client/themes/default/jquery.phpfreechat.less
	@cat $(path)/client/jquery.phpfreechat.js $(path)/client/jquery.phpfreechat.*.js | packnode > $(path)/client/jquery.phpfreechat.min.js
	@lessc $(path)/client/themes/default/jquery.phpfreechat.less $(path)/client/themes/default/jquery.phpfreechat.css
	@cleancss $(path)/client/themes/default/jquery.phpfreechat.css > $(path)/client/themes/default/jquery.phpfreechat.min.css 

setup-jshint:
	@npm install -g jshint

jshint: clean
	@jshint $(wildcard $(path)/client/*.js) --config client/config-jshint.json
	@jshint $(wildcard $(path)/server/tests/*.js) --config server/config-jshint.json

clean: dummy
	@rm -f $(path)/client/*.min.js
	@rm -f $(path)/client/themes/default/jquery.phpfreechat.css
	@rm -f $(path)/client/themes/default/jquery.phpfreechat.min.css

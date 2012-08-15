path = $(shell pwd)
TESTS=$(wildcard $(path)/server/tests/*.js)
TESTSSLIM=$(wildcard $(path)/server-slim/tests/*.js)

dummy:

# run all tests
test: test-server test-client

# run server tests
test-server: setup-server-test-config
	@rm -rf server/data/*
	@vows $(TESTS) --spec

# run server-slim tests
test-slim: setup-server-test-config
	@rm -rf server-slim/data/*
	@vows $(TESTSSLIM) --spec

# run client tests
test-client: dummy
	@./phantomjs/bin/phantomjs ./phantomjs/examples/run-qunit.js http://127.0.0.1:32773/phpfreechat/client/tests/test1.html

# install needed packages for tests run
setup-server-test:
	@cd $(path)/server/tests && npm install vows request async && npm install -g vows

setup-server-test-config:
	@cp -f $(path)/server-slim/tests/config.local.php $(path)/server-slim/config.local.php

setup-default-config:
	@rm -f $(path)/server-slim/config.local.php

setup-client-test:
	@cd $(path) && wget http://phantomjs.googlecode.com/files/phantomjs-1.5.0-linux-x86-dynamic.tar.gz
	@tar xzf phantomjs-1.5.0-linux-x86-dynamic.tar.gz

setup-minify:
	@npm install -g less clean-css pack

# compress javascript and css
minify: $(path)/client/jquery.phpfreechat.js $(path)/client/themes/default/jquery.phpfreechat.less
	@cat $(path)/client/jquery.phpfreechat.js $(path)/client/jquery.phpfreechat.*.js | packnode > $(path)/client/jquery.phpfreechat.min.js
	@lessc $(path)/client/themes/default/jquery.phpfreechat.less $(path)/client/themes/default/jquery.phpfreechat.css
	@cleancss $(path)/client/themes/default/jquery.phpfreechat.css > $(path)/client/themes/default/jquery.phpfreechat.min.css 

clean: setup-default-config
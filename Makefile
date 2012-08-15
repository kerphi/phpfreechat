path = $(shell pwd)
TESTS=$(wildcard $(path)/server/tests/*.js)
TESTSSLIM=$(wildcard $(path)/server-slim/tests/*.js)

dummy:

# run all tests
test: test-slim test-client

# run server-slim tests
test-slim: dummy
	@touch $(path)/server-slim/config.local.php
	@mv -f $(path)/server-slim/config.local.php $(path)/server-slim/config.local.php.tmp
	@cp -f $(path)/server-slim/tests/config.local.php $(path)/server-slim/config.local.php
	@rm -rf server-slim/data/*
	@vows $(TESTSSLIM) --spec
	@mv -f $(path)/server-slim/config.local.php.tmp $(path)/server-slim/config.local.php

# run client tests
test-client: dummy
	@./phantomjs/bin/phantomjs ./phantomjs/examples/run-qunit.js http://127.0.0.1:32773/phpfreechat/client/tests/test1.html

setup-server: dummy
	@cd $(path)/server-slim/lib/ && curl -L https://nodeload.github.com/codeguy/Slim/tarball/1.6.5 > slim.tar.gz && pwd && tar -ztf slim.tar.gz 2>/dev/null | head -1 > /tmp/slimname
	@cd $(path)/server-slim/lib/ && tar xzf slim.tar.gz
	@rm -rf $(path)/server-slim/lib/Slim && mv $(path)/server-slim/lib/`cat /tmp/slimname` $(path)/server-slim/lib/Slim
	@rm -f /tmp/slimname && rm -f $(path)/server-slim/lib/slim.tar.gz

# install needed packages for tests run
setup-server-test:
	@cd $(path)/server/tests && npm install vows request async && npm install -g vows

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
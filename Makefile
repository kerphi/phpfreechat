path=$(shell pwd)
SERVERURL=`cat serverurl 2>/dev/null | echo "http://127.0.0.1:32773"`
VERSION=`$(path)/tools/get-version`
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
	@cd $(path)/server/lib/ && curl -L https://nodeload.github.com/codeguy/Slim/tarball/2.1.0 > slim.tar.gz && pwd && tar -ztf slim.tar.gz 2>/dev/null | head -1 > /tmp/slimname
	@cd $(path)/server/lib/ && tar xzf slim.tar.gz
	@rm -rf $(path)/server/lib/Slim && mv $(path)/server/lib/`cat /tmp/slimname` $(path)/server/lib/Slim
	@rm -rf $(path)/server/lib/Slim/tests ; rm -rf $(path)/server/lib/Slim/docs
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
minify: $(path)/client/jquery.phpfreechat.js $(path)/client/jquery.phpfreechat.*.js $(path)/client/themes/*/jquery.phpfreechat.less $(path)/client/themes/*/jquery.phpfreechat.*.less
	$(shell cat $(path)/client/jquery.phpfreechat.js $(path)/client/jquery.phpfreechat.*.js | packnode > $(path)/client/jquery.phpfreechat.min.js)
	$(shell for f in `ls client/themes/*/jquery.phpfreechat.less`; do lessc $$f `echo $$f | sed s/.less/.css/g`; done)
	$(shell for f in `ls client/themes/*/jquery.phpfreechat.css`; do cleancss $$f > `echo $$f | sed s/.css/.min.css/g`; done)

setup-jshint:
	@npm install -g jshint

jshint:
	@jshint $(wildcard $(path)/client/*.js) $(wildcard $(path)/server/tests/*.js)

phpcs:
	@phpcs --standard=Zend --tab-width=2  --encoding=utf-8 --sniffs=Generic.Functions.FunctionCallArgumentSpacing,Generic.Functions.OpeningFunctionBraceBsdAllmann,Generic.PHP.DisallowShortOpenTag,Generic.WhiteSpace.DisallowTabIndent,PEAR.ControlStructures.ControlSignature,PEAR.Functions.ValidDefaultValue,PEAR.WhiteSpace.ScopeClosingBrace,Generic.Files.LineEndings -s $(wildcard $(path)/server/*.php) $(wildcard $(path)/server/routes/*.php) $(wildcard $(path)/server/container/*.php)

clean: dummy
	@rm -f $(path)/client/*.min.js
	@rm -f $(path)/client/themes/*/jquery.phpfreechat.css
	@rm -f $(path)/client/themes/*/jquery.phpfreechat.min.css
	@rm -rf $(path)/server/data/*
	@rm -f $(path)/server/logs/*

clean-release: setup setup-minify minify
	@rm -rf $(path)/client/tests
	@rm -rf $(path)/server/tests
	@rm -rf $(path)/server/data/*
	@rm -f $(path)/server/logs/*
	@rm -f $(path)/Makefile
	@rm -f $(path)/.jshintrc
	@rm -f $(path)/.jshintignore
	@rm -rf $(path)/.git

# do not minify .js/.css (and remove .less)
clean-release-for-debug: clean-release
	@rm -f $(path)/client/*.min.js
	@cat $(path)/client/*.js > $(path)/client/jquery.phpfreechat.js.tmp
	@rm -f $(path)/client/*.js
	@mv $(path)/client/jquery.phpfreechat.js.tmp $(path)/client/jquery.phpfreechat.js
	@rm -f $(path)/client/lib/less-*.js
	@rm -f $(path)/client/themes/*/*.less
	@rm -f $(path)/client/themes/*/jquery.phpfreechat.min.css
	@tools/switch-examples-head --dev
	@rm -rf $(path)/tools

# remove .less, minify .css and .js
clean-release-for-prod: clean-release
	@mv $(path)/client/jquery.phpfreechat.min.js $(path)/client/jquery.phpfreechat.min.js.tmp
	@rm -f $(path)/client/*.js
	@mv $(path)/client/jquery.phpfreechat.min.js.tmp $(path)/client/jquery.phpfreechat.min.js
	@rm -f $(path)/client/lib/less-*.js
	@rm -f $(path)/client/themes/*/*.less
	@rm -f $(path)/client/themes/*/jquery.phpfreechat.css
	@tools/switch-examples-head --prod
	@rm -rf $(path)/tools

# keep less and separated js
clean-release-for-dev: clean-release clean
	@tools/switch-examples-head --debug
	@rm -rf $(path)/tools

version: dummy
	@npm install optimist
	@tools/patch-version-number.js --version $(v)

tag: dummy
	@tools/tag-release

untag: dummy
	@tools/untag-release

release: dummy
	@tools/build-release --prod
	@tools/build-release --dev
	@tools/build-release --debug

upload: dummy
	$(shell cd /tmp/; mkdir -p $(VERSION) ; scp -r $(VERSION)/ kerphi@frs.sourceforge.net:"/home/frs/project/phpfreechat/branch\\ 2.x/")
	$(shell scp    $(path)/$(VERSION)/phpfreechat-$(VERSION).zip kerphi@frs.sourceforge.net:"/home/frs/project/phpfreechat/branch\\ 2.x/")
	$(shell scp -r $(path)/$(VERSION)/ kerphi@frs.sourceforge.net:"/home/frs/project/phpfreechat/branch\\ 2.x/")
	$(shell sleep 5)
	$(shell scp $(path)/$(VERSION)/phpfreechat-$(VERSION).zip kerphi@frs.sourceforge.net:"/home/frs/project/phpfreechat/branch\\ 2.x/$(VERSION)/")

setup-bench: dummy
	@npm install shelljs
	@cd $(path)/server/tests/bench && npm install Faker

simulate-user-session: dummy
	@vows $(path)/server/tests/bench/user-session.js

bench: dummy
	@touch $(path)/server/config.local.php
	@mv -f $(path)/server/config.local.php $(path)/server/config.local.php.tmp
	@rm -rf server/data/*
	@node tools/run-bench.js
	@mv -f $(path)/server/config.local.php.tmp $(path)/server/config.local.php

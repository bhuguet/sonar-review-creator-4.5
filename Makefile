.PHONY: prepare test

prepare:
	curl -s http://getcomposer.org/installer | php
	php composer.phar install

test:
	tools/composer/bin/phpunit -c test/phpunit/phpunit.xml test/phpunit/
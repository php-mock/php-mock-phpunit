# @configure_input@

doc: composer-update
	./vendor/bin/apigen generate

composer-clean:
	rm -rf vendor/php-mock/php-mock-phpunit/ composer.lock

composer-update: composer-clean
	composer.phar update

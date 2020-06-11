install:
	composer install

test:
	composer run-script phpunit -- --colors=always tests

lint:
	composer run-script phpcs -- --standard=PSR12 src tests

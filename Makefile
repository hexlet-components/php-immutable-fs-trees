install:
	composer install

test:
	composer run-script phpunit tests

lint:
	composer run-script phpcs -- --standard=PSR12 src tests

vendor: composer.json
	@symfony composer install --no-interaction

cs:
	# Running PHP-CS-Fixer
	PHP_CS_FIXER_IGNORE_ENV=1 symfony php vendor/bin/php-cs-fixer fix

analyse:
	# Running PHPStan static code analyse
	@symfony php vendor/bin/phpstan analyse
	
test-coverage:
	@symfony php vendor/bin/phpunit -v --coverage-clover clover-coverage.xml --coverage-html coverage_html --log-junit coverage_html/junit.xml

all: cs test-coverage analyse

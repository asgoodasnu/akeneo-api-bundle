vendor: composer.json
	@symfony composer install --no-interaction

cs:
	# Running PHP-CS-Fixer
	@symfony php vendor/bin/php-cs-fixer fix

analyse:
	# Running PHPStan static code analyse
	@symfony php vendor/bin/phpstan analyse src/ tests/ --level 7
	
test-coverage:
	@symfony php vendor/bin/simple-phpunit -v --coverage-clover clover-coverage.xml --coverage-html coverage_html --log-junit coverage_html/junit.xml

all: cs analyse test-coverage

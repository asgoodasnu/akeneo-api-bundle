# vim: set tabstop=8 softtabstop=8 noexpandtab:

DATE := $(shell date +%Y-%m-%d_%H-%M-%S)

vendor: composer.json composer.lock
	@composer install --no-interaction

cs:
	# Running PHP-CS-Fixer
	@php vendor/bin/php-cs-fixer fix

analyse:
	# Running PHPStan static code analyse
	@php vendor/bin/phpstan analyse src/ tests/ --level 7

lint-twig:
	# Linting Twig files
	@symfony php bin/console lint:twig templates/

lint-yaml:
	# Linting YAML files
	@symfony php bin/console lint:yaml config/ src/ translations/ --parse-tags

test-coverage:
	@php vendor/bin/simple-phpunit -v --coverage-clover clover-coverage.xml --coverage-html coverage_html --log-junit coverage_html/junit.xml

all: cs analyse test-coverage

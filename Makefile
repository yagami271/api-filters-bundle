dc_exec = docker compose exec php

.DEFAULT_GOAL := help

.PHONY: help install update test phpstan cs cs-fix check start stop bash test-sqlite test-mysql test-pgsql test-mariadb test-all

help: ## Show this help
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "  \033[36m%-10s\033[0m %s\n", $$1, $$2}'

install: ## Build image, start container and install dependencies
	docker compose build
	docker compose up -d
	$(dc_exec) composer install

update: ## Update composer dependencies
	$(dc_exec) composer update

test: ## Run tests (use ARGS="--filter method" to filter)
	$(dc_exec) vendor/bin/phpunit $(ARGS)

phpstan: ## Run PHPStan static analysis
	$(dc_exec) vendor/bin/phpstan analyse

cs: ## Check code style (dry-run)
	$(dc_exec) vendor/bin/php-cs-fixer fix --dry-run --diff

cs-fix: ## Auto-fix code style
	$(dc_exec) vendor/bin/php-cs-fixer fix

check: test phpstan cs ## Run all checks (tests + phpstan + cs)

test-sqlite: ## Run tests against SQLite
	docker compose exec -e DATABASE_DRIVER=sqlite php vendor/bin/phpunit $(ARGS)

test-mysql: ## Run tests against MySQL
	docker compose exec -e DATABASE_DRIVER=mysql -e DATABASE_HOST=mysql -e DATABASE_PORT=3306 -e DATABASE_USER=root -e DATABASE_PASSWORD=root -e DATABASE_NAME=api_filters_test php vendor/bin/phpunit $(ARGS)

test-pgsql: ## Run tests against PostgreSQL
	docker compose exec -e DATABASE_DRIVER=pgsql -e DATABASE_HOST=postgres -e DATABASE_PORT=5432 -e DATABASE_USER=root -e DATABASE_PASSWORD=root -e DATABASE_NAME=api_filters_test php vendor/bin/phpunit $(ARGS)

test-mariadb: ## Run tests against MariaDB
	docker compose exec -e DATABASE_DRIVER=mariadb -e DATABASE_HOST=mariadb -e DATABASE_PORT=3306 -e DATABASE_USER=root -e DATABASE_PASSWORD=root -e DATABASE_NAME=api_filters_test php vendor/bin/phpunit $(ARGS)

test-all: test-sqlite test-mysql test-pgsql test-mariadb ## Run tests against all databases

start: ## Start the container
	docker compose up -d

stop: ## Stop the container
	docker compose down

bash: ## Open a shell in the container
	docker compose exec php sh

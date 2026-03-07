dc_exec = docker compose exec php

.DEFAULT_GOAL := help

.PHONY: help install update test phpstan cs cs-fix check start stop bash

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

start: ## Start the container
	docker compose up -d

stop: ## Stop the container
	docker compose down

bash: ## Open a shell in the container
	docker compose exec php sh

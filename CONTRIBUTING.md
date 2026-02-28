# Contributing

Thank you for considering contributing to API Filters Bundle!

## Development Setup

1. Clone the repository
2. Install dependencies:
   ```bash
   cd bundle
   composer install
   ```

## Coding Standards

- Follow PSR-12 coding standards
- Use `declare(strict_types=1)` in all PHP files
- All public services must have an interface

## Running Tests

```bash
vendor/bin/phpunit
```

## Static Analysis

```bash
vendor/bin/phpstan analyse
```

## Code Style

Check:
```bash
vendor/bin/php-cs-fixer fix --dry-run --diff -v
```

Fix:
```bash
vendor/bin/php-cs-fixer fix -v
```

## Pull Requests

1. Create a feature branch from `main`
2. Write tests for new functionality
3. Ensure all tests pass and linters report no issues
4. Submit a pull request with a clear description

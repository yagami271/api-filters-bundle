# API Filters Bundle

A Symfony bundle for API filtering.

## Requirements

- PHP >= 8.2
- Symfony >= 7.4

## Installation

```bash
composer require isma/api-filters-bundle
```

If you're using Symfony Flex, the bundle is automatically registered. Otherwise, add it to `config/bundles.php`:

```php
return [
    // ...
    Isma\ApiFiltersBundle\ApiFiltersBundle::class => ['all' => true],
];
```

## Configuration

Add the bundle configuration in `config/packages/api_filters.yaml`:

```yaml
api_filters:
    # Configuration options will be documented here
```

## Usage

Inject the `FilterInterface` in your services:

```php
use Isma\ApiFiltersBundle\Filter\FilterInterface;

class MyService
{
    public function __construct(
        private FilterInterface $filter,
    ) {
    }
}
```

## Development

```bash
# Install dependencies
composer install

# Run tests
vendor/bin/phpunit

# Static analysis
vendor/bin/phpstan analyse

# Code style check
vendor/bin/php-cs-fixer fix --dry-run --diff -v

# Code style fix
vendor/bin/php-cs-fixer fix -v
```

## License

MIT

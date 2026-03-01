# Contributing

Thank you for considering contributing to API Filters Bundle! This guide will help you get started.

## Development setup

### Prerequisites

- PHP >= 8.2
- Composer

### Getting started

```bash
# Clone the repository
git clone <repository-url>
cd api-filters-bundle

# Install dependencies
composer install
```

## Running checks

```bash
# Run tests
vendor/bin/phpunit

# Run a single test file
vendor/bin/phpunit tests/Integration/Filter/ORM/OrmFilterIntegrationTest.php

# Run a single test method
vendor/bin/phpunit --filter itAppliesEqFilter

# Run PHPStan (level max on src/)
vendor/bin/phpstan analyse

# Check code style (dry-run)
vendor/bin/php-cs-fixer fix --dry-run --diff

# Auto-fix code style
vendor/bin/php-cs-fixer fix
```

## Coding standards

- **PHP 8.2+** with `declare(strict_types=1)` in every file
- **Symfony coding standard** (`@Symfony` ruleset) with trailing commas on multiline arguments/parameters
- **PHPStan level max** on `src/`
- Run `bundle-cs-fix` before committing to auto-format your code

## Writing tests

- Use the `#[Test]` attribute, not the `test` method name prefix
- Test classes must be `final`
- Place unit tests in `tests/Unit/` and integration tests in `tests/Integration/`
- Integration tests can use the `TestKernel` located at `tests/Integration/TestKernel.php`

Example:

```php
<?php

declare(strict_types=1);

namespace Isma\ApiFiltersBundle\Tests\Unit\ValueObject;

use Isma\ApiFiltersBundle\ValueObject\Filter;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class FilterTest extends TestCase
{
    #[Test]
    public function itDetectsMultiValue(): void
    {
        $filter = new Filter('field', 'eq', ['a', 'b']);

        $this->assertTrue($filter->isMultiValue());
    }
}
```

## Adding a new filter strategy

1. Create a class implementing `FilterStrategyInterface` in `src/Filter/ORM/Strategy/`
2. Return a unique type string from `getType()` (e.g., `gt`, `gte`, `between`)
3. Implement `apply()` to modify the Doctrine `QueryBuilder`
4. The bundle auto-discovers your strategy - no service configuration needed
5. Write integration tests in `tests/Integration/Filter/ORM/`

```php
<?php

declare(strict_types=1);

namespace Isma\ApiFiltersBundle\Filter\ORM\Strategy;

use Doctrine\ORM\QueryBuilder;
use Isma\ApiFiltersBundle\Filter\FilterStrategyInterface;

final class GteFilterStrategy implements FilterStrategyInterface
{
    public function getType(): string
    {
        return 'gte';
    }

    public function apply(object $queryBuilder, string $column, mixed $value, string $parameterName): void
    {
        \assert($queryBuilder instanceof QueryBuilder);

        $queryBuilder->andWhere(\sprintf('%s >= :%s', $column, $parameterName))
            ->setParameter($parameterName, $value);
    }
}
```

Don't forget to add the new type to the `FilterType` enum if it should be a first-class type:

```php
// src/ValueObject/FilterType.php
enum FilterType: string
{
    case Eq = 'eq';
    case Neq = 'neq';
    case Like = 'like';
    case Gte = 'gte'; // new
}
```

## Project structure

```
src/
├── ApiFiltersBundle.php              # Bundle entry point
├── Attribute/
│   └── ApiFilter.php                 # #[ApiFilter] attribute
├── Exception/
│   ├── InvalidFilterException.php    # 400 Bad Request
│   └── DuplicateFilterStrategyException.php
├── Filter/
│   ├── FilterApplierInterface.php    # Generic applier interface
│   ├── FilterStrategyInterface.php   # Generic strategy interface
│   └── ORM/
│       ├── OrmFilterApplier.php      # Doctrine ORM applier
│       └── Strategy/
│           ├── EqFilterStrategy.php
│           ├── NeqFilterStrategy.php
│           └── LikeFilterStrategy.php
├── Resolver/
│   └── FiltersValueResolver.php      # HTTP request -> Filters VO
└── ValueObject/
    ├── Filter.php                    # Single filter
    ├── Filters.php                   # Filter collection
    └── FilterType.php                # Filter type enum

tests/
├── Unit/
│   └── ValueObject/
│       ├── FilterTest.php
│       └── FiltersTest.php
└── Integration/
    ├── TestKernel.php
    ├── Resolver/
    │   └── FiltersValueResolverIntegrationTest.php
    └── Filter/ORM/
        └── OrmFilterIntegrationTest.php
```

## Pull requests

1. Create a feature branch from `main`
2. Write tests for any new functionality
3. Make sure all checks pass:
   ```bash
   vendor/bin/phpunit && vendor/bin/phpstan analyse && vendor/bin/php-cs-fixer fix --dry-run
   ```
4. Keep commits focused - one logical change per commit
5. Submit a pull request with a clear description of what changed and why

## Reporting bugs

When reporting a bug, please include:

- PHP and Symfony versions
- Steps to reproduce
- Expected vs actual behavior
- Relevant error messages or stack traces

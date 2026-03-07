# Contributing

Thank you for considering contributing to API Filters Bundle! This guide will help you get started.

## Development setup

### Prerequisites

**Option A — Docker (recommended)**

- Docker

**Option B — Local**

- PHP >= 8.3
- Composer

### Getting started

```bash
# Clone the repository
git clone <repository-url>
cd api-filters-bundle
```

**With Docker:**

```bash
make install
```

**Without Docker:**

```bash
composer install
```

## Running checks

| Check | Docker (`make`) | Local |
|---|---|---|
| All checks | `make check` | `vendor/bin/phpunit && vendor/bin/phpstan analyse && vendor/bin/php-cs-fixer fix --dry-run --diff` |
| Tests | `make test` | `vendor/bin/phpunit` |
| Single test file | `make test ARGS="tests/path/to/Test.php"` | `vendor/bin/phpunit tests/path/to/Test.php` |
| Single test method | `make test ARGS="--filter methodName"` | `vendor/bin/phpunit --filter methodName` |
| PHPStan | `make phpstan` | `vendor/bin/phpstan analyse` |
| CS check | `make cs` | `vendor/bin/php-cs-fixer fix --dry-run --diff` |
| CS auto-fix | `make cs-fix` | `vendor/bin/php-cs-fixer fix` |

## Coding standards

- **PHP 8.2+** with `declare(strict_types=1)` in every file
- **Symfony coding standard** (`@Symfony` ruleset) with trailing commas on multiline arguments/parameters
- **PHPStan level max** on `src/`
- Run `make cs-fix` (or `vendor/bin/php-cs-fixer fix`) before committing to auto-format your code

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

Each filter strategy must be implemented across 3 layers: **ORM**, **DBAL**, and **SQL**.

1. Add a `case` to `src/ValueObject/FilterType.php`
2. Create the ORM strategy in `src/Filter/ORM/Strategy/` implementing `FilterStrategyInterface`
3. Create the DBAL strategy in `src/Filter/DBAL/Strategy/` implementing `DbalFilterStrategyInterface`
4. Create the SQL strategy in `src/Filter/SQL/Strategy/` implementing `SqlFilterStrategyInterface`
5. Write integration tests for each layer in `tests/Integration/Filter/{ORM,DBAL,SQL}/Strategy/`
6. Register the strategy in each test trait's `setUp()`:
   - `tests/Integration/Filter/ORM/OrmFilterTestTrait.php`
   - `tests/Integration/Filter/DBAL/DbalFilterTestTrait.php`
   - `tests/Integration/Filter/SQL/SqlFilterTestTrait.php`
7. Update `README.md`
8. Run `make check` to verify everything passes

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
│   ├── ORM/
│   │   ├── OrmFilterApplier.php      # Doctrine ORM applier
│   │   └── Strategy/
│   │       ├── EqFilterStrategy.php
│   │       ├── NeqFilterStrategy.php
│   │       ├── GtFilterStrategy.php
│   │       ├── GteFilterStrategy.php
│   │       ├── LtFilterStrategy.php
│   │       ├── LteFilterStrategy.php
│   │       ├── LikeFilterStrategy.php
│   │       ├── StartWithFilterStrategy.php
│   │       ├── EndWithFilterStrategy.php
│   │       ├── IsNullFilterStrategy.php
│   │       └── OrderFilterStrategy.php
│   ├── DBAL/
│   │   ├── DbalFilterApplier.php
│   │   ├── DbalFilterApplierInterface.php
│   │   ├── DbalFilterStrategyInterface.php
│   │   └── Strategy/
│   │       ├── EqFilterStrategy.php
│   │       ├── NeqFilterStrategy.php
│   │       ├── GtFilterStrategy.php
│   │       ├── GteFilterStrategy.php
│   │       ├── LtFilterStrategy.php
│   │       ├── LteFilterStrategy.php
│   │       ├── LikeFilterStrategy.php
│   │       ├── StartWithFilterStrategy.php
│   │       ├── EndWithFilterStrategy.php
│   │       ├── IsNullFilterStrategy.php
│   │       └── OrderFilterStrategy.php
│   └── SQL/
│       ├── SqlFilterApplier.php
│       ├── SqlFilterApplierInterface.php
│       ├── SqlFilterStrategyInterface.php
│       ├── SqlQueryContext.php
│       └── Strategy/
│           ├── EqFilterStrategy.php
│           ├── NeqFilterStrategy.php
│           ├── GtFilterStrategy.php
│           ├── GteFilterStrategy.php
│           ├── LtFilterStrategy.php
│           ├── LteFilterStrategy.php
│           ├── LikeFilterStrategy.php
│           ├── StartWithFilterStrategy.php
│           ├── EndWithFilterStrategy.php
│           ├── IsNullFilterStrategy.php
│           └── OrderFilterStrategy.php
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
    └── Filter/
        ├── ORM/
        │   ├── OrmFilterTestTrait.php
        │   └── Strategy/
        ├── DBAL/
        │   ├── DbalFilterTestTrait.php
        │   └── Strategy/
        └── SQL/
            ├── SqlFilterTestTrait.php
            └── Strategy/
```

## Pull requests

1. Create a feature branch from `main`
2. Write tests for any new functionality
3. Make sure all checks pass:
   ```bash
   make check
   ```
4. Keep commits focused - one logical change per commit
5. Submit a pull request with a clear description of what changed and why

## Reporting bugs

When reporting a bug, please include:

- PHP and Symfony versions
- Steps to reproduce
- Expected vs actual behavior
- Relevant error messages or stack traces

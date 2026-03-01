# API Filters Bundle

A Symfony bundle that resolves API query filters from HTTP requests and applies them to query builders using a strategy pattern. Declare allowed filters with PHP attributes on your controller actions, and the bundle handles parsing, validation, and query building automatically.

## Requirements

- PHP >= 8.2
- Symfony >= 7.4
- Doctrine ORM >= 3.0 *(optional, required only for the built-in ORM filter strategies)*

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

## Quick start

### 1. Add `#[ApiFilter]` attributes to your controller

```php
use Isma\ApiFiltersBundle\Attribute\ApiFilter;
use Isma\ApiFiltersBundle\ValueObject\Filters;
use Isma\ApiFiltersBundle\ValueObject\FilterType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final class UserController
{
    #[Route('/api/users', methods: ['GET'])]
    #[ApiFilter(name: 'firstname', allowedTypes: [FilterType::Eq->value, FilterType::Like->value])]
    #[ApiFilter(name: 'lastname')]
    #[ApiFilter(name: 'status', enumClass: UserStatus::class)]
    public function list(Filters $filters): JsonResponse
    {
        // $filters is automatically resolved from the query string
    }
}
```

### 2. Send a request with filters

```
GET /api/users?filters[firstname][eq]=John
GET /api/users?filters[firstname][like]=Joh
GET /api/users?filters[status][eq]=active
GET /api/users?filters[firstname][eq]=John&filters[lastname][eq]=Doe
```

Filters also support arrays:

```
GET /api/users?filters[status][eq][]=active&filters[status][eq][]=inactive
```

### 3. Apply filters to a Doctrine query builder

```php
use Isma\ApiFiltersBundle\Filter\FilterApplierInterface;

final class UserRepository
{
    public function __construct(
        private FilterApplierInterface $filterApplier,
    ) {
    }

    public function findByFilters(Filters $filters): array
    {
        $qb = $this->createQueryBuilder('u');

        $this->filterApplier->apply($qb, $filters, [
            'firstname' => 'u.firstname',
            'lastname'  => 'u.lastname',
            'status'    => 'u.status',
        ]);

        return $qb->getQuery()->getResult();
    }
}
```

## The `#[ApiFilter]` attribute

| Parameter | Type | Default | Description |
|---|---|---|---|
| `name` | `string` | *(required)* | The filter field name used in the query string |
| `allowedTypes` | `string[]` | `[]` (all types) | Restrict which filter types can be used. Empty = all types allowed |
| `enumClass` | `class-string<BackedEnum>\|null` | `null` | Validate and cast the filter value against a backed enum |

### Restricting filter types

```php
// Only allow exact match on the "role" field
#[ApiFilter(name: 'role', allowedTypes: [FilterType::Eq->value])]
```

If a client sends a disallowed type, a `400 Bad Request` is returned.

### Enum validation

```php
#[ApiFilter(name: 'status', enumClass: UserStatus::class)]
```

The value is validated against the enum cases. Invalid values return a `400 Bad Request`.

## Built-in filter types

The bundle ships with three ORM filter strategies:

| Type | Query string | Scalar DQL | Array DQL |
|---|---|---|---|
| `eq` | `filters[field][eq]=value` | `field = :param` | `field IN (:param)` |
| `neq` | `filters[field][neq]=value` | `field != :param` | `field NOT IN (:param)` |
| `like` | `filters[field][like]=value` | `field LIKE :param` | `field LIKE :p0 OR field LIKE :p1 ...` |

The `like` strategy automatically wraps values with `%` wildcards and escapes `%` and `_` characters in user input.

Empty arrays are silently skipped (no condition is added).

## Creating a custom filter strategy

Implement `FilterStrategyInterface` and the bundle will auto-discover it:

```php
use Doctrine\ORM\QueryBuilder;
use Isma\ApiFiltersBundle\Filter\FilterStrategyInterface;

final class GreaterThanFilterStrategy implements FilterStrategyInterface
{
    public function getType(): string
    {
        return 'gt';
    }

    public function apply(object $queryBuilder, string $column, mixed $value, string $parameterName): void
    {
        \assert($queryBuilder instanceof QueryBuilder);

        $queryBuilder->andWhere(\sprintf('%s > :%s', $column, $parameterName))
            ->setParameter($parameterName, $value);
    }
}
```

The strategy is automatically tagged with `isma_api_filters.strategy` and collected by `OrmFilterApplier`. No manual service registration needed.

You can then use it immediately:

```
GET /api/users?filters[age][gt]=18
```

## Architecture

```
Request ──> FiltersValueResolver ──> Filters VO ──> FilterApplierInterface ──> QueryBuilder
                 │                                         │
          reads #[ApiFilter]                    dispatches to strategies
          validates types/enums                 by filter type (eq, neq, like, ...)
```

### Namespace overview

| Namespace | Role |
|---|---|
| `Attribute\` | `#[ApiFilter]` PHP attribute |
| `ValueObject\` | `Filter`, `Filters`, `FilterType` enum |
| `Resolver\` | `FiltersValueResolver` - HTTP request to `Filters` value object |
| `Filter\` | Generic interfaces (`FilterApplierInterface`, `FilterStrategyInterface`) |
| `Filter\ORM\` | Doctrine ORM implementation (`OrmFilterApplier`) |
| `Filter\ORM\Strategy\` | Built-in strategies: `EqFilterStrategy`, `NeqFilterStrategy`, `LikeFilterStrategy` |
| `Exception\` | `InvalidFilterException` (400), `DuplicateFilterStrategyException` (logic error) |

### Key design decisions

- **ORM-agnostic interfaces** - `FilterApplierInterface` and `FilterStrategyInterface` accept `object $queryBuilder`, keeping the core free from Doctrine dependencies. Concrete ORM implementations live under `Filter\ORM\`.
- **Strategy auto-configuration** - Any class implementing `FilterStrategyInterface` is automatically tagged and collected. No YAML/XML wiring needed.
- **Doctrine ORM is optional** - It is a `suggest` dependency, not a hard requirement. The bundle can be extended to support other ORMs or query builders.

## Error handling

| Scenario | Exception | HTTP status |
|---|---|---|
| Unknown filter field | `InvalidFilterException` | 400 |
| Disallowed filter type | `InvalidFilterException` | 400 |
| Invalid enum value | `InvalidFilterException` | 400 |
| Malformed filter format | `InvalidFilterException` | 400 |
| Missing field mapping in `apply()` | `\InvalidArgumentException` | 500 |
| Duplicate strategy for same type | `DuplicateFilterStrategyException` | Container build error |

## License

MIT - see [LICENSE](LICENSE) for details.

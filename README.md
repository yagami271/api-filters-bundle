# 🎯 API Filters Bundle

A Symfony bundle that resolves API query filters from HTTP requests and applies them to query builders using a strategy pattern. Declare allowed filters with PHP attributes on your controller actions, and the bundle handles parsing, validation, and query building automatically.

## 📋 Requirements

- PHP >= 8.3
- Symfony 6.4 / 7.4 / 8.0+
- Doctrine ORM >= 3.4 *(required for the built-in ORM filter strategies)*

## 📦 Installation

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

## 🚀 Quick start

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
GET /api/users?filters[firstname][start_with]=Jo
GET /api/users?filters[email][end_with]=@example.com
GET /api/users?filters[status][eq]=active
GET /api/users?filters[age][gte]=18
GET /api/users?filters[age][lt]=65
GET /api/users?filters[deleted_at][is_null]=true
GET /api/users?filters[firstname][eq]=John&filters[lastname][eq]=Doe
```

Filters also support arrays (for `eq`, `neq`, `like`, `start_with`, `end_with`):

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

## ⚙️ The `#[ApiFilter]` attribute

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

## 🔎 Built-in filter types

The bundle ships with 10 ORM filter strategies:

| Type | Query string | Scalar DQL | Array DQL |
|---|---|---|---|
| `eq` | `filters[field][eq]=value` | `field = :param` | `field IN (:param)` |
| `neq` | `filters[field][neq]=value` | `field != :param` | `field NOT IN (:param)` |
| `like` | `filters[field][like]=value` | `field LIKE '%val%'` | OR of `LIKE` clauses |
| `start_with` | `filters[field][start_with]=value` | `field LIKE 'val%'` | OR of `LIKE` clauses |
| `end_with` | `filters[field][end_with]=value` | `field LIKE '%val'` | OR of `LIKE` clauses |
| `gt` | `filters[field][gt]=value` | `field > :param` | ❌ throws exception |
| `gte` | `filters[field][gte]=value` | `field >= :param` | ❌ throws exception |
| `lt` | `filters[field][lt]=value` | `field < :param` | ❌ throws exception |
| `lte` | `filters[field][lte]=value` | `field <= :param` | ❌ throws exception |
| `is_null` | `filters[field][is_null]=true` | `field IS NULL` / `field IS NOT NULL` | ❌ throws exception |

**Notes:**
- `like`, `start_with`, and `end_with` automatically escape `%` and `_` characters in user input.
- Empty arrays are silently skipped for `eq` and `neq` (no condition is added).
- `gt`, `gte`, `lt`, `lte`, and `is_null` only accept scalar values — passing an array throws an `\InvalidArgumentException`.
- `is_null` accepts `"true"`, `"1"`, or `true` for IS NULL, anything else for IS NOT NULL.

## 🧩 Creating a custom filter strategy

Implement `FilterStrategyInterface` and the bundle will auto-discover it:

```php
use Doctrine\ORM\QueryBuilder;
use Isma\ApiFiltersBundle\Filter\FilterStrategyInterface;

final class BetweenFilterStrategy implements FilterStrategyInterface
{
    public function getType(): string
    {
        return 'between';
    }

    public function apply(QueryBuilder $queryBuilder, string $column, mixed $value, string $parameterName): void
    {
        // Expects value as [min, max]
        $queryBuilder->andWhere(\sprintf('%s BETWEEN :%s_min AND :%s_max', $column, $parameterName, $parameterName))
            ->setParameter($parameterName.'_min', $value[0])
            ->setParameter($parameterName.'_max', $value[1]);
    }
}
```

The strategy is automatically tagged with `isma_api_filters.strategy` and collected by `OrmFilterApplier`. No manual service registration needed.

You can then use it immediately:

```
GET /api/users?filters[age][between][]=18&filters[age][between][]=65
```

## 🏗️ Architecture

```
                          ┌─────────────────────┐
                          │    HTTP Request      │
                          │ ?filters[f][type]=v  │
                          └─────────┬───────────┘
                                    │
                                    ▼
                       ┌────────────────────────┐
                       │  FiltersValueResolver   │
                       │  reads #[ApiFilter]     │
                       │  validates types/enums  │
                       └─────────┬──────────────┘
                                 │
                                 ▼
                          ┌────────────┐
                          │ Filters VO │
                          └──────┬─────┘
                                 │
                                 ▼
                    ┌────────────────────────┐
                    │  OrmFilterApplier      │
                    │  (FilterApplierInterface)│
                    └─────────┬──────────────┘
                              │ dispatches by type
              ┌───────┬──────┼──────┬───────┬────────┐
              ▼       ▼      ▼      ▼       ▼        ▼
            eq      neq    like    gt     is_null   ...
```

### 🔑 Key design decisions

- **Strategy pattern** — Each filter type (`eq`, `like`, `gt`, …) is a separate class implementing `FilterStrategyInterface`. Adding a new filter = adding one class.
- **Strategy auto-configuration** — Any class implementing `FilterStrategyInterface` is automatically tagged (`isma_api_filters.strategy`) and collected by `OrmFilterApplier`. No YAML/XML wiring needed.
- **Doctrine ORM integration** — `FilterStrategyInterface` and `FilterApplierInterface` use `Doctrine\ORM\QueryBuilder`. Doctrine ORM is a required dependency.

## ⚠️ Error handling

| Scenario | Exception | HTTP status |
|---|---|---|
| Unknown filter field | `InvalidFilterException` | 400 |
| Disallowed filter type | `InvalidFilterException` | 400 |
| Invalid enum value | `InvalidFilterException` | 400 |
| Malformed filter format | `InvalidFilterException` | 400 |
| Missing field mapping in `apply()` | `\InvalidArgumentException` | 500 |
| Duplicate strategy for same type | `DuplicateFilterStrategyException` | Container build error |

## 📄 License

MIT - see [LICENSE](LICENSE) for details.

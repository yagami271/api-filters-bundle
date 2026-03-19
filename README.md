# 🎯 API Filters Bundle

[![CI](https://github.com/yagami271/api-filters-bundle/actions/workflows/ci.yaml/badge.svg)](https://github.com/yagami271/api-filters-bundle/actions/workflows/ci.yaml)
[![PHP](https://img.shields.io/badge/PHP-≥8.3-8892BF?logo=php&logoColor=white)](https://www.php.net/)
[![Symfony](https://img.shields.io/badge/Symfony-6.4%20|%207.4%20|%208.0-000000?logo=symfony&logoColor=white)](https://symfony.com/)
[![License](https://img.shields.io/github/license/yagami271/api-filters-bundle)](LICENSE)
[![PHPStan](https://img.shields.io/badge/PHPStan-level%20max-brightgreen)](https://phpstan.org/)
[![Code Style](https://img.shields.io/badge/code%20style-%40Symfony-black)](https://cs.symfony.com/)

A Symfony bundle that resolves API query filters from HTTP requests and applies them to query builders using a strategy pattern. Declare allowed filters with PHP attributes on your controller actions, and the bundle handles parsing, validation, and query building automatically.

## 📋 Requirements

- PHP >= 8.3
- Symfony 6.4 / 7.4 / 8.0+
- Doctrine ORM >= 3.4 *(required for the built-in ORM filter strategies)*
- Doctrine DBAL >= 4.0 *(required for the built-in DBAL filter strategies)*
- PDO *(required for the built-in Pure SQL filter strategies — no Doctrine dependency)*

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
GET /api/users?filters[firstname][ilike]=joh
GET /api/users?filters[firstname][inotlike]=admin
GET /api/users?filters[firstname][start_with]=Jo
GET /api/users?filters[email][end_with]=@example.com
GET /api/users?filters[status][eq]=active
GET /api/users?filters[age][gte]=18
GET /api/users?filters[age][lt]=65
GET /api/users?filters[deleted_at][is_null]=true
GET /api/users?filters[firstname][eq]=John&filters[lastname][eq]=Doe
GET /api/users?filters[firstname][order]=asc
```

Filters also support arrays (for `eq`, `neq`, `like`, `ilike`, `inotlike`, `start_with`, `end_with`):

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

### 4. Apply filters using DBAL (without ORM)

If you don't use Doctrine ORM, you can use the DBAL filter applier with a raw `Doctrine\DBAL\Query\QueryBuilder`:

```php
use Isma\ApiFiltersBundle\Filter\DBAL\DbalFilterApplierInterface;

final class UserRepository
{
    public function __construct(
        private DbalFilterApplierInterface $filterApplier,
        private Connection $connection,
    ) {
    }

    public function findByFilters(Filters $filters): array
    {
        $qb = $this->connection->createQueryBuilder()
            ->select('t.*')
            ->from('users', 't');

        $this->filterApplier->apply($qb, $filters, [
            'firstname' => 't.firstname',
            'lastname'  => 't.lastname',
            'status'    => 't.status',
        ]);

        return $qb->executeQuery()->fetchAllAssociative();
    }
}
```

### 5. Apply filters using Pure SQL (PDO — no Doctrine required)

If your project doesn't use Doctrine at all, you can use the Pure SQL filter applier with a raw PDO connection:

```php
use Isma\ApiFiltersBundle\Filter\SQL\SqlFilterApplierInterface;
use Isma\ApiFiltersBundle\Filter\SQL\SqlQueryContext;

final class UserRepository
{
    public function __construct(
        private SqlFilterApplierInterface $filterApplier,
        private \PDO $pdo,
    ) {
    }

    public function findByFilters(Filters $filters): array
    {
        $context = new SqlQueryContext();

        $this->filterApplier->apply($context, $filters, [
            'firstname' => 'firstname',
            'lastname'  => 'lastname',
            'status'    => 'status',
        ]);

        $sql = 'SELECT * FROM users';
        if ($where = $context->getWhereClause()) {
            $sql .= ' WHERE ' . $where;
        }
        if ($orderBy = $context->getOrderByClause()) {
            $sql .= ' ORDER BY ' . $orderBy;
        }

        $stmt = $this->pdo->prepare($sql);
        foreach ($context->getParameters() as $name => $value) {
            $stmt->bindValue(':' . $name, $value);
        }
        $stmt->execute();

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}
```

## 🔎 Built-in filter types

The bundle ships with 13 filter strategies for ORM, DBAL, and Pure SQL:

| Type | Query string | Scalar DQL | Array DQL |
|---|---|---|---|
| `eq` | `filters[field][eq]=value` | `field = :param` | `field IN (:param)` |
| `neq` | `filters[field][neq]=value` | `field != :param` | `field NOT IN (:param)` |
| `like` | `filters[field][like]=value` | `field LIKE '%val%'` | OR of `LIKE` clauses |
| `ilike` | `filters[field][ilike]=value` | `LOWER(field) LIKE LOWER('%val%')` | OR of `LIKE` clauses |
| `inotlike` | `filters[field][inotlike]=value` | `LOWER(field) NOT LIKE LOWER('%val%')` | AND of `NOT LIKE` clauses |
| `start_with` | `filters[field][start_with]=value` | `field LIKE 'val%'` | OR of `LIKE` clauses |
| `end_with` | `filters[field][end_with]=value` | `field LIKE '%val'` | OR of `LIKE` clauses |
| `gt` | `filters[field][gt]=value` | `field > :param` | ❌ throws exception |
| `gte` | `filters[field][gte]=value` | `field >= :param` | ❌ throws exception |
| `lt` | `filters[field][lt]=value` | `field < :param` | ❌ throws exception |
| `lte` | `filters[field][lte]=value` | `field <= :param` | ❌ throws exception |
| `is_null` | `filters[field][is_null]=true` | `field IS NULL` / `field IS NOT NULL` | ❌ throws exception |
| `order` | `filters[field][order]=asc` | `ORDER BY field ASC/DESC` | ❌ throws exception |

**Notes:**
- `like`, `ilike`, `inotlike`, `start_with`, and `end_with` automatically escape `%` and `_` characters in user input.
- Empty arrays are silently skipped for `eq` and `neq` (no condition is added).
- `gt`, `gte`, `lt`, `lte`, `is_null`, and `order` only accept scalar values — passing an array throws an `\InvalidArgumentException`.
- `is_null` accepts `"true"`, `"1"`, or `true` for IS NULL, anything else for IS NOT NULL.
- `order` accepts only `"asc"` or `"desc"` (case-insensitive) and adds an `ORDER BY` clause instead of a `WHERE` condition.

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

### Creating a custom DBAL filter strategy

For DBAL, implement `DbalFilterStrategyInterface`:

```php
use Doctrine\DBAL\Query\QueryBuilder;
use Isma\ApiFiltersBundle\Filter\DBAL\DbalFilterStrategyInterface;

final class BetweenFilterStrategy implements DbalFilterStrategyInterface
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

The strategy is automatically tagged with `isma_api_filters.dbal_strategy` and collected by `DbalFilterApplier`.

### Creating a custom Pure SQL filter strategy

For pure SQL (PDO), implement `SqlFilterStrategyInterface`:

```php
use Isma\ApiFiltersBundle\Filter\SQL\SqlFilterStrategyInterface;
use Isma\ApiFiltersBundle\Filter\SQL\SqlQueryContext;

final class BetweenFilterStrategy implements SqlFilterStrategyInterface
{
    public function getType(): string
    {
        return 'between';
    }

    public function apply(SqlQueryContext $context, string $column, mixed $value, string $parameterName): void
    {
        // Expects value as [min, max]
        $context->andWhere(\sprintf('%s BETWEEN :%s_min AND :%s_max', $column, $parameterName, $parameterName))
            ->setParameter($parameterName.'_min', $value[0])
            ->setParameter($parameterName.'_max', $value[1]);
    }
}
```

The strategy is automatically tagged with `isma_api_filters.sql_strategy` and collected by `SqlFilterApplier`.

> **Naming convention:** To avoid any collision with current or future built-in filter types, prefix your custom filter names with `x-` (e.g. `x-between`, `x-fulltext`). This ensures your custom strategies will never conflict with a type added to the bundle in a later version.

You can then use it immediately:

```
GET /api/users?filters[age][between][]=18&filters[age][between][]=65
```

## ⚠️ Error handling

| Scenario | Exception | HTTP status |
|---|---|---|
| Unknown filter field | `InvalidFilterException` | 400 |
| Disallowed filter type | `InvalidFilterException` | 400 |
| Invalid enum value | `InvalidFilterException` | 400 |
| Malformed filter format | `InvalidFilterException` | 400 |
| Missing field mapping in `apply()` | `\InvalidArgumentException` | 500 |
| Duplicate strategy for same type | `DuplicateFilterStrategyException` | Container build error |

## 🗺️ Roadmap

- **More filter types** — Expand the built-in strategy catalog (e.g. `not_between`, `in_range`, `is_empty`, full-text search, etc.).

## 📄 License

MIT - see [LICENSE](LICENSE) for details.

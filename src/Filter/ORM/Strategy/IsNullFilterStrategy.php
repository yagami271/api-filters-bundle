<?php

declare(strict_types=1);

namespace Isma\ApiFiltersBundle\Filter\ORM\Strategy;

use Doctrine\ORM\QueryBuilder;
use Isma\ApiFiltersBundle\Filter\FilterStrategyInterface;
use Isma\ApiFiltersBundle\ValueObject\FilterType;

final class IsNullFilterStrategy implements FilterStrategyInterface
{
    public function getType(): string
    {
        return FilterType::IsNull->value;
    }

    public function apply(QueryBuilder $queryBuilder, string $column, mixed $value, string $parameterName): void
    {
        if (\is_array($value)) {
            throw new \InvalidArgumentException(\sprintf('Filter "is_null" does not support array values for column "%s".', $column));
        }

        if (\in_array($value, [true, 'true', '1'], true)) {
            $queryBuilder->andWhere(\sprintf('%s IS NULL', $column));
        } else {
            $queryBuilder->andWhere(\sprintf('%s IS NOT NULL', $column));
        }
    }
}

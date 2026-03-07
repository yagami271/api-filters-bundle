<?php

declare(strict_types=1);

namespace Isma\ApiFiltersBundle\Filter\SQL\Strategy;

use Isma\ApiFiltersBundle\Filter\SQL\SqlFilterStrategyInterface;
use Isma\ApiFiltersBundle\Filter\SQL\SqlQueryContext;
use Isma\ApiFiltersBundle\ValueObject\FilterType;

final class IsNullFilterStrategy implements SqlFilterStrategyInterface
{
    public function getType(): string
    {
        return FilterType::IsNull->value;
    }

    public function apply(SqlQueryContext $context, string $column, mixed $value, string $parameterName): void
    {
        if (\is_array($value)) {
            throw new \InvalidArgumentException(\sprintf('Filter "is_null" does not support array values for column "%s".', $column));
        }

        if (\in_array($value, [true, 'true', '1'], true)) {
            $context->andWhere(\sprintf('%s IS NULL', $column));
        } else {
            $context->andWhere(\sprintf('%s IS NOT NULL', $column));
        }
    }
}

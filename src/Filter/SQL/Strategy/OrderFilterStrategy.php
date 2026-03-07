<?php

declare(strict_types=1);

namespace Isma\ApiFiltersBundle\Filter\SQL\Strategy;

use Isma\ApiFiltersBundle\Filter\SQL\SqlFilterStrategyInterface;
use Isma\ApiFiltersBundle\Filter\SQL\SqlQueryContext;
use Isma\ApiFiltersBundle\ValueObject\FilterType;

final class OrderFilterStrategy implements SqlFilterStrategyInterface
{
    public function getType(): string
    {
        return FilterType::Order->value;
    }

    public function apply(SqlQueryContext $context, string $column, mixed $value, string $parameterName): void
    {
        if (\is_array($value)) {
            throw new \InvalidArgumentException(\sprintf('Filter "order" does not support array values for column "%s".', $column));
        }

        if (!\is_string($value)) {
            throw new \InvalidArgumentException(\sprintf('Filter "order" only accepts "asc" or "desc" for column "%s".', $column));
        }

        if (!\in_array(strtolower($value), ['asc', 'desc'], true)) {
            throw new \InvalidArgumentException(\sprintf('Filter "order" only accepts "asc" or "desc" for column "%s", got "%s".', $column, $value));
        }

        $context->addOrderBy($column, $value);
    }
}

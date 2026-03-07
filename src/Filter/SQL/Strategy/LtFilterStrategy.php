<?php

declare(strict_types=1);

namespace Isma\ApiFiltersBundle\Filter\SQL\Strategy;

use Isma\ApiFiltersBundle\Filter\SQL\SqlFilterStrategyInterface;
use Isma\ApiFiltersBundle\Filter\SQL\SqlQueryContext;
use Isma\ApiFiltersBundle\ValueObject\FilterType;

final class LtFilterStrategy implements SqlFilterStrategyInterface
{
    public function getType(): string
    {
        return FilterType::Lt->value;
    }

    public function apply(SqlQueryContext $context, string $column, mixed $value, string $parameterName): void
    {
        if (\is_array($value)) {
            throw new \InvalidArgumentException(\sprintf('Filter "lt" does not support array values for column "%s".', $column));
        }

        $context->andWhere(\sprintf('%s < :%s', $column, $parameterName))
            ->setParameter($parameterName, $value);
    }
}

<?php

declare(strict_types=1);

namespace Isma\ApiFiltersBundle\Filter\ORM\Strategy;

use Doctrine\ORM\QueryBuilder;
use Isma\ApiFiltersBundle\Filter\FilterStrategyInterface;
use Isma\ApiFiltersBundle\ValueObject\FilterType;

final class GtFilterStrategy implements FilterStrategyInterface
{
    public function getType(): string
    {
        return FilterType::Gt->value;
    }

    public function apply(QueryBuilder $queryBuilder, string $column, mixed $value, string $parameterName): void
    {
        if (\is_array($value)) {
            throw new \InvalidArgumentException(\sprintf('Filter "gt" does not support array values for column "%s".', $column));
        }

        $queryBuilder->andWhere(\sprintf('%s > :%s', $column, $parameterName))
            ->setParameter($parameterName, $value);
    }
}

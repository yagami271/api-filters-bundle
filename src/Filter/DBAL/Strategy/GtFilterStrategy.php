<?php

declare(strict_types=1);

namespace Isma\ApiFiltersBundle\Filter\DBAL\Strategy;

use Doctrine\DBAL\Query\QueryBuilder;
use Isma\ApiFiltersBundle\Filter\DBAL\DbalFilterStrategyInterface;
use Isma\ApiFiltersBundle\ValueObject\FilterType;

final class GtFilterStrategy implements DbalFilterStrategyInterface
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

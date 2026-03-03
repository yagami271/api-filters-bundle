<?php

declare(strict_types=1);

namespace Isma\ApiFiltersBundle\Filter\ORM\Strategy;

use Doctrine\ORM\QueryBuilder;
use Isma\ApiFiltersBundle\Filter\FilterStrategyInterface;
use Isma\ApiFiltersBundle\ValueObject\FilterType;

final class NeqFilterStrategy implements FilterStrategyInterface
{
    public function getType(): string
    {
        return FilterType::Neq->value;
    }

    public function apply(QueryBuilder $queryBuilder, string $column, mixed $value, string $parameterName): void
    {
        if (\is_array($value)) {
            if ([] === $value) {
                return;
            }

            $queryBuilder->andWhere(\sprintf('%s NOT IN (:%s)', $column, $parameterName))
                ->setParameter($parameterName, $value);
        } else {
            $queryBuilder->andWhere(\sprintf('%s != :%s', $column, $parameterName))
                ->setParameter($parameterName, $value);
        }
    }
}

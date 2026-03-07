<?php

declare(strict_types=1);

namespace Isma\ApiFiltersBundle\Filter\DBAL\Strategy;

use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Query\QueryBuilder;
use Isma\ApiFiltersBundle\Filter\DBAL\DbalFilterStrategyInterface;
use Isma\ApiFiltersBundle\ValueObject\FilterType;

final class EqFilterStrategy implements DbalFilterStrategyInterface
{
    public function getType(): string
    {
        return FilterType::Eq->value;
    }

    public function apply(QueryBuilder $queryBuilder, string $column, mixed $value, string $parameterName): void
    {
        if (\is_array($value)) {
            if ([] === $value) {
                return;
            }

            $queryBuilder->andWhere(\sprintf('%s IN (:%s)', $column, $parameterName))
                ->setParameter($parameterName, $value, ArrayParameterType::STRING);
        } else {
            $queryBuilder->andWhere(\sprintf('%s = :%s', $column, $parameterName))
                ->setParameter($parameterName, $value);
        }
    }
}

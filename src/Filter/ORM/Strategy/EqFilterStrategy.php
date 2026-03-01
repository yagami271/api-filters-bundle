<?php

declare(strict_types=1);

namespace Isma\ApiFiltersBundle\Filter\ORM\Strategy;

use Doctrine\ORM\QueryBuilder;
use Isma\ApiFiltersBundle\Filter\FilterStrategyInterface;
use Isma\ApiFiltersBundle\ValueObject\FilterType;

final class EqFilterStrategy implements FilterStrategyInterface
{
    public function getType(): string
    {
        return FilterType::Eq->value;
    }

    public function apply(object $queryBuilder, string $column, mixed $value, string $parameterName): void
    {
        \assert($queryBuilder instanceof QueryBuilder);

        if (\is_array($value)) {
            if ([] === $value) {
                return;
            }

            $queryBuilder->andWhere(\sprintf('%s IN (:%s)', $column, $parameterName))
                ->setParameter($parameterName, $value);
        } else {
            $queryBuilder->andWhere(\sprintf('%s = :%s', $column, $parameterName))
                ->setParameter($parameterName, $value);
        }
    }
}

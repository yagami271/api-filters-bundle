<?php

declare(strict_types=1);

namespace Isma\ApiFiltersBundle\Filter\DBAL\Strategy;

use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Query\QueryBuilder;
use Isma\ApiFiltersBundle\Filter\DBAL\DbalFilterStrategyInterface;
use Isma\ApiFiltersBundle\ValueObject\FilterType;

final class InoteqFilterStrategy implements DbalFilterStrategyInterface
{
    public function getType(): string
    {
        return FilterType::Inoteq->value;
    }

    public function apply(QueryBuilder $queryBuilder, string $column, mixed $value, string $parameterName): void
    {
        if (\is_array($value)) {
            if ([] === $value) {
                return;
            }

            $lowered = [];
            foreach ($value as $item) {
                if (!\is_string($item)) {
                    throw new \InvalidArgumentException(\sprintf('The inoteq filter only supports string values, "%s" given.', get_debug_type($item)));
                }
                $lowered[] = strtolower($item);
            }

            $queryBuilder->andWhere(\sprintf('LOWER(%s) NOT IN (:%s)', $column, $parameterName))
                ->setParameter($parameterName, $lowered, ArrayParameterType::STRING);
        } elseif (\is_string($value)) {
            $queryBuilder->andWhere(\sprintf('LOWER(%s) != :%s', $column, $parameterName))
                ->setParameter($parameterName, strtolower($value));
        } else {
            throw new \InvalidArgumentException(\sprintf('The inoteq filter only supports string values, "%s" given.', get_debug_type($value)));
        }
    }
}

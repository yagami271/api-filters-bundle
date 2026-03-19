<?php

declare(strict_types=1);

namespace Isma\ApiFiltersBundle\Filter\ORM\Strategy;

use Doctrine\ORM\QueryBuilder;
use Isma\ApiFiltersBundle\Filter\FilterStrategyInterface;
use Isma\ApiFiltersBundle\ValueObject\FilterType;

final class IeqFilterStrategy implements FilterStrategyInterface
{
    public function getType(): string
    {
        return FilterType::Ieq->value;
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
                    throw new \InvalidArgumentException(\sprintf('The ieq filter only supports string values, "%s" given.', get_debug_type($item)));
                }
                $lowered[] = strtolower($item);
            }

            $queryBuilder->andWhere(\sprintf('LOWER(%s) IN (:%s)', $column, $parameterName))
                ->setParameter($parameterName, $lowered);
        } elseif (\is_string($value)) {
            $queryBuilder->andWhere(\sprintf('LOWER(%s) = :%s', $column, $parameterName))
                ->setParameter($parameterName, strtolower($value));
        } else {
            throw new \InvalidArgumentException(\sprintf('The ieq filter only supports string values, "%s" given.', get_debug_type($value)));
        }
    }
}

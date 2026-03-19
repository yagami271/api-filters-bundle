<?php

declare(strict_types=1);

namespace Isma\ApiFiltersBundle\Filter\SQL\Strategy;

use Isma\ApiFiltersBundle\Filter\SQL\SqlFilterStrategyInterface;
use Isma\ApiFiltersBundle\Filter\SQL\SqlQueryContext;
use Isma\ApiFiltersBundle\ValueObject\FilterType;

final class IeqFilterStrategy implements SqlFilterStrategyInterface
{
    public function getType(): string
    {
        return FilterType::Ieq->value;
    }

    public function apply(SqlQueryContext $context, string $column, mixed $value, string $parameterName): void
    {
        if (\is_array($value)) {
            if ([] === $value) {
                return;
            }

            $placeholders = [];
            foreach (array_values($value) as $i => $item) {
                if (!\is_string($item)) {
                    throw new \InvalidArgumentException(\sprintf('The ieq filter only supports string values, "%s" given.', get_debug_type($item)));
                }

                $param = $parameterName.'_'.$i;
                $placeholders[] = ':'.$param;
                $context->setParameter($param, strtolower($item));
            }
            $context->andWhere(\sprintf('LOWER(%s) IN (%s)', $column, implode(', ', $placeholders)));
        } elseif (\is_string($value)) {
            $context->andWhere(\sprintf('LOWER(%s) = :%s', $column, $parameterName))
                ->setParameter($parameterName, strtolower($value));
        } else {
            throw new \InvalidArgumentException(\sprintf('The ieq filter only supports string values, "%s" given.', get_debug_type($value)));
        }
    }
}

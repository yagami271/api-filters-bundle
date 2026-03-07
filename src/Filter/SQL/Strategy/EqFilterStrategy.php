<?php

declare(strict_types=1);

namespace Isma\ApiFiltersBundle\Filter\SQL\Strategy;

use Isma\ApiFiltersBundle\Filter\SQL\SqlFilterStrategyInterface;
use Isma\ApiFiltersBundle\Filter\SQL\SqlQueryContext;
use Isma\ApiFiltersBundle\ValueObject\FilterType;

final class EqFilterStrategy implements SqlFilterStrategyInterface
{
    public function getType(): string
    {
        return FilterType::Eq->value;
    }

    public function apply(SqlQueryContext $context, string $column, mixed $value, string $parameterName): void
    {
        if (\is_array($value)) {
            if ([] === $value) {
                return;
            }

            $placeholders = [];
            foreach (array_values($value) as $i => $item) {
                $param = $parameterName.'_'.$i;
                $placeholders[] = ':'.$param;
                $context->setParameter($param, $item);
            }
            $context->andWhere(\sprintf('%s IN (%s)', $column, implode(', ', $placeholders)));
        } else {
            $context->andWhere(\sprintf('%s = :%s', $column, $parameterName))
                ->setParameter($parameterName, $value);
        }
    }
}

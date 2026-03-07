<?php

declare(strict_types=1);

namespace Isma\ApiFiltersBundle\Filter\SQL\Strategy;

use Isma\ApiFiltersBundle\Filter\SQL\SqlFilterStrategyInterface;
use Isma\ApiFiltersBundle\Filter\SQL\SqlQueryContext;
use Isma\ApiFiltersBundle\ValueObject\FilterType;

final class StartWithFilterStrategy implements SqlFilterStrategyInterface
{
    public function getType(): string
    {
        return FilterType::StartWith->value;
    }

    /**
     * @param string|string[] $value
     */
    public function apply(SqlQueryContext $context, string $column, mixed $value, string $parameterName): void
    {
        if (\is_array($value)) {
            $orConditions = [];
            foreach ($value as $i => $item) {
                $param = $parameterName.'_'.$i;
                $orConditions[] = \sprintf('%s LIKE :%s', $column, $param);
                $context->setParameter($param, $this->escapeWildcards($item).'%');
            }
            $context->andWhere('('.implode(' OR ', $orConditions).')');
        } else {
            $context->andWhere(\sprintf('%s LIKE :%s', $column, $parameterName))
                ->setParameter($parameterName, $this->escapeWildcards($value).'%');
        }
    }

    private function escapeWildcards(string $value): string
    {
        return str_replace(['%', '_'], ['\\%', '\\_'], $value);
    }
}

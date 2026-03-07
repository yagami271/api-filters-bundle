<?php

declare(strict_types=1);

namespace Isma\ApiFiltersBundle\Filter\ORM\Strategy;

use Doctrine\ORM\QueryBuilder;
use Isma\ApiFiltersBundle\Filter\FilterStrategyInterface;
use Isma\ApiFiltersBundle\ValueObject\FilterType;

final class StartWithFilterStrategy implements FilterStrategyInterface
{
    public function getType(): string
    {
        return FilterType::StartWith->value;
    }

    /**
     * @param string|string[] $value
     */
    public function apply(QueryBuilder $queryBuilder, string $column, mixed $value, string $parameterName): void
    {
        if (\is_array($value)) {
            $orConditions = [];
            foreach ($value as $i => $item) {
                $param = $parameterName.'_'.$i;
                $orConditions[] = \sprintf('%s LIKE :%s ESCAPE \'\\\'', $column, $param);
                $queryBuilder->setParameter($param, $this->escapeWildcards($item).'%');
            }
            $queryBuilder->andWhere($queryBuilder->expr()->orX(...$orConditions));
        } else {
            $queryBuilder->andWhere(\sprintf('%s LIKE :%s ESCAPE \'\\\'', $column, $parameterName))
                ->setParameter($parameterName, $this->escapeWildcards($value).'%');
        }
    }

    private function escapeWildcards(string $value): string
    {
        return str_replace(['%', '_'], ['\\%', '\\_'], $value);
    }
}

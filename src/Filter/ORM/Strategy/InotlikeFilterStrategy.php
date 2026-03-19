<?php

declare(strict_types=1);

namespace Isma\ApiFiltersBundle\Filter\ORM\Strategy;

use Doctrine\ORM\QueryBuilder;
use Isma\ApiFiltersBundle\Filter\FilterStrategyInterface;
use Isma\ApiFiltersBundle\ValueObject\FilterType;

final class InotlikeFilterStrategy implements FilterStrategyInterface
{
    public function getType(): string
    {
        return FilterType::Inotlike->value;
    }

    /**
     * @param string|string[] $value
     */
    public function apply(QueryBuilder $queryBuilder, string $column, mixed $value, string $parameterName): void
    {
        if (\is_array($value)) {
            $andConditions = [];
            foreach ($value as $i => $item) {
                $param = $parameterName.'_'.$i;
                $andConditions[] = \sprintf('LOWER(%s) NOT LIKE LOWER(:%s) ESCAPE \'!\'', $column, $param);
                $queryBuilder->setParameter($param, '%'.$this->escapeWildcards($item).'%');
            }
            $queryBuilder->andWhere($queryBuilder->expr()->andX(...$andConditions));
        } else {
            $queryBuilder->andWhere(\sprintf('LOWER(%s) NOT LIKE LOWER(:%s) ESCAPE \'!\'', $column, $parameterName))
                ->setParameter($parameterName, '%'.$this->escapeWildcards($value).'%');
        }
    }

    private function escapeWildcards(string $value): string
    {
        return str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $value);
    }
}

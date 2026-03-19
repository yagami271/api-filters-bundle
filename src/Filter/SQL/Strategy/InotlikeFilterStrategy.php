<?php

declare(strict_types=1);

namespace Isma\ApiFiltersBundle\Filter\SQL\Strategy;

use Isma\ApiFiltersBundle\Filter\SQL\SqlFilterStrategyInterface;
use Isma\ApiFiltersBundle\Filter\SQL\SqlQueryContext;
use Isma\ApiFiltersBundle\ValueObject\FilterType;

final class InotlikeFilterStrategy implements SqlFilterStrategyInterface
{
    public function getType(): string
    {
        return FilterType::Inotlike->value;
    }

    /**
     * @param string|string[] $value
     */
    public function apply(SqlQueryContext $context, string $column, mixed $value, string $parameterName): void
    {
        if (\is_array($value)) {
            $andConditions = [];
            foreach ($value as $i => $item) {
                $param = $parameterName.'_'.$i;
                $andConditions[] = \sprintf('LOWER(%s) NOT LIKE LOWER(:%s) ESCAPE \'!\'', $column, $param);
                $context->setParameter($param, '%'.$this->escapeWildcards($item).'%');
            }
            $context->andWhere('('.implode(' AND ', $andConditions).')');
        } else {
            $context->andWhere(\sprintf('LOWER(%s) NOT LIKE LOWER(:%s) ESCAPE \'!\'', $column, $parameterName))
                ->setParameter($parameterName, '%'.$this->escapeWildcards($value).'%');
        }
    }

    private function escapeWildcards(string $value): string
    {
        return str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $value);
    }
}

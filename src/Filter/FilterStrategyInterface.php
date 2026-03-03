<?php

declare(strict_types=1);

namespace Isma\ApiFiltersBundle\Filter;

use Doctrine\ORM\QueryBuilder;

interface FilterStrategyInterface
{
    public function getType(): string;

    public function apply(QueryBuilder $queryBuilder, string $column, mixed $value, string $parameterName): void;
}

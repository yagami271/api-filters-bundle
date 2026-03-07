<?php

declare(strict_types=1);

namespace Isma\ApiFiltersBundle\Filter\DBAL;

use Doctrine\DBAL\Query\QueryBuilder;

interface DbalFilterStrategyInterface
{
    public function getType(): string;

    public function apply(QueryBuilder $queryBuilder, string $column, mixed $value, string $parameterName): void;
}

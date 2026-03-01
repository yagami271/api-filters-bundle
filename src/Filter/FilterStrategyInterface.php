<?php

declare(strict_types=1);

namespace Isma\ApiFiltersBundle\Filter;

interface FilterStrategyInterface
{
    public function getType(): string;

    public function apply(object $queryBuilder, string $column, mixed $value, string $parameterName): void;
}

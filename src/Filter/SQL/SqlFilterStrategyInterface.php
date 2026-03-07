<?php

declare(strict_types=1);

namespace Isma\ApiFiltersBundle\Filter\SQL;

interface SqlFilterStrategyInterface
{
    public function getType(): string;

    public function apply(SqlQueryContext $context, string $column, mixed $value, string $parameterName): void;
}

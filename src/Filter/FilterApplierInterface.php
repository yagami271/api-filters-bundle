<?php

declare(strict_types=1);

namespace Isma\ApiFiltersBundle\Filter;

use Isma\ApiFiltersBundle\ValueObject\Filters;

interface FilterApplierInterface
{
    /**
     * @param array<string, string> $mapping Field name to column mapping (e.g. ['firstname' => 'u.firstname'])
     */
    public function apply(object $queryBuilder, Filters $filters, array $mapping): void;
}

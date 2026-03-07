<?php

declare(strict_types=1);

namespace Isma\ApiFiltersBundle\Filter\SQL;

use Isma\ApiFiltersBundle\ValueObject\Filters;

interface SqlFilterApplierInterface
{
    /**
     * @param array<string, string> $mapping Field name to column mapping (e.g. ['firstname' => 'firstname'])
     */
    public function apply(SqlQueryContext $context, Filters $filters, array $mapping): void;
}

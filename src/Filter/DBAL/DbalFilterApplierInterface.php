<?php

declare(strict_types=1);

namespace Isma\ApiFiltersBundle\Filter\DBAL;

use Doctrine\DBAL\Query\QueryBuilder;
use Isma\ApiFiltersBundle\ValueObject\Filters;

interface DbalFilterApplierInterface
{
    /**
     * @param array<string, string> $mapping Field name to column mapping (e.g. ['firstname' => 't.firstname'])
     */
    public function apply(QueryBuilder $queryBuilder, Filters $filters, array $mapping): void;
}

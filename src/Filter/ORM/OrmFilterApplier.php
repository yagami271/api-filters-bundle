<?php

declare(strict_types=1);

namespace Isma\ApiFiltersBundle\Filter\ORM;

use Doctrine\ORM\QueryBuilder;
use Isma\ApiFiltersBundle\Exception\DuplicateFilterStrategyException;
use Isma\ApiFiltersBundle\Filter\FilterApplierInterface;
use Isma\ApiFiltersBundle\Filter\FilterStrategyInterface;
use Isma\ApiFiltersBundle\ValueObject\Filters;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

final class OrmFilterApplier implements FilterApplierInterface
{
    /** @var array<string, FilterStrategyInterface> */
    private readonly array $strategies;

    /**
     * @param iterable<FilterStrategyInterface> $strategies
     */
    public function __construct(
        #[AutowireIterator('isma_api_filters.strategy')]
        iterable $strategies,
    ) {
        $map = [];
        foreach ($strategies as $strategy) {
            $type = $strategy->getType();
            if (isset($map[$type])) {
                throw new DuplicateFilterStrategyException(\sprintf('Duplicate filter strategy for type "%s": %s and %s.', $type, $map[$type]::class, $strategy::class));
            }
            $map[$type] = $strategy;
        }
        $this->strategies = $map;
    }

    public function apply(QueryBuilder $queryBuilder, Filters $filters, array $mapping): void
    {
        $uid = bin2hex(random_bytes(4));
        $paramIndex = 0;

        foreach ($filters->filters as $filter) {
            if (!isset($mapping[$filter->name])) {
                throw new \InvalidArgumentException(\sprintf('No mapping defined for filter "%s".', $filter->name));
            }

            if (!isset($this->strategies[$filter->type])) {
                throw new \InvalidArgumentException(\sprintf('No filter strategy registered for type "%s".', $filter->type));
            }

            $column = $mapping[$filter->name];
            $parameterName = \sprintf('filter_%s_%d', $uid, $paramIndex++);

            $this->strategies[$filter->type]->apply($queryBuilder, $column, $filter->value, $parameterName);
        }
    }
}

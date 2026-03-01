<?php

declare(strict_types=1);

namespace Isma\ApiFiltersBundle\ValueObject;

final readonly class Filters
{
    /**
     * @param Filter[] $filters
     */
    public function __construct(public array $filters = [])
    {
    }

    public function isEmpty(): bool
    {
        return [] === $this->filters;
    }

    /**
     * @return Filter[]
     */
    public function getByName(string $name): array
    {
        return array_values(
            array_filter($this->filters, static fn (Filter $filter): bool => $filter->name === $name),
        );
    }
}

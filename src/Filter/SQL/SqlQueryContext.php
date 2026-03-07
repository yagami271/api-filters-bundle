<?php

declare(strict_types=1);

namespace Isma\ApiFiltersBundle\Filter\SQL;

final class SqlQueryContext
{
    /** @var list<string> */
    private array $whereClauses = [];

    /** @var array<string, mixed> */
    private array $parameters = [];

    /** @var list<string> */
    private array $orderByClauses = [];

    public function andWhere(string $clause): self
    {
        $this->whereClauses[] = $clause;

        return $this;
    }

    public function setParameter(string $name, mixed $value): self
    {
        $this->parameters[$name] = $value;

        return $this;
    }

    public function addOrderBy(string $column, string $direction): self
    {
        $this->orderByClauses[] = \sprintf('%s %s', $column, $direction);

        return $this;
    }

    public function getWhereClause(): ?string
    {
        if ([] === $this->whereClauses) {
            return null;
        }

        return implode(' AND ', $this->whereClauses);
    }

    /**
     * @return array<string, mixed>
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    public function getOrderByClause(): ?string
    {
        if ([] === $this->orderByClauses) {
            return null;
        }

        return implode(', ', $this->orderByClauses);
    }
}

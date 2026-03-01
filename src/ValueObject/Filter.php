<?php

declare(strict_types=1);

namespace Isma\ApiFiltersBundle\ValueObject;

final readonly class Filter
{
    public function __construct(
        public string $name,
        public string $type,
        public mixed $value,
    ) {
    }

    public function isMultiValue(): bool
    {
        return is_array($this->value);
    }
}

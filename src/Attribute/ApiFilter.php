<?php

declare(strict_types=1);

namespace Isma\ApiFiltersBundle\Attribute;

#[\Attribute(\Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
final readonly class ApiFilter
{
    /**
     * @param string[]                       $allowedTypes
     * @param class-string<\BackedEnum>|null $enumClass
     */
    public function __construct(
        public string $name,
        public array $allowedTypes = [],
        public ?string $enumClass = null,
    ) {
    }
}

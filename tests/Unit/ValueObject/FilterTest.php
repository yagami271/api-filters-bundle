<?php

declare(strict_types=1);

namespace Isma\ApiFiltersBundle\Tests\Unit\ValueObject;

use Isma\ApiFiltersBundle\ValueObject\Filter;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class FilterTest extends TestCase
{
    #[Test]
    public function isMultiValueReturnsFalseForScalar(): void
    {
        $filter = new Filter('name', 'eq', 'John');

        self::assertFalse($filter->isMultiValue());
    }

    #[Test]
    public function isMultiValueReturnsTrueForArray(): void
    {
        $filter = new Filter('name', 'eq', ['John', 'Jane']);

        self::assertTrue($filter->isMultiValue());
    }
}

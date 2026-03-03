<?php

declare(strict_types=1);

namespace Isma\ApiFiltersBundle\Tests\Unit\ValueObject;

use Isma\ApiFiltersBundle\ValueObject\Filter;
use Isma\ApiFiltersBundle\ValueObject\Filters;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class FiltersTest extends TestCase
{
    #[Test]
    public function isEmptyReturnsTrueWhenNoFilters(): void
    {
        $filters = new Filters();

        self::assertTrue($filters->isEmpty());
    }

    #[Test]
    public function isEmptyReturnsFalseWhenFiltersExist(): void
    {
        $filters = new Filters([new Filter('name', 'eq', 'John')]);

        self::assertFalse($filters->isEmpty());
    }

    #[Test]
    public function getByNameReturnsMatchingFilters(): void
    {
        $f1 = new Filter('name', 'eq', 'John');
        $f2 = new Filter('age', 'eq', 25);
        $f3 = new Filter('name', 'like', 'Jo');

        $filters = new Filters([$f1, $f2, $f3]);

        $result = $filters->getByName('name');

        self::assertCount(2, $result);
        self::assertSame($f1, $result[0]);
        self::assertSame($f3, $result[1]);
    }

    #[Test]
    public function getByNameReturnsEmptyArrayWhenNoMatch(): void
    {
        $filters = new Filters([new Filter('name', 'eq', 'John')]);

        self::assertSame([], $filters->getByName('unknown'));
    }

    #[Test]
    public function defaultConstructorCreatesEmptyFilters(): void
    {
        $filters = new Filters();

        self::assertSame([], $filters->filters);
    }
}

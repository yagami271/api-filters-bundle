<?php

declare(strict_types=1);

namespace Isma\ApiFiltersBundle\Tests\Integration\Filter\SQL\Strategy;

use Isma\ApiFiltersBundle\Tests\Integration\Filter\SQL\SqlFilterTestTrait;
use Isma\ApiFiltersBundle\ValueObject\Filter;
use Isma\ApiFiltersBundle\ValueObject\Filters;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class OrderFilterStrategyTest extends TestCase
{
    use SqlFilterTestTrait;

    #[Test]
    public function itOrdersByAsc(): void
    {
        $results = $this->applyAndFetch(
            new Filters([new Filter('firstname', 'order', 'asc')]),
            ['firstname' => 'firstname'],
        );

        self::assertCount(5, $results);
        $firstnames = array_column($results, 'firstname');
        self::assertSame(['Alice', 'Bob', 'Jane', 'John', 'John'], $firstnames);
    }

    #[Test]
    public function itOrdersByDesc(): void
    {
        $results = $this->applyAndFetch(
            new Filters([new Filter('firstname', 'order', 'desc')]),
            ['firstname' => 'firstname'],
        );

        self::assertCount(5, $results);
        $firstnames = array_column($results, 'firstname');
        self::assertSame(['John', 'John', 'Jane', 'Bob', 'Alice'], $firstnames);
    }

    #[Test]
    public function itOrdersByAscCaseInsensitive(): void
    {
        $results = $this->applyAndFetch(
            new Filters([new Filter('firstname', 'order', 'ASC')]),
            ['firstname' => 'firstname'],
        );

        self::assertCount(5, $results);
        $firstnames = array_column($results, 'firstname');
        self::assertSame(['Alice', 'Bob', 'Jane', 'John', 'John'], $firstnames);
    }

    #[Test]
    public function itThrowsOnArrayValue(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->applyAndFetch(
            new Filters([new Filter('firstname', 'order', ['asc', 'desc'])]),
            ['firstname' => 'firstname'],
        );
    }

    #[Test]
    public function itThrowsOnInvalidValue(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->applyAndFetch(
            new Filters([new Filter('firstname', 'order', 'invalid')]),
            ['firstname' => 'firstname'],
        );
    }
}

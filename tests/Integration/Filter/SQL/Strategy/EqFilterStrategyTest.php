<?php

declare(strict_types=1);

namespace Isma\ApiFiltersBundle\Tests\Integration\Filter\SQL\Strategy;

use Isma\ApiFiltersBundle\Tests\Integration\Filter\SQL\SqlFilterTestTrait;
use Isma\ApiFiltersBundle\ValueObject\Filter;
use Isma\ApiFiltersBundle\ValueObject\Filters;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class EqFilterStrategyTest extends TestCase
{
    use SqlFilterTestTrait;

    #[Test]
    public function itFiltersWithEqScalar(): void
    {
        $results = $this->applyAndFetch(
            new Filters([new Filter('firstname', 'eq', 'John')]),
            ['firstname' => 'firstname'],
        );

        self::assertCount(2, $results);
        self::assertSame('John', $results[0]['firstname']);
        self::assertSame('John', $results[1]['firstname']);
    }

    #[Test]
    public function itFiltersWithEqArray(): void
    {
        $results = $this->applyAndFetch(
            new Filters([new Filter('firstname', 'eq', ['John', 'Alice'])]),
            ['firstname' => 'firstname'],
        );

        self::assertCount(3, $results);
        $firstnames = array_column($results, 'firstname');
        self::assertContains('John', $firstnames);
        self::assertContains('Alice', $firstnames);
    }

    #[Test]
    public function itHandlesEmptyArrayWithEq(): void
    {
        $results = $this->applyAndFetch(
            new Filters([new Filter('firstname', 'eq', [])]),
            ['firstname' => 'firstname'],
        );

        self::assertCount(5, $results);
    }
}

<?php

declare(strict_types=1);

namespace Isma\ApiFiltersBundle\Tests\Integration\Filter\SQL\Strategy;

use Isma\ApiFiltersBundle\Tests\Integration\Filter\SQL\SqlFilterTestTrait;
use Isma\ApiFiltersBundle\ValueObject\Filter;
use Isma\ApiFiltersBundle\ValueObject\Filters;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class NeqFilterStrategyTest extends TestCase
{
    use SqlFilterTestTrait;

    #[Test]
    public function itFiltersWithNeqScalar(): void
    {
        $results = $this->applyAndFetch(
            new Filters([new Filter('firstname', 'neq', 'John')]),
            ['firstname' => 'firstname'],
        );

        self::assertCount(3, $results);
        foreach ($results as $row) {
            self::assertNotSame('John', $row['firstname']);
        }
    }

    #[Test]
    public function itFiltersWithNeqArray(): void
    {
        $results = $this->applyAndFetch(
            new Filters([new Filter('firstname', 'neq', ['John', 'Alice'])]),
            ['firstname' => 'firstname'],
        );

        self::assertCount(2, $results);
        $firstnames = array_column($results, 'firstname');
        self::assertNotContains('John', $firstnames);
        self::assertNotContains('Alice', $firstnames);
    }

    #[Test]
    public function itHandlesEmptyArrayWithNeq(): void
    {
        $results = $this->applyAndFetch(
            new Filters([new Filter('firstname', 'neq', [])]),
            ['firstname' => 'firstname'],
        );

        self::assertCount(5, $results);
    }
}

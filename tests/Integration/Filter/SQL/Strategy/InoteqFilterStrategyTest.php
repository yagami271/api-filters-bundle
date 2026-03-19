<?php

declare(strict_types=1);

namespace Isma\ApiFiltersBundle\Tests\Integration\Filter\SQL\Strategy;

use Isma\ApiFiltersBundle\Tests\Integration\Filter\SQL\SqlFilterTestTrait;
use Isma\ApiFiltersBundle\ValueObject\Filter;
use Isma\ApiFiltersBundle\ValueObject\Filters;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class InoteqFilterStrategyTest extends TestCase
{
    use SqlFilterTestTrait;

    #[Test]
    public function itFiltersWithInoteqScalarCaseInsensitive(): void
    {
        $results = $this->applyAndFetch(
            new Filters([new Filter('firstname', 'inoteq', 'john')]),
            ['firstname' => 'firstname'],
        );

        self::assertCount(3, $results);
        foreach ($results as $row) {
            self::assertNotSame('John', $row['firstname']);
        }
    }

    #[Test]
    public function itFiltersWithInoteqArrayCaseInsensitive(): void
    {
        $results = $this->applyAndFetch(
            new Filters([new Filter('firstname', 'inoteq', ['JOHN', 'alice'])]),
            ['firstname' => 'firstname'],
        );

        self::assertCount(2, $results);
        foreach ($results as $row) {
            self::assertNotContains($row['firstname'], ['John', 'Alice']);
        }
    }

    #[Test]
    public function itHandlesEmptyArrayWithInoteq(): void
    {
        $results = $this->applyAndFetch(
            new Filters([new Filter('firstname', 'inoteq', [])]),
            ['firstname' => 'firstname'],
        );

        self::assertCount(5, $results);
    }

    #[Test]
    public function itThrowsOnNonStringValue(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->applyAndFetch(
            new Filters([new Filter('firstname', 'inoteq', 123)]),
            ['firstname' => 'firstname'],
        );
    }
}

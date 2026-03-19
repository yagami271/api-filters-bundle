<?php

declare(strict_types=1);

namespace Isma\ApiFiltersBundle\Tests\Integration\Filter\DBAL\Strategy;

use Isma\ApiFiltersBundle\Tests\Integration\Filter\DBAL\DbalFilterTestTrait;
use Isma\ApiFiltersBundle\ValueObject\Filter;
use Isma\ApiFiltersBundle\ValueObject\Filters;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class InoteqFilterStrategyTest extends TestCase
{
    use DbalFilterTestTrait;

    #[Test]
    public function itFiltersWithInoteqScalarCaseInsensitive(): void
    {
        $results = $this->applyAndFetch(
            new Filters([new Filter('firstname', 'inoteq', 'john')]),
            ['firstname' => 't.firstname'],
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
            ['firstname' => 't.firstname'],
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
            ['firstname' => 't.firstname'],
        );

        self::assertCount(5, $results);
    }

    #[Test]
    public function itThrowsOnNonStringValue(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->applyAndFetch(
            new Filters([new Filter('firstname', 'inoteq', 123)]),
            ['firstname' => 't.firstname'],
        );
    }
}

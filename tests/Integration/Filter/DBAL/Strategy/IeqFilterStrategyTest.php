<?php

declare(strict_types=1);

namespace Isma\ApiFiltersBundle\Tests\Integration\Filter\DBAL\Strategy;

use Isma\ApiFiltersBundle\Tests\Integration\Filter\DBAL\DbalFilterTestTrait;
use Isma\ApiFiltersBundle\ValueObject\Filter;
use Isma\ApiFiltersBundle\ValueObject\Filters;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class IeqFilterStrategyTest extends TestCase
{
    use DbalFilterTestTrait;

    #[Test]
    public function itFiltersWithIeqScalarCaseInsensitive(): void
    {
        $results = $this->applyAndFetch(
            new Filters([new Filter('firstname', 'ieq', 'john')]),
            ['firstname' => 't.firstname'],
        );

        self::assertCount(2, $results);
        foreach ($results as $row) {
            self::assertSame('John', $row['firstname']);
        }
    }

    #[Test]
    public function itFiltersWithIeqArrayCaseInsensitive(): void
    {
        $results = $this->applyAndFetch(
            new Filters([new Filter('firstname', 'ieq', ['JOHN', 'alice'])]),
            ['firstname' => 't.firstname'],
        );

        self::assertCount(3, $results);
        $firstnames = array_column($results, 'firstname');
        self::assertContains('John', $firstnames);
        self::assertContains('Alice', $firstnames);
    }

    #[Test]
    public function itHandlesEmptyArrayWithIeq(): void
    {
        $results = $this->applyAndFetch(
            new Filters([new Filter('firstname', 'ieq', [])]),
            ['firstname' => 't.firstname'],
        );

        self::assertCount(5, $results);
    }

    #[Test]
    public function itThrowsOnNonStringValue(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->applyAndFetch(
            new Filters([new Filter('firstname', 'ieq', 123)]),
            ['firstname' => 't.firstname'],
        );
    }
}

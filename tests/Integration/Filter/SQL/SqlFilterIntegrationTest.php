<?php

declare(strict_types=1);

namespace Isma\ApiFiltersBundle\Tests\Integration\Filter\SQL;

use Isma\ApiFiltersBundle\Exception\DuplicateFilterStrategyException;
use Isma\ApiFiltersBundle\Filter\SQL\SqlFilterApplier;
use Isma\ApiFiltersBundle\Filter\SQL\Strategy\EqFilterStrategy;
use Isma\ApiFiltersBundle\ValueObject\Filter;
use Isma\ApiFiltersBundle\ValueObject\Filters;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class SqlFilterIntegrationTest extends TestCase
{
    use SqlFilterTestTrait;

    #[Test]
    public function itCombinesMultipleFilters(): void
    {
        $results = $this->applyAndFetch(
            new Filters([
                new Filter('firstname', 'eq', 'John'),
                new Filter('lastname', 'eq', 'Doe'),
            ]),
            [
                'firstname' => 'firstname',
                'lastname' => 'lastname',
            ],
        );

        self::assertCount(1, $results);
        self::assertSame('John', $results[0]['firstname']);
        self::assertSame('Doe', $results[0]['lastname']);
    }

    #[Test]
    public function itThrowsOnFilterNotInMapping(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('No mapping defined for filter "nonexistent".');

        $this->applyAndFetch(
            new Filters([new Filter('nonexistent', 'eq', 'value')]),
            ['firstname' => 'firstname'],
        );
    }

    #[Test]
    public function itThrowsOnDuplicateStrategyType(): void
    {
        $this->expectException(DuplicateFilterStrategyException::class);

        new SqlFilterApplier([
            new EqFilterStrategy(),
            new EqFilterStrategy(),
        ]);
    }

    #[Test]
    public function itThrowsOnUnknownFilterType(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->applyAndFetch(
            new Filters([new Filter('firstname', 'unknown', 'value')]),
            ['firstname' => 'firstname'],
        );
    }

    #[Test]
    public function itReturnsAllRowsWhenNoFilters(): void
    {
        $results = $this->applyAndFetch(
            new Filters(),
            ['firstname' => 'firstname'],
        );

        self::assertCount(5, $results);
    }
}

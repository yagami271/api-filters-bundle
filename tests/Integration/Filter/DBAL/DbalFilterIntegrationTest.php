<?php

declare(strict_types=1);

namespace Isma\ApiFiltersBundle\Tests\Integration\Filter\DBAL;

use Isma\ApiFiltersBundle\Exception\DuplicateFilterStrategyException;
use Isma\ApiFiltersBundle\Filter\DBAL\DbalFilterApplier;
use Isma\ApiFiltersBundle\Filter\DBAL\Strategy\EqFilterStrategy;
use Isma\ApiFiltersBundle\ValueObject\Filter;
use Isma\ApiFiltersBundle\ValueObject\Filters;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class DbalFilterIntegrationTest extends TestCase
{
    use DbalFilterTestTrait;

    #[Test]
    public function itCombinesMultipleFilters(): void
    {
        $results = $this->applyAndFetch(
            new Filters([
                new Filter('firstname', 'eq', 'John'),
                new Filter('lastname', 'eq', 'Doe'),
            ]),
            [
                'firstname' => 't.firstname',
                'lastname' => 't.lastname',
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
            ['firstname' => 't.firstname'],
        );
    }

    #[Test]
    public function itThrowsOnDuplicateStrategyType(): void
    {
        $this->expectException(DuplicateFilterStrategyException::class);

        new DbalFilterApplier([
            new EqFilterStrategy(),
            new EqFilterStrategy(),
        ]);
    }

    #[Test]
    public function itThrowsOnUnknownFilterType(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $qb = $this->connection->createQueryBuilder()
            ->select('t.*')
            ->from('test_users', 't');

        $this->applier->apply(
            $qb,
            new Filters([new Filter('firstname', 'unknown', 'value')]),
            ['firstname' => 't.firstname'],
        );
    }

    #[Test]
    public function itReturnsAllRowsWhenNoFilters(): void
    {
        $results = $this->applyAndFetch(
            new Filters(),
            ['firstname' => 't.firstname'],
        );

        self::assertCount(5, $results);
    }
}

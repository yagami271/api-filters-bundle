<?php

declare(strict_types=1);

namespace Isma\ApiFiltersBundle\Tests\Integration\Filter\ORM;

use Isma\ApiFiltersBundle\Exception\DuplicateFilterStrategyException;
use Isma\ApiFiltersBundle\Filter\ORM\OrmFilterApplier;
use Isma\ApiFiltersBundle\Filter\ORM\Strategy\EqFilterStrategy;
use Isma\ApiFiltersBundle\Tests\Integration\Filter\ORM\Entity\TestUser;
use Isma\ApiFiltersBundle\ValueObject\Filter;
use Isma\ApiFiltersBundle\ValueObject\Filters;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class OrmFilterIntegrationTest extends TestCase
{
    use OrmFilterTestTrait;

    #[Test]
    public function itCombinesMultipleFilters(): void
    {
        $results = $this->applyAndFetch(
            new Filters([
                new Filter('firstname', 'eq', 'John'),
                new Filter('lastname', 'eq', 'Doe'),
            ]),
            [
                'firstname' => 'u.firstname',
                'lastname' => 'u.lastname',
            ],
        );

        self::assertCount(1, $results);
        self::assertSame('John', $results[0]->getFirstname());
        self::assertSame('Doe', $results[0]->getLastname());
    }

    #[Test]
    public function itThrowsOnFilterNotInMapping(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('No mapping defined for filter "nonexistent".');

        $this->applyAndFetch(
            new Filters([new Filter('nonexistent', 'eq', 'value')]),
            ['firstname' => 'u.firstname'],
        );
    }

    #[Test]
    public function itThrowsOnDuplicateStrategyType(): void
    {
        $this->expectException(DuplicateFilterStrategyException::class);

        new OrmFilterApplier([
            new EqFilterStrategy(),
            new EqFilterStrategy(),
        ]);
    }

    #[Test]
    public function itThrowsOnUnknownFilterType(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $qb = $this->em->createQueryBuilder()
            ->select('u')
            ->from(TestUser::class, 'u');

        $this->applier->apply(
            $qb,
            new Filters([new Filter('firstname', 'unknown', 'value')]),
            ['firstname' => 'u.firstname'],
        );
    }

    #[Test]
    public function itReturnsAllRowsWhenNoFilters(): void
    {
        $results = $this->applyAndFetch(
            new Filters(),
            ['firstname' => 'u.firstname'],
        );

        self::assertCount(5, $results);
    }
}

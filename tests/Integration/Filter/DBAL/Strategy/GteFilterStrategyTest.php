<?php

declare(strict_types=1);

namespace Isma\ApiFiltersBundle\Tests\Integration\Filter\DBAL\Strategy;

use Isma\ApiFiltersBundle\Tests\Integration\Filter\DBAL\DbalFilterTestTrait;
use Isma\ApiFiltersBundle\ValueObject\Filter;
use Isma\ApiFiltersBundle\ValueObject\Filters;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class GteFilterStrategyTest extends TestCase
{
    use DbalFilterTestTrait;

    #[Test]
    public function itFiltersWithGteScalar(): void
    {
        $results = $this->applyAndFetch(
            new Filters([new Filter('age', 'gte', 30)]),
            ['age' => 't.age'],
        );

        self::assertCount(2, $results);
        $firstnames = array_column($results, 'firstname');
        self::assertContains('John', $firstnames);
    }

    #[Test]
    public function itThrowsOnArrayValue(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->applyAndFetch(
            new Filters([new Filter('age', 'gte', [25, 30])]),
            ['age' => 't.age'],
        );
    }

    #[Test]
    public function itFiltersWithGteExcludesLower(): void
    {
        $results = $this->applyAndFetch(
            new Filters([new Filter('age', 'gte', 29)]),
            ['age' => 't.age'],
        );

        self::assertCount(2, $results);
        foreach ($results as $row) {
            self::assertGreaterThanOrEqual(29, $row['age']);
        }
    }
}

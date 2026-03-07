<?php

declare(strict_types=1);

namespace Isma\ApiFiltersBundle\Tests\Integration\Filter\SQL\Strategy;

use Isma\ApiFiltersBundle\Tests\Integration\Filter\SQL\SqlFilterTestTrait;
use Isma\ApiFiltersBundle\ValueObject\Filter;
use Isma\ApiFiltersBundle\ValueObject\Filters;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class GteFilterStrategyTest extends TestCase
{
    use SqlFilterTestTrait;

    #[Test]
    public function itFiltersWithGteScalar(): void
    {
        $results = $this->applyAndFetch(
            new Filters([new Filter('age', 'gte', 30)]),
            ['age' => 'age'],
        );

        self::assertCount(2, $results);
        foreach ($results as $row) {
            self::assertGreaterThanOrEqual(30, (int) $row['age']);
        }
    }

    #[Test]
    public function itThrowsOnArrayValue(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->applyAndFetch(
            new Filters([new Filter('age', 'gte', [28, 30])]),
            ['age' => 'age'],
        );
    }
}

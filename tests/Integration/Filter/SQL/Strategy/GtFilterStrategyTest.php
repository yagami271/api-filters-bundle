<?php

declare(strict_types=1);

namespace Isma\ApiFiltersBundle\Tests\Integration\Filter\SQL\Strategy;

use Isma\ApiFiltersBundle\Tests\Integration\Filter\SQL\SqlFilterTestTrait;
use Isma\ApiFiltersBundle\ValueObject\Filter;
use Isma\ApiFiltersBundle\ValueObject\Filters;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class GtFilterStrategyTest extends TestCase
{
    use SqlFilterTestTrait;

    #[Test]
    public function itFiltersWithGtScalar(): void
    {
        $results = $this->applyAndFetch(
            new Filters([new Filter('age', 'gt', 28)]),
            ['age' => 'age'],
        );

        self::assertCount(2, $results);
        foreach ($results as $row) {
            self::assertGreaterThan(28, (int) $row['age']);
        }
    }

    #[Test]
    public function itThrowsOnArrayValue(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->applyAndFetch(
            new Filters([new Filter('age', 'gt', [28, 30])]),
            ['age' => 'age'],
        );
    }
}

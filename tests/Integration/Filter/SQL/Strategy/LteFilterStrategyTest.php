<?php

declare(strict_types=1);

namespace Isma\ApiFiltersBundle\Tests\Integration\Filter\SQL\Strategy;

use Isma\ApiFiltersBundle\Tests\Integration\Filter\SQL\SqlFilterTestTrait;
use Isma\ApiFiltersBundle\ValueObject\Filter;
use Isma\ApiFiltersBundle\ValueObject\Filters;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class LteFilterStrategyTest extends TestCase
{
    use SqlFilterTestTrait;

    #[Test]
    public function itFiltersWithLteScalar(): void
    {
        $results = $this->applyAndFetch(
            new Filters([new Filter('age', 'lte', 28)]),
            ['age' => 'age'],
        );

        self::assertCount(2, $results);
        foreach ($results as $row) {
            self::assertLessThanOrEqual(28, (int) $row['age']);
        }
    }

    #[Test]
    public function itThrowsOnArrayValue(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->applyAndFetch(
            new Filters([new Filter('age', 'lte', [28, 30])]),
            ['age' => 'age'],
        );
    }
}

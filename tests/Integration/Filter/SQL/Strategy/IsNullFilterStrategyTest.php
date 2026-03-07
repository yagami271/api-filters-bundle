<?php

declare(strict_types=1);

namespace Isma\ApiFiltersBundle\Tests\Integration\Filter\SQL\Strategy;

use Isma\ApiFiltersBundle\Tests\Integration\Filter\SQL\SqlFilterTestTrait;
use Isma\ApiFiltersBundle\ValueObject\Filter;
use Isma\ApiFiltersBundle\ValueObject\Filters;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class IsNullFilterStrategyTest extends TestCase
{
    use SqlFilterTestTrait;

    #[Test]
    public function itFiltersIsNullTrue(): void
    {
        $results = $this->applyAndFetch(
            new Filters([new Filter('age', 'is_null', 'true')]),
            ['age' => 'age'],
        );

        self::assertCount(1, $results);
        self::assertSame('Bob', $results[0]['firstname']);
        self::assertNull($results[0]['age']);
    }

    #[Test]
    public function itThrowsOnArrayValue(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->applyAndFetch(
            new Filters([new Filter('age', 'is_null', ['true', 'false'])]),
            ['age' => 'age'],
        );
    }

    #[Test]
    public function itFiltersIsNullFalse(): void
    {
        $results = $this->applyAndFetch(
            new Filters([new Filter('age', 'is_null', 'false')]),
            ['age' => 'age'],
        );

        self::assertCount(4, $results);
        foreach ($results as $row) {
            self::assertNotNull($row['age']);
        }
    }

    #[Test]
    public function itFiltersIsNullWithBooleanTrue(): void
    {
        $results = $this->applyAndFetch(
            new Filters([new Filter('age', 'is_null', true)]),
            ['age' => 'age'],
        );

        self::assertCount(1, $results);
        self::assertSame('Bob', $results[0]['firstname']);
    }

    #[Test]
    public function itFiltersIsNullWithStringOne(): void
    {
        $results = $this->applyAndFetch(
            new Filters([new Filter('age', 'is_null', '1')]),
            ['age' => 'age'],
        );

        self::assertCount(1, $results);
        self::assertSame('Bob', $results[0]['firstname']);
    }

    #[Test]
    public function itFiltersIsNullWithStringZero(): void
    {
        $results = $this->applyAndFetch(
            new Filters([new Filter('age', 'is_null', '0')]),
            ['age' => 'age'],
        );

        self::assertCount(4, $results);
    }
}

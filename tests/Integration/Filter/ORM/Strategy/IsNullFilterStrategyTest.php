<?php

declare(strict_types=1);

namespace Isma\ApiFiltersBundle\Tests\Integration\Filter\ORM\Strategy;

use Isma\ApiFiltersBundle\Tests\Integration\Filter\ORM\OrmFilterTestTrait;
use Isma\ApiFiltersBundle\ValueObject\Filter;
use Isma\ApiFiltersBundle\ValueObject\Filters;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class IsNullFilterStrategyTest extends TestCase
{
    use OrmFilterTestTrait;

    #[Test]
    public function itFiltersIsNullTrue(): void
    {
        $results = $this->applyAndFetch(
            new Filters([new Filter('age', 'is_null', 'true')]),
            ['age' => 'u.age'],
        );

        self::assertCount(1, $results);
        self::assertSame('Bob', $results[0]->getFirstname());
        self::assertNull($results[0]->getAge());
    }

    #[Test]
    public function itThrowsOnArrayValue(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->applyAndFetch(
            new Filters([new Filter('age', 'is_null', ['true', 'false'])]),
            ['age' => 'u.age'],
        );
    }

    #[Test]
    public function itFiltersIsNullFalse(): void
    {
        $results = $this->applyAndFetch(
            new Filters([new Filter('age', 'is_null', 'false')]),
            ['age' => 'u.age'],
        );

        self::assertCount(4, $results);
        foreach ($results as $user) {
            self::assertNotNull($user->getAge());
        }
    }

    #[Test]
    public function itFiltersIsNullWithBooleanTrue(): void
    {
        $results = $this->applyAndFetch(
            new Filters([new Filter('age', 'is_null', true)]),
            ['age' => 'u.age'],
        );

        self::assertCount(1, $results);
        self::assertSame('Bob', $results[0]->getFirstname());
    }

    #[Test]
    public function itFiltersIsNullWithStringOne(): void
    {
        $results = $this->applyAndFetch(
            new Filters([new Filter('age', 'is_null', '1')]),
            ['age' => 'u.age'],
        );

        self::assertCount(1, $results);
        self::assertSame('Bob', $results[0]->getFirstname());
    }

    #[Test]
    public function itFiltersIsNullWithStringZero(): void
    {
        $results = $this->applyAndFetch(
            new Filters([new Filter('age', 'is_null', '0')]),
            ['age' => 'u.age'],
        );

        self::assertCount(4, $results);
    }
}

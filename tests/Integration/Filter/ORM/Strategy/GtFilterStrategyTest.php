<?php

declare(strict_types=1);

namespace Isma\ApiFiltersBundle\Tests\Integration\Filter\ORM\Strategy;

use Isma\ApiFiltersBundle\Tests\Integration\Filter\ORM\Entity\TestUser;
use Isma\ApiFiltersBundle\Tests\Integration\Filter\ORM\OrmFilterTestTrait;
use Isma\ApiFiltersBundle\ValueObject\Filter;
use Isma\ApiFiltersBundle\ValueObject\Filters;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class GtFilterStrategyTest extends TestCase
{
    use OrmFilterTestTrait;

    #[Test]
    public function itFiltersWithGtScalar(): void
    {
        $results = $this->applyAndFetch(
            new Filters([new Filter('age', 'gt', 28)]),
            ['age' => 'u.age'],
        );

        self::assertCount(2, $results);
        $firstnames = array_map(fn (TestUser $u) => $u->getFirstname(), $results);
        self::assertContains('John', $firstnames); // age 30
        self::assertContains('John', $firstnames); // age 35 (John Smith)
    }

    #[Test]
    public function itThrowsOnArrayValue(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->applyAndFetch(
            new Filters([new Filter('age', 'gt', [25, 30])]),
            ['age' => 'u.age'],
        );
    }

    #[Test]
    public function itFiltersWithGtExcludesEqual(): void
    {
        $results = $this->applyAndFetch(
            new Filters([new Filter('age', 'gt', 30)]),
            ['age' => 'u.age'],
        );

        self::assertCount(1, $results);
        self::assertSame('John', $results[0]->getFirstname());
        self::assertSame(35, $results[0]->getAge());
    }
}
